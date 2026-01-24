<?php
/**
 * FlipbookGenerator - Generates interactive flipbook HTML reports
 * PHP 5.5 compatible
 */

require_once __DIR__ . "/BaseReportGenerator.php";

class FlipbookGenerator extends BaseReportGenerator {
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
        foreach ($this->stocks as $stock) {
            $this->shortcodeProcessor->setStockData($stock);
            $stockPages[] = $this->generateStockPage();
        }

        // Generate controls
        $controlsHtml = View::render("reports/flipbook/controls", []);

        // Load CSS and JS
        $styles = $this->loadStyles();
        $script = $this->loadScript();

        // Render the complete layout
        return View::render("reports/flipbook/layout", [
            "title" => $this->getSetting("file_name"),
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
        return $this->fileLoaderService->loadStyles("assets/css/flipbook-report.css");
    }

    /**
     * Load JavaScript from file
     * @return string JS content
     */
    private function loadScript() {
        $jsFile = defined("APP_DIR") ? APP_DIR . "/services/flipbook-script.js" : __DIR__ . "/flipbook-script.js";
        return $this->fileLoaderService->loadScript($jsFile);
    }

    /**
     * Generate cover page
     * @return string Cover page HTML
     */
    private function generateCoverPage() {
        $hasCoverImage = $this->hasCoverImage();
        $coverImageDataUri = $hasCoverImage ? $this->getCoverImageDataUri() : "";

        return View::render("reports/flipbook/cover", [
            "hasCoverImage" => $hasCoverImage,
            "coverImageDataUri" => $coverImageDataUri,
            "title" => $this->getSetting("report_title"),
        ]);
    }

    /**
     * Generate disclaimer page
     * @return string Disclaimer page HTML
     */
    private function generateDisclaimerPage() {
        $disclaimer = $this->loadDisclaimer();

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
        $intro = $this->loadIntro();

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
    private function generateStockPage() {
        $stockBlockHtml = $this->loadStockBlockTemplate();
        $stockContent = $this->shortcodeProcessor->process($stockBlockHtml, "flipbook");

        return View::render("reports/flipbook/stock-page", [
            "stockContent" => $stockContent,
        ]);
    }
}
