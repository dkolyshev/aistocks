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
     * Get available API data sources
     * @return array Array of API source names
     */
    public function getAvailableApiSources() {
        return [
            'fmp-most-actives'
        ];
    }

    /**
     * Get all available data sources with type indicators
     * @return array Array of data sources prefixed with type (csv: or api:)
     */
    public function getAllDataSourcesWithType() {
        $sources = [];

        // Add CSV sources
        $csvFiles = $this->getAvailableDataSources();
        foreach ($csvFiles as $csvFile) {
            $sources[] = 'csv:' . $csvFile;
        }

        // Add API sources
        $apiSources = $this->getAvailableApiSources();
        foreach ($apiSources as $apiSource) {
            $sources[] = 'api:' . $apiSource;
        }

        return $sources;
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
