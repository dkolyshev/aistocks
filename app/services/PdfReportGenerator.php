<?php
/**
 * PdfReportGenerator - Generates PDF format reports
 * PHP 5.5 compatible
 *
 * Note: This implementation uses wkhtmltopdf command line tool
 * Alternative: Integrate TCPDF or mPDF library for pure PHP solution
 */

class PdfReportGenerator {
    private $settings;
    private $htmlGenerator;

    /**
     * Constructor
     * @param array $settings Report settings
     * @param HtmlReportGenerator $htmlGenerator HTML generator instance
     */
    public function __construct($settings, $htmlGenerator) {
        $this->settings = $settings;
        $this->htmlGenerator = $htmlGenerator;
    }

    /**
     * Generate PDF report
     * @param string $outputPath Output PDF file path
     * @return bool Success status
     */
    public function generate($outputPath) {
        // Check if custom PDF was uploaded
        if (!empty($this->settings["manual_pdf_path"]) && file_exists($this->settings["manual_pdf_path"])) {
            return copy($this->settings["manual_pdf_path"], $outputPath);
        }

        // Try to generate PDF from HTML using wkhtmltopdf
        if ($this->isWkhtmltopdfAvailable()) {
            return $this->generateWithWkhtmltopdf($outputPath);
        }

        // Fallback: Create a placeholder PDF notice file
        return $this->createPlaceholderNotice($outputPath);
    }

    /**
     * Check if wkhtmltopdf is available
     * @return bool True if available
     */
    private function isWkhtmltopdfAvailable() {
        $output = [];
        $returnVar = 0;
        exec("which wkhtmltopdf 2>&1", $output, $returnVar);

        return $returnVar === 0;
    }

    /**
     * Generate PDF using wkhtmltopdf
     * @param string $outputPath Output PDF file path
     * @return bool Success status
     */
    private function generateWithWkhtmltopdf($outputPath) {
        // Create temporary HTML file
        $tempHtml = tempnam(sys_get_temp_dir(), "report_") . ".html";
        $this->htmlGenerator->setPdfMode(true);
        $htmlContent = $this->htmlGenerator->generate();

        if (file_put_contents($tempHtml, $htmlContent) === false) {
            return false;
        }

        // Generate PDF using wkhtmltopdf with xvfb (virtual display)
        $command = sprintf(
            'xvfb-run -a --server-args="-screen 0 1024x768x24" wkhtmltopdf --quiet --enable-javascript --javascript-delay 2000 --no-stop-slow-scripts --page-size Letter %s %s 2>&1',
            escapeshellarg($tempHtml),
            escapeshellarg($outputPath)
        );

        $output = [];
        $returnVar = 0;
        exec($command, $output, $returnVar);

        // Clean up temp file
        if (file_exists($tempHtml)) {
            unlink($tempHtml);
        }

        return $returnVar === 0 && file_exists($outputPath);
    }

    /**
     * Create placeholder notice when PDF generation is not available
     * @param string $outputPath Output file path
     * @return bool Success status
     */
    private function createPlaceholderNotice($outputPath) {
        $notice = "PDF Generation Not Available\n\n";
        $notice .= "To enable PDF generation, install wkhtmltopdf:\n";
        $notice .= "- Ubuntu/Debian: sudo apt-get install wkhtmltopdf\n";
        $notice .= "- CentOS/RHEL: sudo yum install wkhtmltopdf\n";
        $notice .= "- macOS: brew install wkhtmltopdf\n\n";
        $notice .= "Alternative: Upload manual PDF via Report Manager\n\n";
        $notice .= "HTML report available: " . $this->settings["file_name"] . ".html\n";

        // Save as text file with .pdf.txt extension to indicate it's a placeholder
        $placeholderPath = $outputPath . ".txt";
        $result = file_put_contents($placeholderPath, $notice) !== false;

        // Also log the notice
        error_log("PDF generation not available for: " . $this->settings["file_name"]);

        return $result;
    }

    /**
     * Check if manual PDF was uploaded
     * @return bool True if manual PDF exists
     */
    public function hasManualPdf() {
        return !empty($this->settings["manual_pdf_path"]) && file_exists($this->settings["manual_pdf_path"]);
    }

    /**
     * Get PDF generation method
     * @return string Generation method name
     */
    public function getGenerationMethod() {
        if ($this->hasManualPdf()) {
            return "manual_upload";
        } elseif ($this->isWkhtmltopdfAvailable()) {
            return "wkhtmltopdf";
        } else {
            return "unavailable";
        }
    }
}
