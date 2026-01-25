<?php
/**
 * ShortcodeProvider - Provides available shortcodes
 * Single Responsibility: Only handles shortcode discovery and listing
 * PHP 5.5 compatible
 */

class ShortcodeProvider {
    private $csvFilePath;
    private $specialShortcodes;
    private $cachedDataShortcodes = null;

    /**
     * Constructor
     * @param string $csvFilePath Path to CSV data file
     * @param array|null $specialShortcodes Custom special shortcodes (optional)
     */
    public function __construct($csvFilePath, $specialShortcodes = null) {
        $this->csvFilePath = $csvFilePath;
        $this->specialShortcodes = $specialShortcodes !== null ? $specialShortcodes : ["[Chart]", "[ArticleImage]", "[Current Date]"];
    }

    /**
     * Get available shortcodes from CSV columns
     * @return array Array of available shortcodes grouped by type
     */
    public function getAvailableShortcodes() {
        $shortcodes = [
            "special" => $this->specialShortcodes,
            "data" => [],
        ];

        $dataShortcodes = $this->getDataShortcodes();
        if (!empty($dataShortcodes)) {
            $shortcodes["data"] = $dataShortcodes;
        }

        return $shortcodes;
    }

    /**
     * Get data shortcodes from CSV headers (cached after first read)
     * @return array Array of data shortcodes
     */
    private function getDataShortcodes() {
        if ($this->cachedDataShortcodes !== null) {
            return $this->cachedDataShortcodes;
        }

        $this->cachedDataShortcodes = [];

        if (!file_exists($this->csvFilePath)) {
            return $this->cachedDataShortcodes;
        }

        $csvHandle = fopen($this->csvFilePath, "r");
        if (!$csvHandle) {
            return $this->cachedDataShortcodes;
        }

        $headers = fgetcsv($csvHandle);
        fclose($csvHandle);

        if ($headers) {
            foreach ($headers as $header) {
                $this->cachedDataShortcodes[] = "[" . trim($header) . "]";
            }
        }

        return $this->cachedDataShortcodes;
    }

    /**
     * Get only special shortcodes
     * @return array Special shortcodes
     */
    public function getSpecialShortcodes() {
        return $this->specialShortcodes;
    }

    /**
     * Add a special shortcode
     * @param string $shortcode Shortcode to add
     */
    public function addSpecialShortcode($shortcode) {
        if (!in_array($shortcode, $this->specialShortcodes)) {
            $this->specialShortcodes[] = $shortcode;
        }
    }

    /**
     * Remove a special shortcode
     * @param string $shortcode Shortcode to remove
     */
    public function removeSpecialShortcode($shortcode) {
        $key = array_search($shortcode, $this->specialShortcodes);
        if ($key !== false) {
            unset($this->specialShortcodes[$key]);
            $this->specialShortcodes = array_values($this->specialShortcodes);
        }
    }

    /**
     * Check if shortcode is available
     * @param string $shortcode Shortcode to check
     * @return bool True if available
     */
    public function isShortcodeAvailable($shortcode) {
        $allShortcodes = $this->getAvailableShortcodes();
        $all = array_merge($allShortcodes["special"], $allShortcodes["data"]);
        return in_array($shortcode, $all);
    }
}
