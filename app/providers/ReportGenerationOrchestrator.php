<?php
/**
 * ReportGenerationOrchestrator - Orchestrates report generation
 * Single Responsibility: Coordinate report generation across different formats
 * PHP 5.5 compatible
 */

class ReportGenerationOrchestrator {
    private $settingsManager;
    private $dataDir;
    private $reportsDir;
    private $serviceFactory;
    private $dataSourceFactory;

    /**
     * Constructor with dependency injection
     * @param SettingsManagerInterface $settingsManager Settings manager
     * @param string $dataDir Path to data directory containing CSV files
     * @param string $reportsDir Reports output directory
     * @param ReportServiceFactoryInterface $serviceFactory Factory for creating report services
     * @param DataSourceFactory $dataSourceFactory Factory for creating data sources
     */
    public function __construct($settingsManager, $dataDir, $reportsDir, $serviceFactory, $dataSourceFactory) {
        $this->settingsManager = $settingsManager;
        $this->dataDir = rtrim($dataDir, "/");
        $this->reportsDir = rtrim($reportsDir, "/");
        $this->serviceFactory = $serviceFactory;
        $this->dataSourceFactory = $dataSourceFactory;
    }

    /**
     * Generate reports for all settings
     * @return array Result with all generation results
     */
    public function generateAllReports() {
        $results = [];
        $allSettings = $this->settingsManager->getAllSettings();

        if (empty($allSettings)) {
            return [
                "success" => false,
                "message" => "No report settings found",
                "results" => [],
            ];
        }

        foreach ($allSettings as $settings) {
            $result = $this->generateReportForSetting($settings);
            $results[] = $result;
        }

        return [
            "success" => true,
            "message" => "Report generation completed",
            "results" => $results,
        ];
    }

    /**
     * Generate reports for a single setting
     * @param array $settings Report settings
     * @return array Result with status and messages
     */
    public function generateReportForSetting($settings) {
        $fileName = str_replace(" ", "-", $settings["file_name"]);
        $result = [
            "file_name" => $fileName,
            "html" => false,
            "pdf" => false,
            "flipbook" => false,
            "errors" => [],
        ];

        try {
            // Support both old (api_placeholder) and new (data_source + data_source_type) formats
            $dataSourceString = "";

            if (!empty($settings["api_placeholder"])) {
                // Old format: already in "type:name" format
                $dataSourceString = $settings["api_placeholder"];
            } elseif (!empty($settings["data_source"]) && !empty($settings["data_source_type"])) {
                // New format: construct "type:name" format
                $dataSourceString = $settings["data_source_type"] . ":" . $settings["data_source"];
            }

            // Validate data source is specified
            if (empty($dataSourceString)) {
                $result["errors"][] = "Data source is not specified";
                return $result;
            }

            // Extract API config if available
            $apiConfig = isset($settings["api_config"]) && is_array($settings["api_config"]) ? $settings["api_config"] : null;

            // Load data source using factory
            try {
                $dataSource = $this->dataSourceFactory->create($dataSourceString, $apiConfig);
            } catch (InvalidArgumentException $e) {
                $result["errors"][] = "Invalid data source: " . $e->getMessage();
                return $result;
            }

            // Load data from source
            if (!$dataSource->load()) {
                $result["errors"][] = "Failed to load data from: " . $dataSourceString;
                return $result;
            }

            // Validate data is available
            $stockCount = isset($settings["stock_count"]) ? intval($settings["stock_count"]) : 6;
            $stocks = $dataSource->getLimitedData($stockCount);

            if (empty($stocks)) {
                $result["errors"][] = "No stock data available";
                return $result;
            }

            // Initialize shortcode processor
            $shortcodeProcessor = $this->serviceFactory->createShortcodeProcessor();

            // Generate HTML report
            $htmlGenerator = $this->serviceFactory->createHtmlReportGenerator($settings, $dataSource, $shortcodeProcessor);
            $htmlPath = $this->reportsDir . "/" . $fileName . ".html";
            $result["html"] = $htmlGenerator->saveToFile($htmlPath);

            if (!$result["html"]) {
                $result["errors"][] = "Failed to generate HTML report";
            }

            // Generate PDF report
            $pdfGenerator = $this->serviceFactory->createPdfReportGenerator($settings, $htmlGenerator);
            $pdfPath = $this->reportsDir . "/" . $fileName . ".pdf";
            $result["pdf"] = $pdfGenerator->saveToFile($pdfPath);

            if (!$result["pdf"]) {
                $result["errors"][] = "Failed to generate PDF report (install wkhtmltopdf or upload manual PDF)";
            }

            // Generate Flipbook report
            $flipbookGenerator = $this->serviceFactory->createFlipbookGenerator($settings, $dataSource, $shortcodeProcessor);
            $flipbookPath = $this->reportsDir . "/" . $fileName . "-flipbook.html";
            $result["flipbook"] = $flipbookGenerator->saveToFile($flipbookPath);

            if (!$result["flipbook"]) {
                $result["errors"][] = "Failed to generate Flipbook report";
            }
        } catch (Exception $e) {
            $result["errors"][] = "Exception: " . $e->getMessage();
        }

        return $result;
    }

    /**
     * Calculate success statistics from generation results
     * @param array $results Generation results array
     * @return array Statistics with success count and total
     */
    public function calculateStatistics($results) {
        $successCount = 0;
        $totalCount = count($results);

        foreach ($results as $result) {
            if ($result["html"] || $result["pdf"] || $result["flipbook"]) {
                $successCount++;
            }
        }

        return [
            "success_count" => $successCount,
            "total_count" => $totalCount,
        ];
    }

}
