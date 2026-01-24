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
        return $this->generateFlipbookStructure();
    }

    /**
     * Generate complete flipbook HTML structure using view templates
     * @return string HTML content
     */
    private function generateFlipbookStructure() {
        // Generate cover page
        $coverHtml = $this->generateCoverPage();

        // Generate disclaimer page
        $disclaimerHtml = $this->generateDisclaimerPage();

        // Generate intro page
        $introHtml = $this->generateIntroPage();

        // Generate stock pages
        $stockPages = [];
        foreach ($this->stocks as $index => $stock) {
            $this->shortcodeProcessor->setStockData($stock);
            $stockPages[] = $this->generateStockPage($stock, $index);
        }

        // Generate controls
        $controlsHtml = View::render("reports/flipbook/controls", []);

        // Load CSS and JS
        $styles = $this->loadStyles();
        $script = $this->loadScript();

        // Render the complete layout
        return View::render("reports/flipbook/layout", [
            "title" => $this->settings["file_name"],
            "styles" => $styles,
            "coverHtml" => $coverHtml,
            "disclaimerHtml" => $disclaimerHtml,
            "introHtml" => $introHtml,
            "stockPages" => $stockPages,
            "controlsHtml" => $controlsHtml,
            "script" => $script,
        ]);
    }

    /**
     * Load CSS styles from file
     * @return string CSS content
     */
    private function loadStyles() {
        $cssFile = PUBLIC_DIR . "/assets/css/flipbook-report.css";
        if (file_exists($cssFile)) {
            return file_get_contents($cssFile);
        }
        return "";
    }

    /**
     * Load JavaScript from file
     * @return string JS content
     */
    private function loadScript() {
        $jsFile = APP_DIR . "/services/flipbook-script.js";
        if (file_exists($jsFile)) {
            return file_get_contents($jsFile);
        }
        return "";
    }

    /**
     * Generate cover page
     * @return string Cover page HTML
     */
    private function generateCoverPage() {
        $coverImagePath = !empty($this->settings["pdf_cover_image"])
            ? $this->settings["pdf_cover_image"]
            : "";

        $hasCoverImage = !empty($coverImagePath) && file_exists($coverImagePath);
        $coverImageDataUri = $hasCoverImage ? $this->convertImageToDataUri($coverImagePath) : "";

        return View::render("reports/flipbook/cover", [
            "hasCoverImage" => $hasCoverImage,
            "coverImageDataUri" => $coverImageDataUri,
            "title" => $this->settings["report_title"],
        ]);
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

        $disclaimerContent = $this->shortcodeProcessor->process($disclaimer, "flipbook");

        return View::render("reports/flipbook/disclaimer", [
            "disclaimerContent" => $disclaimerContent,
        ]);
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

        $introContent = $this->shortcodeProcessor->process($intro, "flipbook");

        return View::render("reports/flipbook/intro", [
            "introContent" => $introContent,
        ]);
    }

    /**
     * Generate stock page
     * @param array $stock Stock data
     * @param int $index Stock index
     * @return string Stock page HTML
     */
    private function generateStockPage($stock, $index) {
        // Use custom stock block if provided, otherwise use default template
        if (!empty($this->settings["stock_block_html"])) {
            $stockContent = $this->shortcodeProcessor->process($this->settings["stock_block_html"], "flipbook");
        } else {
            $stockContent = $this->generateDefaultStockContent($stock, $index);
        }

        return View::render("reports/flipbook/stock-page", [
            "stockContent" => $stockContent,
        ]);
    }

    /**
     * Generate default stock content when no custom template provided
     * @param array $stock Stock data
     * @param int $index Stock index
     * @return string Default stock HTML
     */
    private function generateDefaultStockContent($stock, $index) {
        $company = isset($stock["Company"]) ? $stock["Company"] : "";
        $exchange = isset($stock["Exchange"]) ? $stock["Exchange"] : "NASDAQ";
        $ticker = isset($stock["Ticker"]) ? $stock["Ticker"] : "";
        $price = isset($stock["Price"]) ? $stock["Price"] : null;
        $description = isset($stock["Description"]) ? $stock["Description"] : "";

        $marketCap = "";
        if (isset($stock["Market Cap"])) {
            $marketCap = CsvDataReader::formatMarketCap($stock["Market Cap"]);
        }

        $chartHtml = "";
        if (!empty($ticker)) {
            $chartHtml = $this->shortcodeProcessor->process("[Chart]", "flipbook");
        }

        return View::render("reports/flipbook/stock-default", [
            "stockNumber" => $index + 1,
            "company" => $company,
            "exchange" => $exchange,
            "ticker" => $ticker,
            "price" => $price,
            "marketCap" => $marketCap,
            "chartHtml" => $chartHtml,
            "description" => $description,
        ]);
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
}
