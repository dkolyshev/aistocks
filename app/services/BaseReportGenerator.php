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

        // Set author name if available
        if (!empty($settings["author_name"])) {
            $this->shortcodeProcessor->setAuthorName($settings["author_name"]);
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
     * Load field content based on state (empty/custom/default)
     * @param string $stateKey Settings key for the field state
     * @param string $contentKey Settings key for custom content
     * @param string $defaultFile Path to default template file
     * @return string Content based on state (empty string if state is "empty")
     */
    protected function loadFieldContent($stateKey, $contentKey, $defaultFile) {
        $state = $this->getSetting($stateKey, "default");

        if ($state === "empty") {
            return "";
        }

        if ($state === "custom") {
            return $this->getSetting($contentKey, "");
        }

        return $this->fileLoaderService->loadDataFile($defaultFile);
    }

    /**
     * Load disclaimer content based on field state
     * @return string Disclaimer HTML content (empty if state is "empty")
     */
    protected function loadDisclaimer() {
        return $this->loadFieldContent(
            "disclaimer_html_state",
            "disclaimer_html",
            DEFAULT_REPORT_DISCLAIMER_HTML
        );
    }

    /**
     * Load intro content based on field state
     * @return string Intro HTML content (empty if state is "empty")
     */
    protected function loadIntro() {
        return $this->loadFieldContent(
            "report_intro_html_state",
            "report_intro_html",
            DEFAULT_REPORT_INTRO_HTML
        );
    }

    /**
     * Load stock block template based on field state
     * @return string Stock block HTML template (empty if state is "empty")
     */
    protected function loadStockBlockTemplate() {
        return $this->loadFieldContent(
            "stock_block_html_state",
            "stock_block_html",
            DEFAULT_REPORT_STOCK_BLOCK_HTML
        );
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
