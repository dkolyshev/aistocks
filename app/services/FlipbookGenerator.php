<?php
/**
 * FlipbookGenerator - Generates interactive flipbook HTML reports
 * PHP 5.5 compatible
 */

class FlipbookGenerator {
    private $settings;
    private $stocks;
    private $shortcodeProcessor;

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
     * Generate flipbook HTML
     * @return string Generated HTML content
     */
    public function generate() {
        $html = $this->generateFlipbookStructure();
        return $html;
    }

    /**
     * Generate complete flipbook HTML structure
     * @return string HTML content
     */
    private function generateFlipbookStructure() {
        $html = "<!DOCTYPE html>" . "\n";
        $html .= "<html>" . "\n";
        $html .= "<head>" . "\n";
        $html .= "<title>" . $this->escapeHtml($this->settings["file_name"]) . "</title>" . "\n";
        $html .= '<meta charset="UTF-8">' . "\n";
        $html .= '<script src="https://code.jquery.com/jquery-1.11.0.min.js"></script>' . "\n";
        $html .= '<script src="https://go.trendadvisor.net/tools/flipbook/js/turn.min.js" type="text/javascript"></script>' . "\n";
        $html .= '<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">' . "\n";
        $html .= '<link rel="stylesheet" href="https://go.trendadvisor.net/tools/flipbook/css/flipbook.css">' . "\n";
        $html .= $this->generateFlipbookStyles();
        $html .= "</head>" . "\n";
        $html .= "<body>" . "\n";
        $html .= $this->generateFlipbookBody();
        $html .= $this->generateFlipbookScript();
        $html .= "</body>" . "\n";
        $html .= "</html>";

        return $html;
    }

    /**
     * Generate flipbook-specific styles
     * @return string Style tag
     */
    private function generateFlipbookStyles() {
        $css = "<style>" . "\n";
        $css .= ".stock-container { padding: 25px; background: white; height: 100%; box-sizing: border-box; }" . "\n";
        $css .= ".stock-container-2 { height: 100%; overflow-y: auto; }" . "\n";
        $css .= ".stock-description-2 { font-size: 0.9em; margin-top: 10px; }" . "\n";
        $css .= ".page { background: white; }" . "\n";
        $css .= "h2 { margin-top: 0; color: #2c3e50; }" . "\n";
        $css .= ".stock-intro, .stock-disclaimer { height: 100%; overflow-y: auto; }" . "\n";
        $css .= ".page-nav { display: flex; justify-content: center; gap: 5px; margin-top: 10px; flex-wrap: wrap; }" . "\n";
        $css .= ".page-nav-btn { width: 30px; height: 30px; border: 1px solid #ccc; background: #fff; cursor: pointer; border-radius: 4px; font-size: 12px; }" . "\n";
        $css .= ".page-nav-btn:hover { background: #f0f0f0; }" . "\n";
        $css .= ".page-nav-btn.active { background: #667eea; color: #fff; border-color: #667eea; }" . "\n";
        $css .= "</style>" . "\n";

        return $css;
    }

    /**
     * Generate flipbook body content
     * @return string Body HTML
     */
    private function generateFlipbookBody() {
        $body = '<div class="wrapper">' . "\n";
        $body .= '<div class="flipbook-viewport">' . "\n";
        $body .= '<div class="container">' . "\n";
        $body .= '<div class="flipbook" id="flipbook">' . "\n";

        // Add cover page
        $body .= $this->generateCoverPage();

        // Add disclaimer page
        $body .= $this->generateDisclaimerPage();

        // Add intro page
        $body .= $this->generateIntroPage();

        // Add stock pages (each stock on its own page)
        foreach ($this->stocks as $index => $stock) {
            $this->shortcodeProcessor->setStockData($stock);
            $body .= $this->generateStockPage($stock, $index);
        }

        $body .= "</div>" . "\n";
        $body .= $this->generateFlipControls();
        $body .= "</div>" . "\n";
        $body .= "</div>" . "\n";
        $body .= "</div>" . "\n";

        return $body;
    }

    /**
     * Generate cover page
     * @return string Cover page HTML
     */
    private function generateCoverPage() {
        $coverImagePath = !empty($this->settings["pdf_cover_image"]) ? $this->settings["pdf_cover_image"] : "";

        $html = '<div class="page">' . "\n";

        if (!empty($coverImagePath) && file_exists($coverImagePath)) {
            $html .= $this->generateCoverImageHtml($coverImagePath);
        } else {
            $html .= $this->generateDefaultCoverHtml();
        }

        $html .= "</div>" . "\n";

        return $html;
    }

    /**
     * Generate cover image HTML with embedded base64 data
     * @param string $imagePath Path to cover image file
     * @return string Image HTML tag
     */
    private function generateCoverImageHtml($imagePath) {
        $dataUri = $this->convertImageToDataUri($imagePath);

        return '<img src="' . $dataUri . '" draggable="false" alt="" height="100%" width="100%" />' . "\n";
    }

    /**
     * Generate default cover HTML (gradient background with title)
     * @return string Cover HTML
     */
    private function generateDefaultCoverHtml() {
        $html =
            '<div style="display: flex; align-items: center; justify-content: center; height: 100%; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">' .
            "\n";
        $html .= '<h1 style="color: white; text-align: center; padding: 20px;">' . $this->escapeHtml($this->settings["report_title"]) . "</h1>" . "\n";
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
     * Generate disclaimer page
     * @return string Disclaimer page HTML
     */
    private function generateDisclaimerPage() {
        $disclaimer = !empty($this->settings["disclaimer_html"])
            ? $this->settings["disclaimer_html"]
            : $this->loadDataFile(DEFAULT_REPORT_DISCLAIMER_HTML);

        if (empty($disclaimer)) {
            return "";
        }

        $html = '<div class="page pagebreak">' . "\n";
        $html .= '<div class="stock-container">' . "\n";
        $html .= '<div class="stock-container-2">' . "\n";
        $html .= '<div class="stock-disclaimer">' . "\n";
        $html .= $this->shortcodeProcessor->process($disclaimer, "flipbook") . "\n";
        $html .= "</div>" . "\n";
        $html .= "</div>" . "\n";
        $html .= "</div>" . "\n";
        $html .= "</div>" . "\n";

        return $html;
    }

    /**
     * Generate intro page
     * @return string Intro page HTML
     */
    private function generateIntroPage() {
        $intro = !empty($this->settings["report_intro_html"])
            ? $this->settings["report_intro_html"]
            : $this->loadDataFile(DEFAULT_REPORT_INTRO_HTML);

        if (empty($intro)) {
            return "";
        }

        $html = '<div class="page pagebreak">' . "\n";
        $html .= '<div class="stock-container">' . "\n";
        $html .= '<div class="stock-container-2">' . "\n";
        $html .= '<div class="stock-intro">' . "\n";
        $html .= $this->shortcodeProcessor->process($intro, "flipbook") . "\n";
        $html .= "</div>" . "\n";
        $html .= "</div>" . "\n";
        $html .= "</div>" . "\n";
        $html .= "</div>" . "\n";

        return $html;
    }

    /**
     * Generate stock page (stock content only)
     * @param array $stock Stock data
     * @param int $index Stock index
     * @return string Stock page HTML
     */
    private function generateStockPage($stock, $index) {
        $html = '<div class="page pagebreak">' . "\n";
        $html .= '<div class="stock-container">' . "\n";
        $html .= '<div class="stock-container-2">' . "\n";

        // Add custom stock block if provided
        if (!empty($this->settings["stock_block_html"])) {
            $html .= $this->shortcodeProcessor->process($this->settings["stock_block_html"], "flipbook") . "\n";
        } else {
            // Generate default stock content
            $html .= $this->generateDefaultStockContent($stock, $index);
        }

        $html .= "</div>" . "\n";
        $html .= "</div>" . "\n";
        $html .= "</div>" . "\n";

        return $html;
    }

    /**
     * Generate default stock content when no custom template provided
     * @param array $stock Stock data
     * @param int $index Stock index
     * @return string Default stock HTML
     */
    private function generateDefaultStockContent($stock, $index) {
        $html = '<div class="order-md-1">' . "\n";

        $company = isset($stock["Company"]) ? $stock["Company"] : "";
        $exchange = isset($stock["Exchange"]) ? $stock["Exchange"] : "NASDAQ";
        $ticker = isset($stock["Ticker"]) ? $stock["Ticker"] : "";
        $stockNumber = $index + 1;

        $html .=
            '<h2 class="mt-1">' .
            $stockNumber .
            ") " .
            $this->escapeHtml($company) .
            " (" .
            $this->escapeHtml($exchange) .
            ":" .
            $this->escapeHtml($ticker) .
            ")</h2>" .
            "\n";

        // Add chart
        if (!empty($ticker)) {
            $chartHtml = $this->shortcodeProcessor->process("[Chart]", "flipbook");
            $html .= $chartHtml . "\n";
        }

        $html .= "<br>" . "\n";

        if (isset($stock["Price"])) {
            $html .= '<strong>Closing Price: </strong>$' . $this->escapeHtml($stock["Price"]) . "<br>" . "\n";
        }

        if (isset($stock["Market Cap"])) {
            $formattedMarketCap = CsvDataReader::formatMarketCap($stock["Market Cap"]);
            $html .= "<strong>Market Cap</strong>: " . $formattedMarketCap . "<br>" . "\n";
        }

        $html .= "</div>" . "\n";

        if (isset($stock["Description"])) {
            $html .= '<div class="w-100 mt-2 order-md-3 stock-description-2">' . $this->escapeHtml($stock["Description"]) . "</div>" . "\n";
        }

        return $html;
    }

    /**
     * Generate flip controls
     * @return string Flip controls HTML
     */
    private function generateFlipControls() {
        $html = '<div class="flip-control">' . "\n";
        $html .= '<a href="#" id="prev"><i class="fa fa-angle-left" style="font-size:3rem;color:black;font-weight: 600;"></i></a>' . "\n";
        $html .= '<a href="#" id="next"><i class="fa fa-angle-right" style="font-size:3rem;color:black;font-weight: 600;"></i></a>' . "\n";
        $html .= "</div>" . "\n";
        $html .= '<div class="page-nav" id="page-nav"></div>' . "\n";

        return $html;
    }

    /**
     * Generate flipbook JavaScript
     * @return string Script tag with JS
     */
    private function generateFlipbookScript() {
        $js = file_get_contents(APP_DIR . "/services/flipbook-script.js");
        return '<script type="text/javascript">' . "\n" . $js . "\n" . "</script>" . "\n";
    }

    /**
     * Save generated flipbook to file
     * @param string $outputPath Output file path
     * @return bool Success status
     */
    public function saveToFile($outputPath) {
        $html = $this->generate();
        return file_put_contents($outputPath, $html) !== false;
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
            "svg" => "image/svg+xml",
        ];

        if (isset($mimeTypes[$extension])) {
            return $mimeTypes[$extension];
        }

        // Fallback: try to detect with getimagesize if available
        if (function_exists("getimagesize")) {
            $info = @getimagesize($filePath);
            if ($info !== false && isset($info["mime"])) {
                return $info["mime"];
            }
        }

        // Default fallback
        return "image/jpeg";
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

    /**
     * Escape HTML special characters
     * @param string $text Text to escape
     * @return string Escaped text
     */
    private function escapeHtml($text) {
        return htmlspecialchars($text, ENT_QUOTES, "UTF-8");
    }
}
