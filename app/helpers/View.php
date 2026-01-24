<?php
/**
 * View - Simple template renderer
 * Dependency Inversion: Views directory is configurable
 * PHP 5.5 compatible
 */

class View {
    /**
     * @var string|null Views directory path (configurable)
     */
    private static $viewsDir = null;

    /**
     * Configure the views directory
     * @param string $viewsDir Path to views directory
     */
    public static function setViewsDir($viewsDir) {
        self::$viewsDir = rtrim($viewsDir, "/");
    }

    /**
     * Get the views directory
     * @return string Views directory path
     */
    public static function getViewsDir() {
        if (self::$viewsDir !== null) {
            return self::$viewsDir;
        }

        // Fall back to APP_DIR constant for backward compatibility
        if (defined("APP_DIR")) {
            return APP_DIR . "/views";
        }

        // Last resort: try to determine from current file location
        return dirname(dirname(__FILE__)) . "/views";
    }

    /**
     * Render a view template with data
     * @param string $viewPath Path to view file (relative to views directory)
     * @param array $data Data to pass to the view
     * @return string Rendered HTML
     */
    public static function render($viewPath, $data = []) {
        $viewFile = self::getViewsDir() . "/" . $viewPath . ".php";

        if (!file_exists($viewFile)) {
            throw new Exception("View file not found: " . $viewFile);
        }

        extract($data);

        ob_start();
        include $viewFile;
        return ob_get_clean();
    }

    /**
     * Escape HTML to prevent XSS
     * @param string $text Text to escape
     * @return string Escaped text
     */
    public static function escape($text) {
        return htmlspecialchars($text, ENT_QUOTES, "UTF-8");
    }

    /**
     * Render and output a view directly
     * @param string $viewPath Path to view file
     * @param array $data Data to pass to the view
     */
    public static function show($viewPath, $data = []) {
        echo self::render($viewPath, $data);
    }

    /**
     * Check if a view exists
     * @param string $viewPath Path to view file (relative to views directory)
     * @return bool True if view exists
     */
    public static function exists($viewPath) {
        $viewFile = self::getViewsDir() . "/" . $viewPath . ".php";
        return file_exists($viewFile);
    }

    /**
     * Reset the views directory to default
     * Useful for testing
     */
    public static function resetViewsDir() {
        self::$viewsDir = null;
    }
}
