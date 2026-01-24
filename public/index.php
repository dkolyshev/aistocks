<?php
/**
 * Report Manager - CRUD Interface for Stock Report Settings
 * PHP 5.5 compatible
 */

// Load configuration and classes
require_once __DIR__ . "/../app/config/config.php";
require_once APP_DIR . "/models/SettingsManager.php";
require_once APP_DIR . "/models/CsvDataReader.php";
require_once APP_DIR . "/services/FileUploadHandler.php";
require_once APP_DIR . "/services/ShortcodeProcessor.php";
require_once APP_DIR . "/services/HtmlReportGenerator.php";
require_once APP_DIR . "/services/PdfReportGenerator.php";
require_once APP_DIR . "/services/FlipbookGenerator.php";
require_once APP_DIR . "/controllers/ReportController.php";
require_once APP_DIR . "/helpers/View.php";

// Initialize controller
$controller = new ReportController();

// Handle form submissions
$message = "";
$messageType = "info";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (isset($_POST["action"]) && $_POST["action"] === "delete") {
        $result = $controller->handleDelete();
    } elseif (isset($_POST["action"]) && $_POST["action"] === "generate") {
        $result = $controller->handleGenerate();
    } elseif (isset($_POST["action"]) && $_POST["action"] === "delete_report") {
        $result = $controller->handleDeleteReport();
    } elseif (isset($_POST["action"]) && $_POST["action"] === "delete_all_reports") {
        $result = $controller->handleDeleteAllReports();
    } else {
        $result = $controller->handleSettingsSubmission();
    }

    $message = $result["message"];
    $messageType = $result["success"] ? "success" : "danger";
}

// Handle GET messages (from redirects)
if (isset($_GET["message"])) {
    $message = urldecode($_GET["message"]);
    $messageType = "success";
}

// Get all settings for display
$allSettings = $controller->getAllSettings();

// Get available shortcodes
$availableShortcodes = $controller->getAvailableShortcodes();

// Get report files for display
$reportFiles = $controller->getReportFiles();

// Get setting for editing if edit parameter is present
$editMode = false;
$editData = null;
if (isset($_GET["edit"])) {
    $editData = $controller->getSettingByFileName($_GET["edit"]);
    if ($editData !== null) {
        $editMode = true;
    }
}

// Render the main content (form + table)
$content = View::render("report-manager/index", [
    "editMode" => $editMode,
    "editData" => $editData,
    "allSettings" => $allSettings,
    "availableShortcodes" => $availableShortcodes,
    "reportFiles" => $reportFiles,
]);

// Render the full page with layout
View::show("report-manager/layout", [
    "message" => $message,
    "messageType" => $messageType,
    "content" => $content,
]);
