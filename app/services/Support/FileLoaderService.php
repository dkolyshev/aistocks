<?php
/**
 * FileLoaderService - Handles file loading operations
 * PHP 5.5 compatible
 */

class FileLoaderService {
    private $dataDir;
    private $publicDir;

    /**
     * Constructor
     * @param string|null $dataDir Data directory path (defaults to data/ relative to app)
     * @param string|null $publicDir Public directory path (defaults to PUBLIC_DIR constant)
     */
    public function __construct($dataDir = null, $publicDir = null) {
        // Default data dir: from /app/services/Support/ go up to project root, then /data/
        $this->dataDir = $dataDir !== null ? $dataDir : dirname(dirname(dirname(__DIR__))) . "/data";
        $this->publicDir = $publicDir !== null ? $publicDir : (defined("PUBLIC_DIR") ? PUBLIC_DIR : "");
    }

    /**
     * Load content from data file
     * @param string $filename File name in data directory
     * @return string File content or empty string if not found
     */
    public function loadDataFile($filename) {
        $filePath = $this->dataDir . "/" . $filename;
        if (file_exists($filePath)) {
            $content = file_get_contents($filePath);
            return $content !== false ? $content : "";
        }
        return "";
    }

    /**
     * Load CSS styles from public assets
     * @param string $cssPath Relative path from public directory
     * @return string CSS content or empty string if not found
     */
    public function loadStyles($cssPath) {
        $filePath = $this->publicDir . "/" . $cssPath;
        if (file_exists($filePath)) {
            $content = file_get_contents($filePath);
            return $content !== false ? $content : "";
        }
        return "";
    }

    /**
     * Load JavaScript from file
     * @param string $jsPath Full path to JS file
     * @return string JS content or empty string if not found
     */
    public function loadScript($jsPath) {
        if (file_exists($jsPath)) {
            $content = file_get_contents($jsPath);
            return $content !== false ? $content : "";
        }
        return "";
    }

    /**
     * Check if file exists in data directory
     * @param string $filename File name in data directory
     * @return bool True if file exists
     */
    public function dataFileExists($filename) {
        return file_exists($this->dataDir . "/" . $filename);
    }

    /**
     * Get full path to data file
     * @param string $filename File name in data directory
     * @return string Full file path
     */
    public function getDataFilePath($filename) {
        return $this->dataDir . "/" . $filename;
    }
}
