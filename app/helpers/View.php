<?php
/**
 * View - Simple template renderer
 * Supports both static usage (backward compatible) and instance usage (testable)
 * PHP 5.5 compatible
 */

class View {
    /**
     * @var string|null Static views directory path (for backward compatibility)
     */
    private static $viewsDir = null;

    /**
     * @var string Instance views directory path
     */
    private $instanceViewsDir;

    /**
     * Constructor for instance-based usage
     * @param string|null $viewsDir Path to views directory (optional)
     */
    public function __construct($viewsDir = null) {
        if ($viewsDir !== null) {
            $this->instanceViewsDir = rtrim($viewsDir, "/");
        } else {
            $this->instanceViewsDir = self::resolveDefaultViewsDir();
        }
    }

    /**
     * Resolve the default views directory
     * @return string Views directory path
     */
    private static function resolveDefaultViewsDir() {
        if (defined("APP_DIR")) {
            return APP_DIR . "/views";
        }
        return dirname(dirname(__FILE__)) . "/views";
    }

    // =========================================================================
    // Instance Methods (preferred for new code and testing)
    // =========================================================================

    /**
     * Get the instance views directory
     * @return string Views directory path
     */
    public function getViewsDirectory() {
        return $this->instanceViewsDir;
    }

    /**
     * Render a view template with data (instance method)
     * @param string $viewPath Path to view file (relative to views directory)
     * @param array $data Data to pass to the view
     * @return string Rendered HTML
     */
    public function renderView($viewPath, $data = []) {
        $viewFile = $this->instanceViewsDir . "/" . $viewPath . ".php";

        if (!file_exists($viewFile)) {
            throw new Exception("View file not found: " . $viewFile);
        }

        extract($data);

        ob_start();
        include $viewFile;
        return ob_get_clean();
    }

    /**
     * Render and output a view directly (instance method)
     * @param string $viewPath Path to view file
     * @param array $data Data to pass to the view
     */
    public function showView($viewPath, $data = []) {
        echo $this->renderView($viewPath, $data);
    }

    /**
     * Check if a view exists (instance method)
     * @param string $viewPath Path to view file (relative to views directory)
     * @return bool True if view exists
     */
    public function viewExists($viewPath) {
        $viewFile = $this->instanceViewsDir . "/" . $viewPath . ".php";
        return file_exists($viewFile);
    }

    // =========================================================================
    // Static Methods (backward compatible, kept for existing code)
    // =========================================================================

    /**
     * Configure the static views directory
     * @param string $viewsDir Path to views directory
     * @deprecated Use instance-based View instead
     */
    public static function setViewsDir($viewsDir) {
        self::$viewsDir = rtrim($viewsDir, "/");
    }

    /**
     * Get the static views directory
     * @return string Views directory path
     * @deprecated Use instance-based View instead
     */
    public static function getViewsDir() {
        if (self::$viewsDir !== null) {
            return self::$viewsDir;
        }
        return self::resolveDefaultViewsDir();
    }

    /**
     * Render a view template with data (static method)
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
     * Render and output a view directly (static method)
     * @param string $viewPath Path to view file
     * @param array $data Data to pass to the view
     */
    public static function show($viewPath, $data = []) {
        echo self::render($viewPath, $data);
    }

    /**
     * Check if a view exists (static method)
     * @param string $viewPath Path to view file (relative to views directory)
     * @return bool True if view exists
     */
    public static function exists($viewPath) {
        $viewFile = self::getViewsDir() . "/" . $viewPath . ".php";
        return file_exists($viewFile);
    }

    /**
     * Reset the static views directory to default
     * Useful for testing
     */
    public static function resetViewsDir() {
        self::$viewsDir = null;
    }
}
