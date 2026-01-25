<?php
/**
 * ShortcodeProcessorInterface - Contract for shortcode processing
 * PHP 5.5 compatible
 */

interface ShortcodeProcessorInterface {
    /**
     * Process all shortcodes in content
     * @param string $content Content with shortcodes
     * @param string $format Output format ('html' or 'flipbook')
     * @return string Processed content
     */
    public function process($content, $format = "html");

    /**
     * Set current stock data context
     * @param array $stockData Stock data array
     */
    public function setStockData($stockData);

    /**
     * Set article image path
     * @param string $imagePath Path to article image
     */
    public function setArticleImagePath($imagePath);

    /**
     * Get current date formatted
     * @return string Current date
     */
    public function getCurrentDate();
}
