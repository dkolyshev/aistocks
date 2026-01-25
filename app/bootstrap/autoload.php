<?php

// PSR-0 compatible autoloader with a small class map
$classMap = [
    "FileSystemInterface" => APP_DIR . "/models/Contracts/FileSystemInterface.php",
    "SettingsManagerInterface" => APP_DIR . "/models/Contracts/SettingsManagerInterface.php",
    "CsvDataReaderInterface" => APP_DIR . "/models/Contracts/CsvDataReaderInterface.php",
    "FileSystem" => APP_DIR . "/models/Support/FileSystem.php",
    "SettingsManager" => APP_DIR . "/models/SettingsManager.php",
    "CsvDataReader" => APP_DIR . "/models/CsvDataReader.php",
    "FileUploadHandler" => APP_DIR . "/services/FileUploadHandler.php",
    "ShortcodeProcessor" => APP_DIR . "/services/ShortcodeProcessor.php",
    "FileLoaderService" => APP_DIR . "/services/Support/FileLoaderService.php",
    "HtmlReportGenerator" => APP_DIR . "/services/HtmlReportGenerator.php",
    "PdfReportGenerator" => APP_DIR . "/services/PdfReportGenerator.php",
    "FlipbookGenerator" => APP_DIR . "/services/FlipbookGenerator.php",
    "View" => APP_DIR . "/helpers/View.php",
    "StockFormatter" => APP_DIR . "/helpers/StockFormatter.php",
    "FieldStateResolver" => APP_DIR . "/helpers/FieldStateResolver.php",
    "ShortcodeProvider" => APP_DIR . "/controllers/Support/ShortcodeProvider.php",
    "DataSourceProvider" => APP_DIR . "/controllers/Support/DataSourceProvider.php",
    "ReportGenerationOrchestrator" => APP_DIR . "/controllers/Support/ReportGenerationOrchestrator.php",
    "Router" => APP_DIR . "/controllers/Support/Router.php",
    "SettingsController" => APP_DIR . "/controllers/SettingsController.php",
    "ReportFileController" => APP_DIR . "/controllers/ReportFileController.php",
    "ReportController" => APP_DIR . "/controllers/ReportController.php",
];

spl_autoload_register(function ($className) use ($classMap) {
    if (isset($classMap[$className])) {
        require_once $classMap[$className];
        return;
    }

    $relativePath = str_replace(["\\", "_"], "/", $className) . ".php";
    $filePath = APP_DIR . "/" . $relativePath;

    if (is_file($filePath)) {
        require_once $filePath;
    }
});
