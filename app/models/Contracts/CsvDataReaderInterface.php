<?php
/**
 * CsvDataReaderInterface - Contract for CSV data reading
 * PHP 5.5 compatible
 */

interface CsvDataReaderInterface {
    /**
     * Load and parse CSV file
     * @return bool Success status
     */
    public function load();

    /**
     * Get all data
     * @return array Array of data rows
     */
    public function getAllData();

    /**
     * Get limited number of rows
     * @param int $limit Number of rows to return
     * @return array Limited array of data
     */
    public function getLimitedData($limit);

    /**
     * Get row by key field value
     * @param string $value Value to search for
     * @return array|null Data row or null if not found
     */
    public function getByKeyField($value);

    /**
     * Get available headers/columns
     * @return array Array of column names
     */
    public function getHeaders();

    /**
     * Get total number of rows
     * @return int Total count
     */
    public function getTotalCount();

    /**
     * Check if data has been loaded
     * @return bool True if data exists
     */
    public function isLoaded();
}
