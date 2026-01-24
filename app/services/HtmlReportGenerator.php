<?php
/**
 * HtmlReportGenerator - Generates HTML format reports
 * PHP 5.5 compatible
 */

class HtmlReportGenerator {
    private $settings;
    private $stocks;
    private $shortcodeProcessor;
    private $isPdfMode = false;

    /**
     * Constructor
     * @param array $settings Report settings
     * @param array $stocks Stock data array
     * @param ShortcodeProcessor $shortcodeProcessor Shortcode processor instance
     */
    public function __construct($settings, $stocks, $shortcodeProcessor) {
        $this->settings = $settings;
        $this->stocks = $stocks;
        $this->shortcodeProcessor = $shortcodeProcessor;

        // Set article image path if available
        if (!empty($settings["article_image"])) {
            $this->shortcodeProcessor->setArticleImagePath($settings["article_image"]);
        }
    }

    /**
     * Set PDF mode (enables cover page)
     * @param bool $isPdfMode Whether generating for PDF output
     */
    public function setPdfMode($isPdfMode) {
        $this->isPdfMode = $isPdfMode;
    }

    /**
     * Generate HTML report
     * @return string Generated HTML content
     */
    public function generate() {
        $html = $this->generateHtmlStructure();
        return $html;
    }

    /**
     * Generate complete HTML structure
     * @return string HTML content
     */
    private function generateHtmlStructure() {
        $html = "<!DOCTYPE html>" . "\n";
        $html .= '<html lang="en">' . "\n";
        $html .= "<head>" . "\n";
        $html .= '    <meta charset="UTF-8">' . "\n";
        $html .= '    <meta name="viewport" content="width=device-width, initial-scale=1.0">' . "\n";
        $html .= "    <title>" . $this->escapeHtml($this->settings["report_title"]) . "</title>" . "\n";
        $html .= $this->generateStyles();
        $html .= "</head>" . "\n";
        $html .= "<body>" . "\n";
        $html .= $this->generateBody();
        $html .= "</body>" . "\n";
        $html .= "</html>";

        return $html;
    }

    /**
     * Generate CSS styles
     * @return string Style tag with CSS
     */
    private function generateStyles() {
        $css = "<style>" . "\n";
        $css .= "@page { margin: 0; }" . "\n";
        $css .= "html, body { height: 100%; }" . "\n";
        $css .= "body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; }" . "\n";
        $css .= "#article-body { margin: 0; padding: 0; }" . "\n";
        $css .= ".report-content { max-width: 1200px; margin: 0 auto; padding: 20px; }" . "\n";
        $css .= "h1 { color: #2c3e50; border-bottom: 3px solid #3498db; padding-bottom: 10px; }" . "\n";
        $css .= "h2 { color: #34495e; margin-top: 30px; }" . "\n";
        $css .= ".stock-container { margin: 30px 0; padding: 20px; border: 1px solid #ddd; border-radius: 5px; background: #f9f9f9; }" . "\n";
        $css .= ".stock-container h2 { margin-top: 0; }" . "\n";
        $css .= ".tradingview-widget-container { margin: 20px 0; }" . "\n";
        $css .= ".cover-page { page-break-after: always; margin: 0; padding: 0; width: 100%; height: 100%; min-height: 100vh; }" . "\n";
        $css .= ".cover-image { display: block; width: 100%; height: 100%; object-fit: cover; }" . "\n";
        $css .= ".pagebreak { page-break-before: always; }" . "\n";
        $css .= ".termsblk { margin-top: 50px; padding: 20px; background: #f5f5f5; border-radius: 5px; font-size: 0.9em; }" . "\n";
        $css .= ".termsblk p { margin: 10px 0; }" . "\n";
        $css .= "strong { color: #2c3e50; }" . "\n";
        $css .= "</style>" . "\n";

        return $css;
    }

    /**
     * Generate body content
     * @return string Body HTML
     */
    private function generateBody() {
        $body = '<div id="article-body">' . "\n";

        if ($this->isPdfMode) {
            $body .= $this->generateCoverPage();
        }

        $body .= '<div class="report-content">' . "\n";

        // Add report title
        $body .= "<h1>" . $this->escapeHtml($this->settings["report_title"]) . "</h1>" . "\n";

        // Add report intro once at the top
        $reportIntro = !empty($this->settings["report_intro_html"]) ? $this->settings["report_intro_html"] : $this->loadDataFile(DEFAULT_REPORT_INTRO_HTML);
        if (!empty($reportIntro)) {
            $body .= $this->shortcodeProcessor->process($reportIntro, "html") . "\n";
        }

        // Add stock blocks
        foreach ($this->stocks as $stock) {
            $this->shortcodeProcessor->setStockData($stock);
            $stockBlock = $this->generateStockBlock($stock);
            $body .= $stockBlock . "\n";
        }

        // Add disclaimer once at the bottom (with page break for PDF)
        $disclaimer = !empty($this->settings["disclaimer_html"]) ? $this->settings["disclaimer_html"] : $this->loadDataFile(DEFAULT_REPORT_DISCLAIMER_HTML);
        if (!empty($disclaimer)) {
            $body .= '<div class="pagebreak">' . "\n";
            $body .= $this->shortcodeProcessor->process($disclaimer, "html") . "\n";
            $body .= "</div>" . "\n";
        }

        $body .= "</div>" . "\n";
        $body .= "</div>" . "\n";

        return $body;
    }

    /**
     * Generate PDF cover page HTML
     * @return string Cover page HTML or empty string
     */
    private function generateCoverPage() {
        $coverImagePath = !empty($this->settings["pdf_cover_image"]) ? $this->settings["pdf_cover_image"] : "";

        if (empty($coverImagePath) || !file_exists($coverImagePath)) {
            return "";
        }

        $dataUri = $this->convertImageToDataUri($coverImagePath);
        $html = '<div class="cover-page">' . "\n";
        $html .= '    <img class="cover-image" src="' . $dataUri . '" alt="">' . "\n";
        $html .= "</div>" . "\n";

        return $html;
    }

    /**
     * Convert image file to base64 data URI
     * @param string $imagePath Path to image file
     * @return string Base64 data URI
     */
    private function convertImageToDataUri($imagePath) {
        $imageData = file_get_contents($imagePath);
        $mimeType = $this->getImageMimeType($imagePath);
        $base64Data = base64_encode($imageData);

        return "data:" . $mimeType . ";base64," . $base64Data;
    }

    /**
     * Get image MIME type from file
     * @param string $filePath Path to image file
     * @return string MIME type
     */
    private function getImageMimeType($filePath) {
        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

        $mimeTypes = [
            "jpg" => "image/jpeg",
            "jpeg" => "image/jpeg",
            "png" => "image/png",
            "gif" => "image/gif",
            "webp" => "image/webp",
        ];

        if (isset($mimeTypes[$extension])) {
            return $mimeTypes[$extension];
        }

        return "image/jpeg";
    }

    /**
     * Generate individual stock block
     * @param array $stock Stock data
     * @return string Stock block HTML
     */
    private function generateStockBlock($stock) {
        $pageBreakClass = " pagebreak";

        $stockBlockHtml = !empty($this->settings["stock_block_html"])
            ? $this->settings["stock_block_html"]
            : $this->loadDataFile(DEFAULT_REPORT_STOCK_BLOCK_HTML);

        if ($this->isPdfMode) {
            $stockBlockHtml = str_replace("[Chart]", "", $stockBlockHtml);
        }

        $blockHtml = '<div class="stock-container' . $pageBreakClass . '">' . "\n";
        $blockHtml .= $this->shortcodeProcessor->process($stockBlockHtml, "html") . "\n";
        $blockHtml .= "</div>" . "\n";

        return $blockHtml;
    }

    /**
     * Save generated HTML to file
     * @param string $outputPath Output file path
     * @return bool Success status
     */
    public function saveToFile($outputPath) {
        $html = $this->generate();
        return file_put_contents($outputPath, $html) !== false;
    }

    /**
     * Escape HTML special characters
     * @param string $text Text to escape
     * @return string Escaped text
     */
    private function escapeHtml($text) {
        return htmlspecialchars($text, ENT_QUOTES, "UTF-8");
    }

    /**
     * Load content from data file as fallback
     * @param string $filename File name in data directory
     * @return string File content or empty string
     */
    private function loadDataFile($filename) {
        $filePath = __DIR__ . "/../../data/" . $filename;
        if (file_exists($filePath)) {
            return file_get_contents($filePath);
        }
        return "";
    }
}
