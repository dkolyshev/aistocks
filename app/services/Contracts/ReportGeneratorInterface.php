<?php
/**
 * ReportGeneratorInterface - Contract for report generators
 * PHP 5.5 compatible
 */

interface ReportGeneratorInterface {
    /**
     * Generate report content
     * @return string Generated content
     */
    public function generate();

    /**
     * Save generated report to file
     * @param string $outputPath Output file path
     * @return bool Success status
     */
    public function saveToFile($outputPath);
}
