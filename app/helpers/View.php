<?php
/**
 * View - Simple template renderer (static helper)
 * PHP 5.5 compatible
 */

class View {
    /**
     * @var string|null Static views directory path (for backward compatibility)
     */
    private static $viewsDir = null;

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
     * Generate a URL with the base path prefix
     * @param string $path Path relative to app root (e.g., "/assets/css/style.css")
     * @return string Full URL with base path
     */
    public static function url($path) {
        $base = defined("BASE_URL") ? BASE_URL : "";
        // Ensure path starts with /
        if (strlen($path) > 0 && $path[0] !== "/") {
            $path = "/" . $path;
        }
        return $base . $path;
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

    // =========================================================================
    // CSRF Protection Helpers
    // =========================================================================

    /**
     * @var CsrfServiceInterface|null CSRF service instance
     */
    private static $csrfService = null;

    /**
     * Set the CSRF service instance
     * @param CsrfServiceInterface $csrfService CSRF service
     */
    public static function setCsrfService($csrfService) {
        self::$csrfService = $csrfService;
    }

    /**
     * Render a hidden CSRF token field for forms
     * @return string HTML hidden input field
     */
    public static function csrfField() {
        if (self::$csrfService === null) {
            return '';
        }
        $fieldName = self::$csrfService->getTokenFieldName();
        $token = self::$csrfService->getToken();
        return '<input type="hidden" name="' . self::escape($fieldName) . '" value="' . self::escape($token) . '">';
    }

    /**
     * Get the current CSRF token value
     * @return string Token value or empty string
     */
    public static function csrfToken() {
        if (self::$csrfService === null) {
            return '';
        }
        return self::$csrfService->getToken();
    }
}
