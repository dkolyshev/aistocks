<?php
/**
 * Report Manager - CRUD Interface for Stock Report Settings
 * Entry point with proper dependency wiring (Composition Root)
 * PHP 5.5 compatible
 */

// Load configuration
require_once __DIR__ . "/../app/config/config.php";
require APP_DIR . "/bootstrap/autoload.php";

// Create file system abstraction
$fileSystem = new FileSystem();

// Create settings manager with file system injection
$settingsManager = new SettingsManager(SETTINGS_FILE, $fileSystem);
$settingsManager->ensureFileExists();

// Create file upload handlers
$imageUploadHandler = new FileUploadHandler(IMAGES_DIR, ALLOWED_IMAGE_TYPES, MAX_FILE_SIZE);
$pdfUploadHandler = new FileUploadHandler(REPORTS_DIR, ALLOWED_PDF_TYPES, MAX_FILE_SIZE);

// Create specialized controllers
$htmlSanitizer = new HtmlSanitizer();
$settingsController = new SettingsController($settingsManager, $imageUploadHandler, $pdfUploadHandler, $htmlSanitizer);
$reportFileController = new ReportFileController(REPORTS_DIR, $fileSystem, DATE_FORMAT);

// Create support services
$shortcodeProvider = new ShortcodeProvider(DATA_CSV_FILE);
$dataSourceProvider = new DataSourceProvider(DATA_DIR);
$reportServiceFactory = new ReportServiceFactory();
$reportOrchestrator = new ReportGenerationOrchestrator($settingsManager, DATA_DIR, REPORTS_DIR, $reportServiceFactory);

// Create main controller (facade) with all dependencies injected
$controller = new ReportController($settingsController, $reportFileController, $reportOrchestrator, $shortcodeProvider, $dataSourceProvider);

$router = new Router($controller, new FileLoaderService());
$router->dispatch();
