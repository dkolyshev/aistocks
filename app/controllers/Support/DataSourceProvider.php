<?php
/**
 * DataSourceProvider - Provides available CSV data sources
 * Single Responsibility: Only handles data source discovery and listing
 * PHP 5.5 compatible
 */

class DataSourceProvider {
    private $dataDirectory;

    /**
     * Constructor
     * @param string $dataDirectory Path to data directory
     */
    public function __construct($dataDirectory) {
        $this->dataDirectory = $dataDirectory;
    }

    /**
     * Get available CSV files from data directory
     * @return array Array of CSV file names
     */
    public function getAvailableDataSources() {
        $csvFiles = [];

        if (!is_dir($this->dataDirectory)) {
            return $csvFiles;
        }

        $files = scandir($this->dataDirectory);
        if ($files === false) {
            return $csvFiles;
        }

        foreach ($files as $file) {
            if ($this->isCsvFile($file)) {
                $csvFiles[] = $file;
            }
        }

        sort($csvFiles);
        return $csvFiles;
    }

    /**
     * Check if a file is a CSV file
     * @param string $filename File name to check
     * @return bool True if file is a CSV
     */
    private function isCsvFile($filename) {
        if ($filename === '.' || $filename === '..') {
            return false;
        }

        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        return $extension === 'csv';
    }

    /**
     * Check if a data source exists
     * @param string $filename CSV file name to check
     * @return bool True if exists
     */
    public function dataSourceExists($filename) {
        $sources = $this->getAvailableDataSources();
        return in_array($filename, $sources);
    }

    /**
     * Get full path to a data source file
     * @param string $filename CSV file name
     * @return string Full path to file
     */
    public function getDataSourcePath($filename) {
        return $this->dataDirectory . '/' . $filename;
    }
}
