<?php
/**
 * HtmlReportGenerator - Generates HTML format reports
 * PHP 5.5 compatible
 */

class HtmlReportGenerator
{
    private $settings;
    private $stocks;
    private $shortcodeProcessor;

    /**
     * Constructor
     * @param array $settings Report settings
     * @param array $stocks Stock data array
     * @param ShortcodeProcessor $shortcodeProcessor Shortcode processor instance
     */
    public function __construct($settings, $stocks, $shortcodeProcessor)
    {
        $this->settings = $settings;
        $this->stocks = $stocks;
        $this->shortcodeProcessor = $shortcodeProcessor;
    }

    /**
     * Generate HTML report
     * @return string Generated HTML content
     */
    public function generate()
    {
        $html = $this->generateHtmlStructure();
        return $html;
    }

    /**
     * Generate complete HTML structure
     * @return string HTML content
     */
    private function generateHtmlStructure()
    {
        $html = '<!DOCTYPE html>' . "\n";
        $html .= '<html lang="en">' . "\n";
        $html .= '<head>' . "\n";
        $html .= '    <meta charset="UTF-8">' . "\n";
        $html .= '    <meta name="viewport" content="width=device-width, initial-scale=1.0">' . "\n";
        $html .= '    <title>' . $this->escapeHtml($this->settings['report_title']) . '</title>' . "\n";
        $html .= $this->generateStyles();
        $html .= '</head>' . "\n";
        $html .= '<body>' . "\n";
        $html .= $this->generateBody();
        $html .= '</body>' . "\n";
        $html .= '</html>';

        return $html;
    }

    /**
     * Generate CSS styles
     * @return string Style tag with CSS
     */
    private function generateStyles()
    {
        $css = '<style>' . "\n";
        $css .= 'body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 1200px; margin: 0 auto; padding: 20px; }' . "\n";
        $css .= 'h1 { color: #2c3e50; border-bottom: 3px solid #3498db; padding-bottom: 10px; }' . "\n";
        $css .= 'h2 { color: #34495e; margin-top: 30px; }' . "\n";
        $css .= '.stock-container { margin: 30px 0; padding: 20px; border: 1px solid #ddd; border-radius: 5px; background: #f9f9f9; }' . "\n";
        $css .= '.stock-container h2 { margin-top: 0; }' . "\n";
        $css .= '.tradingview-widget-container { margin: 20px 0; }' . "\n";
        $css .= '.pagebreak { page-break-before: always; }' . "\n";
        $css .= '.termsblk { margin-top: 50px; padding: 20px; background: #f5f5f5; border-radius: 5px; font-size: 0.9em; }' . "\n";
        $css .= '.termsblk p { margin: 10px 0; }' . "\n";
        $css .= 'strong { color: #2c3e50; }' . "\n";
        $css .= '</style>' . "\n";

        return $css;
    }

    /**
     * Generate body content
     * @return string Body HTML
     */
    private function generateBody()
    {
        $body = '<div id="article-body">' . "\n";

        // Add report title
        $body .= '<h1>' . $this->escapeHtml($this->settings['report_title']) . '</h1>' . "\n";

        // Add report intro
        if (!empty($this->settings['report_intro_html'])) {
            $body .= $this->shortcodeProcessor->process($this->settings['report_intro_html'], 'html') . "\n";
        }

        // Add stock blocks
        foreach ($this->stocks as $index => $stock) {
            $this->shortcodeProcessor->setStockData($stock);
            $stockBlock = $this->generateStockBlock($stock, $index);
            $body .= $stockBlock . "\n";
        }

        // Add disclaimer
        if (!empty($this->settings['disclaimer_html'])) {
            $body .= $this->shortcodeProcessor->process($this->settings['disclaimer_html'], 'html') . "\n";
        }

        $body .= '</div>' . "\n";

        return $body;
    }

    /**
     * Generate individual stock block
     * @param array $stock Stock data
     * @param int $index Stock index
     * @return string Stock block HTML
     */
    private function generateStockBlock($stock, $index)
    {
        $pageBreakClass = $index > 0 ? ' pagebreak' : '';

        if (!empty($this->settings['stock_block_html'])) {
            $blockHtml = '<div class="stock-container' . $pageBreakClass . '">' . "\n";
            $blockHtml .= $this->shortcodeProcessor->process($this->settings['stock_block_html'], 'html') . "\n";
            $blockHtml .= '</div>' . "\n";
        } else {
            $blockHtml = $this->generateDefaultStockBlock($stock, $pageBreakClass);
        }

        return $blockHtml;
    }

    /**
     * Generate default stock block if template not provided
     * @param array $stock Stock data
     * @param string $pageBreakClass Page break CSS class
     * @return string Default stock block HTML
     */
    private function generateDefaultStockBlock($stock, $pageBreakClass)
    {
        $html = '<div class="stock-container' . $pageBreakClass . '">' . "\n";
        $html .= '    <div>' . "\n";

        $company = isset($stock['Company']) ? $stock['Company'] : '';
        $exchange = isset($stock['Exchange']) ? $stock['Exchange'] : '';
        $ticker = isset($stock['Ticker']) ? $stock['Ticker'] : '';

        $html .= '        <h2>' . $this->escapeHtml($company) . ' (' . $this->escapeHtml($exchange) . ':' . $this->escapeHtml($ticker) . ')</h2>' . "\n";

        // Add chart
        if (!empty($ticker)) {
            $this->shortcodeProcessor->setStockData($stock);
            $chartHtml = $this->shortcodeProcessor->process('[Chart]', 'html');
            $html .= $chartHtml . '<br>' . "\n";
        }

        // Add stock details
        if (isset($stock['Price'])) {
            $html .= '        <strong>Stock Price: </strong>$' . $this->escapeHtml($stock['Price']) . '<br>' . "\n";
        }

        if (isset($stock['Market Cap'])) {
            $formattedMarketCap = CsvDataReader::formatMarketCap($stock['Market Cap']);
            $html .= '        <strong>Market Cap</strong>: ' . $formattedMarketCap . '<br>' . "\n";
        }

        $html .= '    </div>' . "\n";

        // Add description
        if (isset($stock['Description'])) {
            $html .= '    <div class="w-100 mt-2">' . $this->escapeHtml($stock['Description']) . '</div>' . "\n";
        }

        $html .= '</div>' . "\n";

        return $html;
    }

    /**
     * Save generated HTML to file
     * @param string $outputPath Output file path
     * @return bool Success status
     */
    public function saveToFile($outputPath)
    {
        $html = $this->generate();
        return file_put_contents($outputPath, $html) !== false;
    }

    /**
     * Escape HTML special characters
     * @param string $text Text to escape
     * @return string Escaped text
     */
    private function escapeHtml($text)
    {
        return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
    }
}
