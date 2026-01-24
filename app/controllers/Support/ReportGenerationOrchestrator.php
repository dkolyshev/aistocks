<?php
/**
 * ReportGenerationOrchestrator - Orchestrates report generation
 * Single Responsibility: Coordinate report generation across different formats
 * Open/Closed: Uses registry pattern for extensible report types
 * PHP 5.5 compatible
 */

class ReportGenerationOrchestrator {
    private $settingsManager;
    private $csvFilePath;
    private $reportsDir;
    private $generatorTypes;

    /**
     * Constructor with dependency injection
     * @param SettingsManagerInterface $settingsManager Settings manager
     * @param string $csvFilePath Path to CSV data file
     * @param string $reportsDir Reports output directory
     * @param array $generatorTypes Array of generator type configurations (OCP)
     */
    public function __construct($settingsManager, $csvFilePath, $reportsDir, $generatorTypes = null) {
        $this->settingsManager = $settingsManager;
        $this->csvFilePath = $csvFilePath;
        $this->reportsDir = rtrim($reportsDir, "/");

        // Default generator types (can be overridden for OCP)
        $this->generatorTypes =
            $generatorTypes !== null
                ? $generatorTypes
                : [
                    "html" => [
                        "class" => "HtmlReportGenerator",
                        "extension" => ".html",
                        "requires_stocks" => true,
                    ],
                    "pdf" => [
                        "class" => "PdfReportGenerator",
                        "extension" => ".pdf",
                        "requires_html" => true,
                    ],
                    "flipbook" => [
                        "class" => "FlipbookGenerator",
                        "extension" => "-flipbook.html",
                        "requires_stocks" => true,
                    ],
                ];
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
        $fileName = $settings["file_name"];
        $result = [
            "file_name" => $fileName,
            "html" => false,
            "pdf" => false,
            "flipbook" => false,
            "errors" => [],
        ];

        try {
            // Load CSV data
            $csvReader = new CsvDataReader($this->csvFilePath);
            if (!$csvReader->load()) {
                $result["errors"][] = "Failed to load CSV data";
                return $result;
            }

            $stockCount = isset($settings["stock_count"]) ? intval($settings["stock_count"]) : 6;
            $stocks = $csvReader->getLimitedData($stockCount);

            if (empty($stocks)) {
                $result["errors"][] = "No stock data available";
                return $result;
            }

            // Initialize shortcode processor
            $shortcodeProcessor = new ShortcodeProcessor();

            // Generate HTML report
            $htmlGenerator = new HtmlReportGenerator($settings, $stocks, $shortcodeProcessor);
            $htmlPath = $this->reportsDir . "/" . $fileName . ".html";
            $result["html"] = $htmlGenerator->saveToFile($htmlPath);

            if (!$result["html"]) {
                $result["errors"][] = "Failed to generate HTML report";
            }

            // Generate PDF report
            $pdfGenerator = new PdfReportGenerator($settings, $htmlGenerator);
            $pdfPath = $this->reportsDir . "/" . $fileName . ".pdf";
            $result["pdf"] = $pdfGenerator->saveToFile($pdfPath);

            if (!$result["pdf"]) {
                $result["errors"][] = "Failed to generate PDF report (install wkhtmltopdf or upload manual PDF)";
            }

            // Generate Flipbook report
            $flipbookGenerator = new FlipbookGenerator($settings, $stocks, $shortcodeProcessor);
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

    /**
     * Add a new generator type (OCP extension point)
     * @param string $name Generator type name
     * @param array $config Generator configuration
     */
    public function addGeneratorType($name, $config) {
        $this->generatorTypes[$name] = $config;
    }

    /**
     * Remove a generator type
     * @param string $name Generator type name
     */
    public function removeGeneratorType($name) {
        unset($this->generatorTypes[$name]);
    }

    /**
     * Get registered generator types
     * @return array Generator types configuration
     */
    public function getGeneratorTypes() {
        return $this->generatorTypes;
    }
}
