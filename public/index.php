<?php
/**
 * Report Manager - CRUD Interface for Stock Report Settings
 * Entry point with proper dependency wiring (Composition Root)
 * PHP 5.5 compatible
 */

// Load configuration
require_once __DIR__ . "/../app/config/config.php";

// Load model contracts and implementations
require_once APP_DIR . "/models/Contracts/FileSystemInterface.php";
require_once APP_DIR . "/models/Contracts/SettingsManagerInterface.php";
require_once APP_DIR . "/models/Contracts/CsvDataReaderInterface.php";
require_once APP_DIR . "/models/Support/FileSystem.php";
require_once APP_DIR . "/models/SettingsManager.php";
require_once APP_DIR . "/models/CsvDataReader.php";

// Load services
require_once APP_DIR . "/services/FileUploadHandler.php";
require_once APP_DIR . "/services/ShortcodeProcessor.php";
require_once APP_DIR . "/services/HtmlReportGenerator.php";
require_once APP_DIR . "/services/PdfReportGenerator.php";
require_once APP_DIR . "/services/FlipbookGenerator.php";

// Load helpers
require_once APP_DIR . "/helpers/View.php";
require_once APP_DIR . "/helpers/StockFormatter.php";

// Load controller contracts and implementations
require_once APP_DIR . "/controllers/Contracts/ControllerInterface.php";
require_once APP_DIR . "/controllers/Support/ShortcodeProvider.php";
require_once APP_DIR . "/controllers/Support/ReportGenerationOrchestrator.php";
require_once APP_DIR . "/controllers/SettingsController.php";
require_once APP_DIR . "/controllers/ReportFileController.php";
require_once APP_DIR . "/controllers/ReportController.php";

// ============================================================
// Composition Root - Wire all dependencies here
// ============================================================

// Create file system abstraction
$fileSystem = new FileSystem();

// Create settings manager with file system injection
$settingsManager = new SettingsManager(SETTINGS_FILE, $fileSystem);

// Create file upload handlers
$imageUploadHandler = new FileUploadHandler(IMAGES_DIR, ALLOWED_IMAGE_TYPES, MAX_FILE_SIZE);
$pdfUploadHandler = new FileUploadHandler(REPORTS_DIR, ALLOWED_PDF_TYPES, MAX_FILE_SIZE);

// Create specialized controllers
$settingsController = new SettingsController($settingsManager, $imageUploadHandler, $pdfUploadHandler);
$reportFileController = new ReportFileController(REPORTS_DIR, $fileSystem, DATE_FORMAT);

// Create support services
$shortcodeProvider = new ShortcodeProvider(DATA_CSV_FILE);
$reportOrchestrator = new ReportGenerationOrchestrator($settingsManager, DATA_CSV_FILE, REPORTS_DIR);

// Create main controller (facade) with all dependencies injected
$controller = new ReportController($settingsController, $reportFileController, $reportOrchestrator, $shortcodeProvider);

// ============================================================
// Handle HTTP Requests
// ============================================================

$message = "";
$messageType = "info";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $action = isset($_POST["action"]) ? $_POST["action"] : "";

    if ($action === "delete") {
        $result = $controller->handleDelete();
    } elseif ($action === "generate") {
        $result = $controller->handleGenerate();
    } elseif ($action === "delete_report") {
        $result = $controller->handleDeleteReport();
    } elseif ($action === "delete_all_reports") {
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

// ============================================================
// Prepare View Data
// ============================================================

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

// ============================================================
// Render Views
// ============================================================

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
