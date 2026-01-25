<?php
/**
 * ReportController - Facade that delegates to specialized controllers
 * Single Responsibility: Route requests to appropriate handlers
 * Dependency Inversion: Accepts dependencies via constructor
 * PHP 5.5 compatible
 */

require_once dirname(__FILE__) . "/SettingsController.php";
require_once dirname(__FILE__) . "/ReportFileController.php";
require_once dirname(__FILE__) . "/Support/ReportGenerationOrchestrator.php";
require_once dirname(__FILE__) . "/Support/ShortcodeProvider.php";
require_once dirname(__FILE__) . "/Support/DataSourceProvider.php";

class ReportController {
    private $settingsController;
    private $reportFileController;
    private $reportOrchestrator;
    private $shortcodeProvider;
    private $dataSourceProvider;

    /**
     * Constructor with dependency injection
     * Dependencies are optional for backward compatibility
     *
     * @param SettingsController|null $settingsController Settings controller
     * @param ReportFileController|null $reportFileController Report file controller
     * @param ReportGenerationOrchestrator|null $reportOrchestrator Report generation orchestrator
     * @param ShortcodeProvider|null $shortcodeProvider Shortcode provider
     * @param DataSourceProvider|null $dataSourceProvider Data source provider
     */
    public function __construct($settingsController = null, $reportFileController = null, $reportOrchestrator = null, $shortcodeProvider = null, $dataSourceProvider = null) {
        // Create default instances if not provided (backward compatibility)
        if ($settingsController === null) {
            $settingsManager = new SettingsManager(SETTINGS_FILE);
            $imageUploadHandler = new FileUploadHandler(IMAGES_DIR, ALLOWED_IMAGE_TYPES, MAX_FILE_SIZE);
            $pdfUploadHandler = new FileUploadHandler(REPORTS_DIR, ALLOWED_PDF_TYPES, MAX_FILE_SIZE);
            $settingsController = new SettingsController($settingsManager, $imageUploadHandler, $pdfUploadHandler);
        }

        if ($reportFileController === null) {
            $reportFileController = new ReportFileController(REPORTS_DIR, null, DATE_FORMAT);
        }

        if ($reportOrchestrator === null) {
            $settingsManager = new SettingsManager(SETTINGS_FILE);
            $reportOrchestrator = new ReportGenerationOrchestrator($settingsManager, DATA_DIR, REPORTS_DIR);
        }

        if ($shortcodeProvider === null) {
            $shortcodeProvider = new ShortcodeProvider(DATA_CSV_FILE);
        }

        if ($dataSourceProvider === null) {
            $dataSourceProvider = new DataSourceProvider(DATA_DIR);
        }

        $this->settingsController = $settingsController;
        $this->reportFileController = $reportFileController;
        $this->reportOrchestrator = $reportOrchestrator;
        $this->shortcodeProvider = $shortcodeProvider;
        $this->dataSourceProvider = $dataSourceProvider;
    }

    /**
     * Handle settings form submission (delegates to SettingsController)
     * @return array Response with success status and message
     */
    public function handleSettingsSubmission() {
        if ($_SERVER["REQUEST_METHOD"] !== "POST") {
            return ["success" => false, "message" => "Invalid request method"];
        }

        $action = isset($_POST["action"]) ? $_POST["action"] : "add";
        return $this->settingsController->handleSubmission($action);
    }

    /**
     * Handle delete request (delegates to SettingsController)
     * @return array Response with success status and message
     */
    public function handleDelete() {
        if ($_SERVER["REQUEST_METHOD"] !== "POST") {
            return ["success" => false, "message" => "Invalid request method"];
        }

        return $this->settingsController->handleDelete();
    }

    /**
     * Get all settings (delegates to SettingsController)
     * @return array All settings
     */
    public function getAllSettings() {
        return $this->settingsController->getAllSettings();
    }

    /**
     * Get setting by file name (delegates to SettingsController)
     * @param string $fileName File name
     * @return array|null Setting data or null
     */
    public function getSettingByFileName($fileName) {
        return $this->settingsController->getSettingByFileName($fileName);
    }

    /**
     * Get available shortcodes (delegates to ShortcodeProvider)
     * @return array Array of available shortcodes
     */
    public function getAvailableShortcodes() {
        return $this->shortcodeProvider->getAvailableShortcodes();
    }

    /**
     * Handle report generation request (delegates to ReportGenerationOrchestrator)
     * @return array Response with success status and message
     */
    public function handleGenerate() {
        if ($_SERVER["REQUEST_METHOD"] !== "POST") {
            return ["success" => false, "message" => "Invalid request method"];
        }

        $generationResult = $this->reportOrchestrator->generateAllReports();

        $stats = ["success_count" => 0, "total_count" => 0];

        if (isset($generationResult["results"]) && is_array($generationResult["results"])) {
            $stats = $this->reportOrchestrator->calculateStatistics($generationResult["results"]);
        }

        $message = "Generated reports for {$stats["success_count"]} of {$stats["total_count"]} configurations. Check reports <a href='#reports-table'>here</a> or <a href='/reports' target='_blank'>here.</a>";
        return ["success" => $stats["success_count"] > 0, "message" => $message];
    }

    /**
     * Get list of report files (delegates to ReportFileController)
     * @return array Array of report file info
     */
    public function getReportFiles() {
        return $this->reportFileController->getReportFiles();
    }

    /**
     * Handle delete single report file request (delegates to ReportFileController)
     * @return array Response with success status and message
     */
    public function handleDeleteReport() {
        return $this->reportFileController->handleDeleteReport();
    }

    /**
     * Handle delete all report files request (delegates to ReportFileController)
     * @return array Response with success status and message
     */
    public function handleDeleteAllReports() {
        return $this->reportFileController->handleDeleteAllReports();
    }

    /**
     * Get the settings controller instance
     * @return SettingsController
     */
    public function getSettingsController() {
        return $this->settingsController;
    }

    /**
     * Get the report file controller instance
     * @return ReportFileController
     */
    public function getReportFileController() {
        return $this->reportFileController;
    }

    /**
     * Get the report orchestrator instance
     * @return ReportGenerationOrchestrator
     */
    public function getReportOrchestrator() {
        return $this->reportOrchestrator;
    }

    /**
     * Get the shortcode provider instance
     * @return ShortcodeProvider
     */
    public function getShortcodeProvider() {
        return $this->shortcodeProvider;
    }

    /**
     * Get available data sources (delegates to DataSourceProvider)
     * @return array Array of available CSV file names
     */
    public function getAvailableDataSources() {
        return $this->dataSourceProvider->getAvailableDataSources();
    }

    /**
     * Get the data source provider instance
     * @return DataSourceProvider
     */
    public function getDataSourceProvider() {
        return $this->dataSourceProvider;
    }
}
