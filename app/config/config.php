<?php
/**
 * Application Configuration
 * PHP 5.5 compatible configuration file
 */

date_default_timezone_set("UTC");

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

// Default fields
define("DEFAULT_REPORT_INTRO_HTML", "reportIntro.html");
define("DEFAULT_REPORT_DISCLAIMER_HTML", "disclaimer.html");
define("DEFAULT_REPORT_STOCK_BLOCK_HTML", "stockBlock.html");
