<?php
/**
 * Application Configuration
 * PHP 5.5 compatible configuration file
 */

date_default_timezone_set("UTC");

// Base URL for subdirectory installations
// Set this to your app's base path if not at domain root
// Examples: "" (root), "/myapp", "/tools/testproject/contractord/aistocks"
if (!defined("BASE_URL")) {
    // For subdirectory test, use: "/tools/testproject/contractord/aistocks"
    // For production at root, use: ""
    define("BASE_URL", "");
}

if (!defined("APP_ROOT")) {
    define("APP_ROOT", dirname(dirname(dirname(__FILE__))));
}

define("APP_DIR", APP_ROOT . "/app");
define("PUBLIC_DIR", APP_ROOT . "/public");
define("REPORTS_DIR", APP_ROOT . "/reports");
define("IMAGES_DIR", APP_ROOT . "/images");
define("DATA_DIR", APP_ROOT . "/data");

define("SETTINGS_FILE", APP_ROOT . "/reportSettings.json");
define("DATA_CSV_FILE", DATA_DIR . "/extended-data.csv");

define("ALLOWED_IMAGE_TYPES", "image/jpeg,image/jpg,image/png");
define("ALLOWED_PDF_TYPES", "application/pdf");
define("MAX_FILE_SIZE", 5242880); // 5MB
define("ARTICLE_IMAGE_MAX_WIDTH", 200);
define("ARTICLE_IMAGE_MAX_HEIGHT", 200);

define("DATE_FORMAT", "Y-m-d H:i:s");

// TradingView widget configuration
define("TRADINGVIEW_WIDGET_URL", "https://www.tradingview-widget.com/embed-widget/mini-symbol-overview/");
define("TRADINGVIEW_WIDGET_WIDTH", 600);
define("TRADINGVIEW_WIDGET_HEIGHT", 220);

// URLs
define("REPORT_MANAGER_URL", BASE_URL . "/reportManager");

// Default fields
define("DEFAULT_REPORT_INTRO_HTML", "reportIntro.html");
define("DEFAULT_REPORT_DISCLAIMER_HTML", "disclaimer.html");
define("DEFAULT_REPORT_STOCK_BLOCK_HTML", "stockBlock.html");

// Financial Modeling Prep API Configuration
define("FMP_API_KEY", "3rtO1ZEiKHJHkkQOqwrwPsSa9ugfT7xQ"); // Set your FMP API key here
define("FMP_BASE_URL", "https://financialmodelingprep.com/stable");
define("FMP_API_TIMEOUT", 30); // Timeout in seconds
define("FMP_CACHE_TTL", 3600); // Cache time-to-live in seconds (1 hour)
define("FMP_RATE_LIMIT", 300); // Requests per minute
define("FMP_CACHE_DIR", APP_ROOT . "/cache/fmp"); // Cache directory for API responses
