<?php
/**
 * SettingsController - Handles settings CRUD operations
 * Single Responsibility: Only handles settings-related HTTP requests
 * PHP 5.5 compatible
 */

class SettingsController {
    private $settingsManager;
    private $imageUploadHandler;
    private $pdfUploadHandler;
    private $htmlSanitizer;
    private $requestValidator;

    /**
     * Constructor with dependency injection
     * @param SettingsManagerInterface $settingsManager Settings manager
     * @param FileUploadHandler $imageUploadHandler Image upload handler
     * @param FileUploadHandler $pdfUploadHandler PDF upload handler
     * @param HtmlSanitizer $htmlSanitizer HTML sanitizer
     * @param RequestValidator $requestValidator Request validator
     */
    public function __construct($settingsManager, $imageUploadHandler, $pdfUploadHandler, $htmlSanitizer, $requestValidator) {
        $this->settingsManager = $settingsManager;
        $this->imageUploadHandler = $imageUploadHandler;
        $this->pdfUploadHandler = $pdfUploadHandler;
        $this->htmlSanitizer = $htmlSanitizer;
        $this->requestValidator = $requestValidator;
    }

    /**
     * Handle the request based on action
     * @return array Response with success status and message
     */
    public function handleRequest() {
        $error = $this->requestValidator->requirePost();
        if ($error !== null) {
            return $error;
        }

        $action = isset($_POST["action"]) ? $_POST["action"] : Action::ADD;

        if ($action === Action::DELETE) {
            return $this->handleDelete();
        }

        return $this->handleSubmission($action);
    }

    /**
     * Handle settings form submission (add/update)
     * @param string $action Action type (add or update)
     * @return array Response with success status and message
     */
    public function handleSubmission($action = Action::ADD) {
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
        if ($action === Action::UPDATE) {
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
        $sourceType = isset($_POST["source_type"]) ? trim($_POST["source_type"]) : "csv";

        $data = [
            "file_name" => isset($_POST["file_name"]) ? trim($_POST["file_name"]) : "",
            "report_title" => isset($_POST["report_title"]) ? trim($_POST["report_title"]) : "",
            "author_name" => isset($_POST["author_name"]) ? trim($_POST["author_name"]) : "",
            "stock_count" => isset($_POST["stock_count"]) ? intval($_POST["stock_count"]) : 6,
            "article_image" => isset($_POST["existing_article_image"]) ? $_POST["existing_article_image"] : "",
            "pdf_cover_image" => isset($_POST["existing_pdf_cover"]) ? $_POST["existing_pdf_cover"] : "",
            "report_intro_html" => $this->sanitizeHtml(isset($_POST["report_intro_html"]) ? $_POST["report_intro_html"] : ""),
            "report_intro_html_state" => $this->extractFieldState("report_intro_html_state"),
            "stock_block_html" => $this->sanitizeHtml(isset($_POST["stock_block_html"]) ? $_POST["stock_block_html"] : ""),
            "stock_block_html_state" => $this->extractFieldState("stock_block_html_state"),
            "disclaimer_html" => $this->sanitizeHtml(isset($_POST["disclaimer_html"]) ? $_POST["disclaimer_html"] : ""),
            "disclaimer_html_state" => $this->extractFieldState("disclaimer_html_state"),
            "manual_pdf_path" => isset($_POST["existing_manual_pdf"]) ? $_POST["existing_manual_pdf"] : "",
            "data_source_type" => $sourceType,
        ];

        // Set data_source and api_config based on source type
        if ($sourceType === "csv") {
            $csvSource = isset($_POST["api_placeholder"]) ? trim($_POST["api_placeholder"]) : "";
            $data["data_source"] = $csvSource;
        } elseif ($sourceType === "api") {
            $apiEndpoint = isset($_POST["api_endpoint"]) ? trim($_POST["api_endpoint"]) : "";

            // Map endpoint to DataSourceFactory format
            $endpointMap = array(
                "most-actives" => "fmp-most-actives"
            );
            $factoryEndpoint = isset($endpointMap[$apiEndpoint]) ? $endpointMap[$apiEndpoint] : "fmp-most-actives";
            $data["data_source"] = $factoryEndpoint;

            // Collect API configuration
            $apiConfig = array(
                "endpoint" => $apiEndpoint
            );

            // Collect API filters (applied client-side after receiving API data)
            $apiFilters = array();
            if (isset($_POST["api_filter_marketcap_min"]) && $_POST["api_filter_marketcap_min"] !== "") {
                $apiFilters["marketcap_min"] = intval($_POST["api_filter_marketcap_min"]);
            }
            if (isset($_POST["api_filter_marketcap_max"]) && $_POST["api_filter_marketcap_max"] !== "") {
                $apiFilters["marketcap_max"] = intval($_POST["api_filter_marketcap_max"]);
            }
            if (isset($_POST["api_filter_price_min"]) && $_POST["api_filter_price_min"] !== "") {
                $apiFilters["price_min"] = floatval($_POST["api_filter_price_min"]);
            }
            if (isset($_POST["api_filter_price_max"]) && $_POST["api_filter_price_max"] !== "") {
                $apiFilters["price_max"] = floatval($_POST["api_filter_price_max"]);
            }
            if (isset($_POST["api_filter_exchange"]) && !empty($_POST["api_filter_exchange"])) {
                $apiFilters["exchange"] = trim($_POST["api_filter_exchange"]);
            }
            if (isset($_POST["api_filter_country"]) && !empty($_POST["api_filter_country"])) {
                $apiFilters["country"] = trim($_POST["api_filter_country"]);
            }

            if (!empty($apiFilters)) {
                $apiConfig["filters"] = $apiFilters;
            }

            $data["api_config"] = $apiConfig;
        }

        return $data;
    }

    /**
     * Sanitize HTML content before persisting.
     * @param string $html Raw HTML
     * @return string Sanitized HTML
     */
    private function sanitizeHtml($html) {
        return $this->htmlSanitizer->sanitize($html);
    }

    /**
     * Extract and validate field state from POST
     * @param string $fieldName POST field name
     * @return string Valid state value (default, custom, or empty)
     */
    private function extractFieldState($fieldName) {
        $validStates = ["default", "custom", "empty"];
        $state = isset($_POST[$fieldName]) ? trim($_POST[$fieldName]) : "default";
        return in_array($state, $validStates, true) ? $state : "default";
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
            $filename = $this->imageUploadHandler->upload(
                $_FILES["article_image"],
                $settingData["file_name"] . "_article",
                ARTICLE_IMAGE_MAX_WIDTH,
                ARTICLE_IMAGE_MAX_HEIGHT
            );

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
     * Get all settings (delegate to manager)
     * @return array All settings
     */
    public function getAllSettings() {
        return $this->settingsManager->getAllSettings();
    }

    /**
     * Get setting by file name (delegate to manager)
     * @param string $fileName File name
     * @return array|null Setting data or null
     */
    public function getSettingByFileName($fileName) {
        return $this->settingsManager->getSettingByFileName($fileName);
    }
}
