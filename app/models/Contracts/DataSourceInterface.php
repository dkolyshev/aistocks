<?php
/**
 * DataSourceInterface - Contract for data sources (CSV, API, etc.)
 * Allows CSV and API sources to be interchangeable
 * PHP 5.5 compatible
 */

interface DataSourceInterface {
    /**
     * Load data from source
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
}
