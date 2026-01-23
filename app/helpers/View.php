<?php
/**
 * View - Simple template renderer
 * PHP 5.5 compatible
 */

class View {
    /**
     * Render a view template with data
     * @param string $viewPath Path to view file (relative to app/views/)
     * @param array $data Data to pass to the view
     * @return string Rendered HTML
     */
    public static function render($viewPath, $data = array()) {
        $viewFile = APP_DIR . "/views/" . $viewPath . ".php";

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
    public static function show($viewPath, $data = array()) {
        echo self::render($viewPath, $data);
    }
}
