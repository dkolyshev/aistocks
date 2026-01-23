<?php
/**
 * Report Manager - CRUD Interface for Stock Report Settings
 * PHP 5.5 compatible
 */

// Load configuration and classes
require_once __DIR__ . "/app/config/config.php";
require_once APP_DIR . "/models/SettingsManager.php";
require_once APP_DIR . "/services/FileUploadHandler.php";
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
$content = View::render("report-manager/index", array(
    "editMode" => $editMode,
    "editData" => $editData,
    "allSettings" => $allSettings
));

// Render the full page with layout
View::show("report-manager/layout", array(
    "message" => $message,
    "messageType" => $messageType,
    "content" => $content
));
