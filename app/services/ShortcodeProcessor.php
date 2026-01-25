<?php
/**
 * ShortcodeProcessor - Processes shortcodes in templates
 * Supports: [Current Date], [Author], [Chart], [ArticleImage], and all CSV column names
 * PHP 5.5 compatible
 */

require_once dirname(__FILE__) . "/Contracts/ShortcodeProcessorInterface.php";
require_once dirname(__FILE__) . "/Support/ImageService.php";

class ShortcodeProcessor implements ShortcodeProcessorInterface {
    private $stockData;
    private $currentDate;
    private $articleImagePath;
    private $authorName;
    private $imageService;

    /**
     * Constructor
     * @param ImageService|null $imageService Image service for MIME detection (optional)
     */
    public function __construct($imageService = null) {
        $this->currentDate = date(DATE_FORMAT);
        $this->stockData = [];
        $this->articleImagePath = "";
        $this->authorName = "";
        $this->imageService = $imageService !== null ? $imageService : new ImageService();
    }

    /**
     * Set current stock data context
     * @param array $stockData Stock data array
     */
    public function setStockData($stockData) {
        $this->stockData = $stockData;
    }

    /**
     * Set article image path
     * @param string $imagePath Path to article image
     */
    public function setArticleImagePath($imagePath) {
        $this->articleImagePath = $imagePath;
    }

    /**
     * Set author name for [Author] shortcode
     * @param string $authorName Author name
     */
    public function setAuthorName($authorName) {
        $this->authorName = $authorName;
    }

    /**
     * Process all shortcodes in content
     * @param string $content Content with shortcodes
     * @param string $format Output format ('html' or 'flipbook')
     * @return string Processed content
     */
    public function process($content, $format = "html") {
        if (empty($content)) {
            return $content;
        }

        // Replace [Current Date]
        $content = str_replace("[Current Date]", $this->currentDate, $content);

        // Replace [Author]
        $content = str_replace("[Author]", $this->escapeHtml($this->authorName), $content);

        // Replace [ArticleImage]
        if (!empty($this->articleImagePath)) {
            $imageHtml = $this->generateArticleImage($this->articleImagePath, $format);
            $content = str_replace("[ArticleImage]", $imageHtml, $content);
        }

        // Replace [Chart] with TradingView widget
        if (isset($this->stockData["Ticker"])) {
            $chartHtml = $this->generateChartWidget($this->stockData["Ticker"], $format);
            $content = str_replace("[Chart]", $chartHtml, $content);
        }

        // Replace CSV column shortcodes
        $content = $this->replaceDataShortcodes($content);

        return $content;
    }

    /**
     * Replace data shortcodes with actual values from stock data
     * @param string $content Content with shortcodes
     * @return string Processed content
     */
    private function replaceDataShortcodes($content) {
        if (empty($this->stockData)) {
            return $content;
        }

        foreach ($this->stockData as $key => $value) {
            $shortcode = "[" . $key . "]";
            $content = str_replace($shortcode, $this->escapeHtml($value), $content);
        }

        return $content;
    }

    /**
     * Generate TradingView chart widget HTML
     * @param string $ticker Stock ticker symbol
     * @param string $format Output format ('html' or 'flipbook')
     * @return string Chart widget HTML
     */
    private function generateChartWidget($ticker, $format) {
        if ($format === "flipbook") {
            return $this->generateFlipbookChart($ticker);
        }

        return $this->generateHtmlChart($ticker);
    }

    /**
     * Generate chart widget for HTML reports
     * @param string $ticker Stock ticker symbol
     * @return string Chart widget HTML
     */
    private function generateHtmlChart($ticker) {
        $width = TRADINGVIEW_WIDGET_WIDTH;
        $height = TRADINGVIEW_WIDGET_HEIGHT;

        $config = [
            "symbol" => $ticker,
            "width" => $width,
            "height" => $height,
            "dateRange" => "12m",
            "colorTheme" => "light",
            "trendLineColor" => "#37a6ef",
            "underLineColor" => "#E3F2FD",
            "isTransparent" => false,
            "autosize" => true,
            "largeChartUrl" => "",
            "utm_source" => "finstrategist.com",
            "utm_medium" => "widget",
            "utm_campaign" => "mini-symbol-overview",
            "page-uri" => "finstrategist.com/go/news/rep164556/TopStocksReport",
        ];

        $configJson = json_encode($config);
        $encodedConfig = urlencode($configJson);

        $html = '<div class="tradingview-widget-container" style="width: ' . $width . "px; height: " . $height . 'px;">' . "\n";
        $html .= '<iframe scrolling="no" allowtransparency="true" frameborder="0" ';
        $html .= 'src="' . TRADINGVIEW_WIDGET_URL . "?locale=en#" . $encodedConfig . '" ';
        $html .= 'title="mini symbol-overview TradingView widget" lang="en" ';
        $html .= 'style="user-select: none; box-sizing: border-box; display: block; height: 100%; width: 100%;"></iframe>' . "\n";
        $html .= "</div>";

        return $html;
    }

    /**
     * Generate chart widget for flipbook reports
     * @param string $ticker Stock ticker symbol
     * @return string Chart widget HTML
     */
    private function generateFlipbookChart($ticker) {
        $config = [
            "symbol" => $ticker,
            "width" => TRADINGVIEW_WIDGET_WIDTH,
            "height" => TRADINGVIEW_WIDGET_HEIGHT,
            "locale" => "en",
            "dateRange" => "12m",
            "colorTheme" => "light",
            "trendLineColor" => "#37a6ef",
            "underLineColor" => "#E3F2FD",
            "isTransparent" => false,
            "autosize" => true,
            "largeChartUrl" => "",
        ];

        $configJson = json_encode($config);

        $html = '<div class="tradingview-widget-container">' . "\n";
        $html .= '<div class="tradingview-widget-container__widget"></div>' . "\n";
        $html .= '<script type="text/javascript" src="https://s3.tradingview.com/external-embedding/embed-widget-mini-symbol-overview.js" async>' . "\n";
        $html .= $configJson . "\n";
        $html .= "</script>" . "\n";
        $html .= "</div>";

        return $html;
    }

    /**
     * Escape HTML special characters
     * @param string $value Value to escape
     * @return string Escaped value
     */
    private function escapeHtml($value) {
        return htmlspecialchars($value, ENT_QUOTES, "UTF-8");
    }

    /**
     * Get current date formatted
     * @return string Current date
     */
    public function getCurrentDate() {
        return $this->currentDate;
    }

    /**
     * Set custom date format
     * @param string $format Date format
     */
    public function setDateFormat($format) {
        $this->currentDate = date($format);
    }

    /**
     * Generate article image HTML
     * @param string $imagePath Path to article image
     * @param string $format Output format ('html' or 'flipbook')
     * @return string Article image HTML
     */
    private function generateArticleImage($imagePath, $format) {
        if (!file_exists($imagePath)) {
            return "";
        }

        // Both formats use base64 data URI (works with PDF generation and local files)
        $dataUri = $this->imageService->convertToDataUri($imagePath);
        if (empty($dataUri)) {
            return "";
        }

        return '<img alt="" src="' . $dataUri . '" style="float: left; width: 200px; height: 200px; margin: 14px;">';
    }
}
