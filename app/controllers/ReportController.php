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
}
