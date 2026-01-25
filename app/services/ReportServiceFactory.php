<?php
/**
 * ReportServiceFactory - Creates report service instances
 * Implements Factory pattern for dependency inversion
 * PHP 5.5 compatible
 */

require_once __DIR__ . "/Contracts/ReportServiceFactoryInterface.php";

class ReportServiceFactory implements ReportServiceFactoryInterface {
    /**
     * Create CSV data reader instance
     * @param string $csvFilePath Path to CSV file
     * @return CsvDataReaderInterface CSV data reader instance
     */
    public function createCsvDataReader($csvFilePath) {
        return new CsvDataReader($csvFilePath);
    }

    /**
     * Create shortcode processor instance
     * @return ShortcodeProcessorInterface Shortcode processor instance
     */
    public function createShortcodeProcessor() {
        return new ShortcodeProcessor();
    }

    /**
     * Create HTML report generator instance
     * @param array $settings Report settings
     * @param array $stocks Stock data array
     * @param ShortcodeProcessorInterface $shortcodeProcessor Shortcode processor
     * @return ReportGeneratorInterface HTML report generator instance
     */
    public function createHtmlReportGenerator($settings, $stocks, $shortcodeProcessor) {
        return new HtmlReportGenerator($settings, $stocks, $shortcodeProcessor);
    }

    /**
     * Create PDF report generator instance
     * @param array $settings Report settings
     * @param ReportGeneratorInterface $htmlGenerator HTML generator instance
     * @return ReportGeneratorInterface PDF report generator instance
     */
    public function createPdfReportGenerator($settings, $htmlGenerator) {
        return new PdfReportGenerator($settings, $htmlGenerator);
    }

    /**
     * Create flipbook generator instance
     * @param array $settings Report settings
     * @param array $stocks Stock data array
     * @param ShortcodeProcessorInterface $shortcodeProcessor Shortcode processor
     * @return ReportGeneratorInterface Flipbook generator instance
     */
    public function createFlipbookGenerator($settings, $stocks, $shortcodeProcessor) {
        return new FlipbookGenerator($settings, $stocks, $shortcodeProcessor);
    }
}
