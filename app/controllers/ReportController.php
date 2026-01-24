<?php
/**
 * ReportController - Handles report-related requests
 * PHP 5.5 compatible
 */

class ReportController {
    private $settingsManager;
    private $imageUploadHandler;
    private $pdfUploadHandler;

    /**
     * Constructor
     */
    public function __construct() {
        $this->settingsManager = new SettingsManager(SETTINGS_FILE);
        $this->imageUploadHandler = new FileUploadHandler(IMAGES_DIR, ALLOWED_IMAGE_TYPES, MAX_FILE_SIZE);
        $this->pdfUploadHandler = new FileUploadHandler(REPORTS_DIR, ALLOWED_PDF_TYPES, MAX_FILE_SIZE);
    }

    /**
     * Handle settings form submission
     * @return array Response with success status and message
     */
    public function handleSettingsSubmission() {
        if ($_SERVER["REQUEST_METHOD"] !== "POST") {
            return ["success" => false, "message" => "Invalid request method"];
        }

        $action = isset($_POST["action"]) ? $_POST["action"] : "add";
        $settingData = $this->extractSettingData();

        // Validate setting data
        $errors = $this->settingsManager->validateSetting($settingData);
        if (!empty($errors)) {
            return ["success" => false, "message" => implode(", ", $errors)];
        }

        // Handle file uploads
        $uploadResult = $this->handleFileUploads($settingData);
        if (!$uploadResult["success"]) {
            return $uploadResult;
        }

        $settingData = array_merge($settingData, $uploadResult["data"]);

        // Save or update setting
        if ($action === "update") {
            $originalFileName = isset($_POST["original_file_name"]) ? $_POST["original_file_name"] : $settingData["file_name"];
            $result = $this->settingsManager->updateSetting($originalFileName, $settingData);
            $message = $result ? "Settings updated successfully" : "Failed to update settings";
        } else {
            $result = $this->settingsManager->addSetting($settingData);
            $message = $result ? "Settings added successfully" : "Failed to add settings (file name may already exist)";
        }

        return ["success" => $result, "message" => $message];
    }

    /**
     * Handle delete request
     * @return array Response with success status and message
     */
    public function handleDelete() {
        if ($_SERVER["REQUEST_METHOD"] !== "POST") {
            return ["success" => false, "message" => "Invalid request method"];
        }

        $fileName = isset($_POST["file_name"]) ? $_POST["file_name"] : "";

        if (empty($fileName)) {
            return ["success" => false, "message" => "File name is required"];
        }

        $result = $this->settingsManager->deleteSetting($fileName);
        $message = $result ? "Settings deleted successfully" : "Failed to delete settings";

        return ["success" => $result, "message" => $message];
    }

    /**
     * Extract setting data from POST request
     * @return array Setting data
     */
    private function extractSettingData() {
        return [
            "file_name" => isset($_POST["file_name"]) ? trim($_POST["file_name"]) : "",
            "report_title" => isset($_POST["report_title"]) ? trim($_POST["report_title"]) : "",
            "author_name" => isset($_POST["author_name"]) ? trim($_POST["author_name"]) : "",
            "api_placeholder" => isset($_POST["api_placeholder"]) ? trim($_POST["api_placeholder"]) : "data.csv",
            "stock_count" => isset($_POST["stock_count"]) ? intval($_POST["stock_count"]) : 6,
            "article_image" => isset($_POST["existing_article_image"]) ? $_POST["existing_article_image"] : "",
            "pdf_cover_image" => isset($_POST["existing_pdf_cover"]) ? $_POST["existing_pdf_cover"] : "",
            "report_intro_html" => isset($_POST["report_intro_html"]) ? $_POST["report_intro_html"] : "",
            "stock_block_html" => isset($_POST["stock_block_html"]) ? $_POST["stock_block_html"] : "",
            "disclaimer_html" => isset($_POST["disclaimer_html"]) ? $_POST["disclaimer_html"] : "",
            "manual_pdf_path" => isset($_POST["existing_manual_pdf"]) ? $_POST["existing_manual_pdf"] : "",
        ];
    }

    /**
     * Handle file uploads from form
     * @param array $settingData Current setting data
     * @return array Result with success status and uploaded file paths
     */
    private function handleFileUploads($settingData) {
        $uploadedData = [];

        // Handle article image upload
        if ($this->imageUploadHandler->hasUploadedFile("article_image")) {
            $filename = $this->imageUploadHandler->upload($_FILES["article_image"], $settingData["file_name"] . "_article");

            if ($filename === false) {
                return [
                    "success" => false,
                    "message" => "Article image upload failed: " . $this->imageUploadHandler->getLastError(),
                ];
            }

            $uploadedData["article_image"] = IMAGES_DIR . "/" . $filename;
        }

        // Handle PDF cover image upload
        if ($this->imageUploadHandler->hasUploadedFile("pdf_cover")) {
            $filename = $this->imageUploadHandler->upload($_FILES["pdf_cover"], $settingData["file_name"] . "_cover");

            if ($filename === false) {
                return [
                    "success" => false,
                    "message" => "PDF cover upload failed: " . $this->imageUploadHandler->getLastError(),
                ];
            }

            $uploadedData["pdf_cover_image"] = IMAGES_DIR . "/" . $filename;
        }

        // Handle manual PDF upload
        if ($this->pdfUploadHandler->hasUploadedFile("manual_pdf")) {
            $filename = $this->pdfUploadHandler->upload($_FILES["manual_pdf"], $settingData["file_name"]);

            if ($filename === false) {
                return [
                    "success" => false,
                    "message" => "Manual PDF upload failed: " . $this->pdfUploadHandler->getLastError(),
                ];
            }

            $uploadedData["manual_pdf_path"] = REPORTS_DIR . "/" . $filename;
        }

        return ["success" => true, "data" => $uploadedData];
    }

    /**
     * Get all settings
     * @return array All settings
     */
    public function getAllSettings() {
        return $this->settingsManager->getAllSettings();
    }

    /**
     * Get setting by file name
     * @param string $fileName File name
     * @return array|null Setting data or null
     */
    public function getSettingByFileName($fileName) {
        return $this->settingsManager->getSettingByFileName($fileName);
    }

    /**
     * Get available shortcodes from CSV columns
     * @return array Array of available shortcodes
     */
    public function getAvailableShortcodes() {
        $shortcodes = [
            "special" => ["[Chart]", "[ArticleImage]", "[Current Date]"],
            "data" => [],
        ];

        if (file_exists(DATA_CSV_FILE)) {
            $csvHandle = fopen(DATA_CSV_FILE, "r");
            if ($csvHandle) {
                $headers = fgetcsv($csvHandle);
                fclose($csvHandle);
                if ($headers) {
                    foreach ($headers as $header) {
                        $shortcodes["data"][] = "[" . trim($header) . "]";
                    }
                }
            }
        }

        return $shortcodes;
    }

    /**
     * Handle report generation request
     * @return array Response with success status and message
     */
    public function handleGenerate() {
        if ($_SERVER["REQUEST_METHOD"] !== "POST") {
            return ["success" => false, "message" => "Invalid request method"];
        }

        $generationResult = $this->generateAllReports();

        // Calculate success statistics
        $successCount = 0;
        $totalCount = 0;

        if (isset($generationResult["results"]) && is_array($generationResult["results"])) {
            $totalCount = count($generationResult["results"]);

            foreach ($generationResult["results"] as $result) {
                if ($result["html"] || $result["pdf"] || $result["flipbook"]) {
                    $successCount++;
                }
            }
        }

        $message = "Generated reports for {$successCount} of {$totalCount} configurations. <a href='/reports' target='_blank'>Check reports here.</a>";
        return ["success" => $successCount > 0, "message" => $message];
    }

    /**
     * Generate reports for all settings
     * @return array Result with all generation results
     */
    private function generateAllReports() {
        $results = [];
        $allSettings = $this->settingsManager->getAllSettings();

        if (empty($allSettings)) {
            return [
                "success" => false,
                "message" => "No report settings found",
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
    private function generateReportForSetting($settings) {
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
            $csvReader = new CsvDataReader(DATA_CSV_FILE);
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
            $htmlPath = REPORTS_DIR . "/" . $fileName . ".html";
            $result["html"] = $htmlGenerator->saveToFile($htmlPath);

            if (!$result["html"]) {
                $result["errors"][] = "Failed to generate HTML report";
            }

            // Generate PDF report
            $pdfGenerator = new PdfReportGenerator($settings, $htmlGenerator);
            $pdfPath = REPORTS_DIR . "/" . $fileName . ".pdf";
            $result["pdf"] = $pdfGenerator->saveToFile($pdfPath);

            if (!$result["pdf"]) {
                $result["errors"][] = "Failed to generate PDF report (install wkhtmltopdf or upload manual PDF)";
            }

            // Generate Flipbook report
            $flipbookGenerator = new FlipbookGenerator($settings, $stocks, $shortcodeProcessor);
            $flipbookPath = REPORTS_DIR . "/" . $fileName . "-flipbook.html";
            $result["flipbook"] = $flipbookGenerator->saveToFile($flipbookPath);

            if (!$result["flipbook"]) {
                $result["errors"][] = "Failed to generate Flipbook report";
            }
        } catch (Exception $e) {
            $result["errors"][] = "Exception: " . $e->getMessage();
        }

        return $result;
    }
}
