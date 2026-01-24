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
        return $this->generateHtmlStructure();
    }

    /**
     * Generate complete HTML structure using view templates
     * @return string HTML content
     */
    private function generateHtmlStructure() {
        // Prepare cover page HTML
        $coverHtml = "";
        if ($this->isPdfMode) {
            $coverHtml = $this->generateCoverPage();
        }

        // Prepare disclaimer HTML
        $disclaimer = !empty($this->settings["disclaimer_html"])
            ? $this->settings["disclaimer_html"]
            : $this->loadDataFile(DEFAULT_REPORT_DISCLAIMER_HTML);
        $disclaimerHtml = !empty($disclaimer)
            ? $this->shortcodeProcessor->process($disclaimer, "html")
            : "";

        // Prepare article image HTML
        $articleImageHtml = $this->shortcodeProcessor->process("[ArticleImage]", "html");

        // Prepare intro HTML
        $reportIntro = !empty($this->settings["report_intro_html"])
            ? $this->settings["report_intro_html"]
            : $this->loadDataFile(DEFAULT_REPORT_INTRO_HTML);
        $introHtml = !empty($reportIntro)
            ? $this->shortcodeProcessor->process($reportIntro, "html")
            : "";

        // Generate stock blocks
        $stockBlocks = [];
        foreach ($this->stocks as $stock) {
            $this->shortcodeProcessor->setStockData($stock);
            $stockBlocks[] = $this->generateStockBlock($stock);
        }

        // Load CSS from file
        $styles = $this->loadStyles();

        // Render the complete layout
        return View::render("reports/html/layout", [
            "title" => $this->settings["report_title"],
            "styles" => $styles,
            "isPdfMode" => $this->isPdfMode,
            "coverHtml" => $coverHtml,
            "disclaimerHtml" => $disclaimerHtml,
            "articleImageHtml" => $articleImageHtml,
            "introHtml" => $introHtml,
            "stockBlocks" => $stockBlocks,
        ]);
    }

    /**
     * Load CSS styles from file
     * @return string CSS content
     */
    private function loadStyles() {
        $cssFile = PUBLIC_DIR . "/assets/css/html-report.css";
        if (file_exists($cssFile)) {
            return file_get_contents($cssFile);
        }
        return "";
    }

    /**
     * Generate PDF cover page HTML
     * @return string Cover page HTML or empty string
     */
    private function generateCoverPage() {
        $coverImagePath = !empty($this->settings["pdf_cover_image"])
            ? $this->settings["pdf_cover_image"]
            : "";

        if (empty($coverImagePath) || !file_exists($coverImagePath)) {
            return "";
        }

        $dataUri = $this->convertImageToDataUri($coverImagePath);

        return View::render("reports/html/cover", [
            "coverImageDataUri" => $dataUri,
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
        $stockBlockHtml = !empty($this->settings["stock_block_html"])
            ? $this->settings["stock_block_html"]
            : $this->loadDataFile(DEFAULT_REPORT_STOCK_BLOCK_HTML);

        if ($this->isPdfMode) {
            $stockBlockHtml = str_replace("[Chart]", "", $stockBlockHtml);
        }

        $stockContent = $this->shortcodeProcessor->process($stockBlockHtml, "html");

        return View::render("reports/html/stock-block", [
            "stockContent" => $stockContent,
        ]);
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
