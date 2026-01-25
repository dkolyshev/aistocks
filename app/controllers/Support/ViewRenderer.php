<?php
/**
 * ViewRenderer - Handles view data preparation and rendering
 * Single Responsibility: Prepare and render the main application view
 * PHP 5.5 compatible
 */

class ViewRenderer {
    /**
     * @var ReportController
     */
    private $controller;

    /**
     * Constructor with dependency injection
     * @param ReportController $controller Main controller for data retrieval
     */
    public function __construct($controller) {
        $this->controller = $controller;
    }

    /**
     * Render the main application view
     * @param array $get Query parameters
     * @param string $message Flash message to display
     * @param string $messageType Message type (success, danger, info)
     */
    public function render($get, $message, $messageType) {
        $viewData = $this->prepareViewData($get);

        $content = View::render("report-manager/index", [
            "editMode" => $viewData["editMode"],
            "editData" => $viewData["editData"],
            "fieldStates" => $viewData["fieldStates"],
            "allSettings" => $viewData["allSettings"],
            "availableShortcodes" => $viewData["availableShortcodes"],
            "availableDataSources" => $viewData["availableDataSources"],
            "reportFiles" => $viewData["reportFiles"],
            "formAction" => REPORT_MANAGER_URL,
        ]);

        View::show("report-manager/layout", [
            "message" => $message,
            "messageType" => $messageType,
            "content" => $content,
            "formAction" => REPORT_MANAGER_URL,
        ]);
    }

    /**
     * Prepare view data for rendering
     * @param array $get Query parameters
     * @return array View data
     */
    private function prepareViewData($get) {
        $allSettings = $this->controller->getAllSettings();
        $availableShortcodes = $this->controller->getAvailableShortcodes();
        $availableDataSources = $this->controller->getAvailableDataSources();
        $reportFiles = $this->controller->getReportFiles();

        $editMode = false;
        $editData = null;

        if (isset($get["edit"])) {
            $editData = $this->controller->getSettingByFileName($get["edit"]);
            if ($editData !== null) {
                $editMode = true;
            }
        }

        $fieldStates = FieldStateResolver::resolveAll($editMode, $editData);

        return [
            "editMode" => $editMode,
            "editData" => $editData,
            "fieldStates" => $fieldStates,
            "allSettings" => $allSettings,
            "availableShortcodes" => $availableShortcodes,
            "availableDataSources" => $availableDataSources,
            "reportFiles" => $reportFiles,
        ];
    }
}
