<?php
/**
 * HtmlReportGenerator - Generates HTML format reports
 * PHP 5.5 compatible
 */

require_once __DIR__ . "/BaseReportGenerator.php";

class HtmlReportGenerator extends BaseReportGenerator {
    /** @var bool Whether generating for PDF output */
    private $isPdfMode = false;

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
        $disclaimer = $this->loadDisclaimer();
        $disclaimerHtml = !empty($disclaimer)
            ? $this->shortcodeProcessor->process($disclaimer, "html")
            : "";

        // Prepare article image HTML
        $articleImageHtml = $this->shortcodeProcessor->process("[ArticleImage]", "html");

        // Prepare intro HTML
        $reportIntro = $this->loadIntro();
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
            "title" => $this->getSetting("report_title"),
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
        return $this->fileLoaderService->loadStyles("assets/css/html-report.css");
    }

    /**
     * Generate PDF cover page HTML
     * @return string Cover page HTML or empty string
     */
    private function generateCoverPage() {
        if (!$this->hasCoverImage()) {
            return "";
        }

        $dataUri = $this->getCoverImageDataUri();

        return View::render("reports/html/cover", [
            "coverImageDataUri" => $dataUri,
        ]);
    }

    /**
     * Generate individual stock block
     * @param array $stock Stock data
     * @return string Stock block HTML
     */
    private function generateStockBlock($stock) {
        $stockBlockHtml = $this->loadStockBlockTemplate();

        if ($this->isPdfMode) {
            $stockBlockHtml = str_replace("[Chart]", "", $stockBlockHtml);
        }

        $stockContent = $this->shortcodeProcessor->process($stockBlockHtml, "html");

        return View::render("reports/html/stock-block", [
            "stockContent" => $stockContent,
        ]);
    }
}
