<?php
/**
 * BaseReportGenerator - Abstract base class for report generators
 * PHP 5.5 compatible
 */

require_once __DIR__ . "/Contracts/ReportGeneratorInterface.php";
require_once __DIR__ . "/Support/ImageService.php";
require_once __DIR__ . "/Support/FileLoaderService.php";

abstract class BaseReportGenerator implements ReportGeneratorInterface {
    /** @var array Report settings */
    protected $settings;

    /** @var array Stock data array */
    protected $stocks;

    /** @var ShortcodeProcessor Shortcode processor instance */
    protected $shortcodeProcessor;

    /** @var ImageService Image processing service */
    protected $imageService;

    /** @var FileLoaderService File loading service */
    protected $fileLoaderService;

    /**
     * Constructor
     * @param array $settings Report settings
     * @param array $stocks Stock data array
     * @param ShortcodeProcessor $shortcodeProcessor Shortcode processor instance
     * @param ImageService|null $imageService Image service (optional, creates default if null)
     * @param FileLoaderService|null $fileLoaderService File loader service (optional, creates default if null)
     */
    public function __construct(
        $settings,
        $stocks,
        $shortcodeProcessor,
        $imageService = null,
        $fileLoaderService = null
    ) {
        $this->settings = $settings;
        $this->stocks = $stocks;
        $this->shortcodeProcessor = $shortcodeProcessor;
        $this->imageService = $imageService !== null ? $imageService : new ImageService();
        $this->fileLoaderService = $fileLoaderService !== null ? $fileLoaderService : new FileLoaderService();

        // Set article image path if available
        if (!empty($settings["article_image"])) {
            $this->shortcodeProcessor->setArticleImagePath($settings["article_image"]);
        }
    }

    /**
     * Generate report content (must be implemented by subclasses)
     * @return string Generated content
     */
    abstract public function generate();

    /**
     * Save generated report to file
     * @param string $outputPath Output file path
     * @return bool Success status
     */
    public function saveToFile($outputPath) {
        $content = $this->generate();
        return file_put_contents($outputPath, $content) !== false;
    }

    /**
     * Get setting value with optional default
     * @param string $key Setting key
     * @param mixed $default Default value if not set
     * @return mixed Setting value or default
     */
    protected function getSetting($key, $default = "") {
        return isset($this->settings[$key]) ? $this->settings[$key] : $default;
    }

    /**
     * Load disclaimer content based on field state
     * @return string Disclaimer HTML content (empty if state is "empty")
     */
    protected function loadDisclaimer() {
        $state = $this->getSetting("disclaimer_html_state", "default");

        // If state is empty, exclude from report
        if ($state === "empty") {
            return "";
        }

        // If state is custom, use the saved content
        if ($state === "custom") {
            return $this->getSetting("disclaimer_html", "");
        }

        // Default state: load from default template file
        return $this->fileLoaderService->loadDataFile(DEFAULT_REPORT_DISCLAIMER_HTML);
    }

    /**
     * Load intro content based on field state
     * @return string Intro HTML content (empty if state is "empty")
     */
    protected function loadIntro() {
        $state = $this->getSetting("report_intro_html_state", "default");

        // If state is empty, exclude from report
        if ($state === "empty") {
            return "";
        }

        // If state is custom, use the saved content
        if ($state === "custom") {
            return $this->getSetting("report_intro_html", "");
        }

        // Default state: load from default template file
        return $this->fileLoaderService->loadDataFile(DEFAULT_REPORT_INTRO_HTML);
    }

    /**
     * Load stock block template based on field state
     * @return string Stock block HTML template (empty if state is "empty")
     */
    protected function loadStockBlockTemplate() {
        $state = $this->getSetting("stock_block_html_state", "default");

        // If state is empty, exclude from report
        if ($state === "empty") {
            return "";
        }

        // If state is custom, use the saved content
        if ($state === "custom") {
            return $this->getSetting("stock_block_html", "");
        }

        // Default state: load from default template file
        return $this->fileLoaderService->loadDataFile(DEFAULT_REPORT_STOCK_BLOCK_HTML);
    }

    /**
     * Generate cover image data URI if cover image exists
     * @return string Data URI or empty string
     */
    protected function getCoverImageDataUri() {
        $coverImagePath = $this->getSetting("pdf_cover_image");
        if (!empty($coverImagePath) && file_exists($coverImagePath)) {
            return $this->imageService->convertToDataUri($coverImagePath);
        }
        return "";
    }

    /**
     * Check if cover image exists
     * @return bool True if cover image exists
     */
    protected function hasCoverImage() {
        $coverImagePath = $this->getSetting("pdf_cover_image");
        return !empty($coverImagePath) && file_exists($coverImagePath);
    }
}
