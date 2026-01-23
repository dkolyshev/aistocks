<?php
/**
 * Application Configuration
 * PHP 5.5 compatible configuration file
 */

if (!defined('APP_ROOT')) {
    define('APP_ROOT', dirname(dirname(dirname(__FILE__))));
}

define('APP_DIR', APP_ROOT . '/app');
define('REPORTS_DIR', APP_ROOT . '/reports');
define('IMAGES_DIR', APP_ROOT . '/images');
define('DATA_DIR', APP_ROOT . '/data');

define('SETTINGS_FILE', APP_ROOT . '/reportSettings.json');
define('DATA_CSV_FILE', DATA_DIR . '/data.csv');

define('ALLOWED_IMAGE_TYPES', 'image/jpeg,image/jpg,image/png');
define('ALLOWED_PDF_TYPES', 'application/pdf');
define('MAX_FILE_SIZE', 5242880); // 5MB

define('DATE_FORMAT', 'Y-m-d H:i:s');

// TradingView widget configuration
define('TRADINGVIEW_WIDGET_URL', 'https://www.tradingview-widget.com/embed-widget/mini-symbol-overview/');
define('TRADINGVIEW_WIDGET_WIDTH', 600);
define('TRADINGVIEW_WIDGET_HEIGHT', 220);
