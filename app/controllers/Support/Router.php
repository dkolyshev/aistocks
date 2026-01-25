<?php
/**
 * Router - Front controller router for HTTP requests
 * Single Responsibility: Dispatch requests and prepare view data
 * PHP 5.5 compatible
 */

class Router {
    /**
     * @var ReportController
     */
    private $controller;

    /**
     * @var FileLoaderService
     */
    private $fileLoaderService;

    /**
     * @var CsrfServiceInterface|null
     */
    private $csrfService;

    /**
     * Constructor with dependency injection
     * @param ReportController $controller Main controller
     * @param FileLoaderService $fileLoaderService File loader service
     * @param CsrfServiceInterface|null $csrfService CSRF protection service
     */
    public function __construct($controller, $fileLoaderService, $csrfService = null) {
        $this->controller = $controller;
        $this->fileLoaderService = $fileLoaderService;
        $this->csrfService = $csrfService;
    }

    /**
     * Dispatch the current HTTP request
     * @param array|null $get Query parameters
     * @param array|null $post Post parameters
     * @param array|null $server Server parameters
     */
    public function dispatch($get = null, $post = null, $server = null) {
        $get = $get !== null ? $get : $_GET;
        $post = $post !== null ? $post : $_POST;
        $server = $server !== null ? $server : $_SERVER;

        if ($this->handleTemplateRequest($get)) {
            return;
        }

        $flash = $this->handlePost($post, $server);
        $message = $flash["message"];
        $messageType = $flash["messageType"];

        if (isset($get["message"])) {
            $message = urldecode($get["message"]);
            $messageType = "success";
        }

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
     * Handle AJAX request for default template content
     * @param array $get Query parameters
     * @return bool True if handled
     */
    private function handleTemplateRequest($get) {
        if (!isset($get["action"]) || $get["action"] !== Action::GET_TEMPLATE) {
            return false;
        }

        header("Content-Type: application/json");

        $templateFile = isset($get["template"]) ? basename($get["template"]) : "";
        $allowedTemplates = [
            DEFAULT_REPORT_INTRO_HTML,
            DEFAULT_REPORT_STOCK_BLOCK_HTML,
            DEFAULT_REPORT_DISCLAIMER_HTML,
        ];

        if (empty($templateFile) || !in_array($templateFile, $allowedTemplates, true)) {
            echo json_encode(["success" => false, "error" => "Invalid template"]);
            return true;
        }

        $content = $this->fileLoaderService->loadDataFile($templateFile);
        echo json_encode(["success" => true, "content" => $content]);
        return true;
    }

    /**
     * Handle POST actions and return flash message data
     * @param array $post Post parameters
     * @param array $server Server parameters
     * @return array Message data
     */
    private function handlePost($post, $server) {
        $message = "";
        $messageType = "info";

        if (!isset($server["REQUEST_METHOD"]) || $server["REQUEST_METHOD"] !== "POST") {
            return [
                "message" => $message,
                "messageType" => $messageType,
            ];
        }

        // Validate CSRF token
        if (!$this->validateCsrfToken($post)) {
            return [
                "message" => "Invalid security token. Please refresh the page and try again.",
                "messageType" => "danger",
            ];
        }

        $action = isset($post["action"]) ? $post["action"] : "";

        if ($action === Action::DELETE) {
            $result = $this->controller->handleDelete();
        } elseif ($action === Action::GENERATE) {
            $result = $this->controller->handleGenerate();
        } elseif ($action === Action::DELETE_REPORT) {
            $result = $this->controller->handleDeleteReport();
        } elseif ($action === Action::DELETE_ALL_REPORTS) {
            $result = $this->controller->handleDeleteAllReports();
        } else {
            $result = $this->controller->handleSettingsSubmission();
        }

        return [
            "message" => $result["message"],
            "messageType" => $result["success"] ? "success" : "danger",
        ];
    }

    /**
     * Validate CSRF token from POST data
     * @param array $post POST parameters
     * @return bool True if valid or no CSRF service configured
     */
    private function validateCsrfToken($post) {
        if ($this->csrfService === null) {
            return true;
        }

        $fieldName = $this->csrfService->getTokenFieldName();
        $token = isset($post[$fieldName]) ? $post[$fieldName] : "";

        return $this->csrfService->validateToken($token);
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
