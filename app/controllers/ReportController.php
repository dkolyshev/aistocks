<?php
/**
 * ReportController - Facade that delegates to specialized controllers
 * Single Responsibility: Route requests to appropriate handlers
 * Dependency Inversion: Accepts dependencies via constructor
 * PHP 5.5 compatible
 */

class ReportController {
    private $settingsController;
    private $reportFileController;
    private $reportOrchestrator;
    private $shortcodeProvider;
    private $dataSourceProvider;
    private $requestValidator;

    /**
     * Constructor with dependency injection
     * All dependencies are required - wire them in the composition root (index.php)
     *
     * @param SettingsController $settingsController Settings controller
     * @param ReportFileController $reportFileController Report file controller
     * @param ReportGenerationOrchestrator $reportOrchestrator Report generation orchestrator
     * @param ShortcodeProvider $shortcodeProvider Shortcode provider
     * @param DataSourceProvider $dataSourceProvider Data source provider
     * @param RequestValidator $requestValidator Request validator
     */
    public function __construct(
        SettingsController $settingsController,
        ReportFileController $reportFileController,
        ReportGenerationOrchestrator $reportOrchestrator,
        ShortcodeProvider $shortcodeProvider,
        DataSourceProvider $dataSourceProvider,
        RequestValidator $requestValidator = null
    ) {
        $this->settingsController = $settingsController;
        $this->reportFileController = $reportFileController;
        $this->reportOrchestrator = $reportOrchestrator;
        $this->shortcodeProvider = $shortcodeProvider;
        $this->dataSourceProvider = $dataSourceProvider;
        $this->requestValidator = $requestValidator !== null ? $requestValidator : new RequestValidator();
    }

    /**
     * Handle settings form submission (delegates to SettingsController)
     * @return array Response with success status and message
     */
    public function handleSettingsSubmission() {
        $error = $this->requestValidator->requirePost();
        if ($error !== null) {
            return $error;
        }

        $action = isset($_POST["action"]) ? $_POST["action"] : "add";
        return $this->settingsController->handleSubmission($action);
    }

    /**
     * Handle delete request (delegates to SettingsController)
     * @return array Response with success status and message
     */
    public function handleDelete() {
        $error = $this->requestValidator->requirePost();
        if ($error !== null) {
            return $error;
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
        $error = $this->requestValidator->requirePost();
        if ($error !== null) {
            return $error;
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
     * Get available data sources (delegates to DataSourceProvider)
     * @return array Array of available CSV file names
     */
    public function getAvailableDataSources() {
        return $this->dataSourceProvider->getAvailableDataSources();
    }
}
