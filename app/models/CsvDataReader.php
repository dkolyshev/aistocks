<?php
/**
 * CsvDataReader - Reads and parses CSV stock data
 * PHP 5.5 compatible
 */

class CsvDataReader implements CsvDataReaderInterface, DataSourceInterface {
    private $csvFile;
    private $headers;
    private $data;
    private $keyField;
    private $fileSystem;

    /**
     * Constructor
     * @param string $csvFile Path to CSV file
     * @param string $keyField Key field name for lookups (default: "Ticker")
     * @param FileSystemInterface|null $fileSystem File system abstraction (optional)
     */
    public function __construct($csvFile, $keyField = "Ticker", $fileSystem = null) {
        $this->csvFile = $csvFile;
        $this->headers = [];
        $this->data = [];
        $this->keyField = $keyField;
        $this->fileSystem = $fileSystem !== null ? $fileSystem : new FileSystem();
    }

    /**
     * Load and parse CSV file
     * @return bool Success status
     */
    public function load() {
        if (!$this->fileSystem->exists($this->csvFile)) {
            return false;
        }

        // Use native fopen/fgetcsv for CSV parsing (specialized operation)
        $handle = fopen($this->csvFile, "r");
        if ($handle === false) {
            return false;
        }

        $lineNumber = 0;
        while (($row = fgetcsv($handle, 0, ",")) !== false) {
            if ($lineNumber === 0) {
                $this->headers = array_map("trim", $row);
            } else {
                if (!empty($row) && trim(implode("", $row)) !== "") {
                    $this->data[] = $this->mapRowToHeaders($row);
                }
            }
            $lineNumber++;
        }

        fclose($handle);

        return true;
    }

    /**
     * Map CSV row to associative array using headers
     * @param array $row CSV row data
     * @return array Associative array
     */
    private function mapRowToHeaders($row) {
        $mapped = [];

        foreach ($this->headers as $index => $header) {
            $mapped[$header] = isset($row[$index]) ? trim($row[$index]) : "";
        }

        return $mapped;
    }

    /**
     * Get all stock data
     * @return array Array of stock data
     */
    public function getAllData() {
        return $this->data;
    }

    /**
     * Get limited number of stocks
     * @param int $limit Number of stocks to return
     * @return array Limited array of stock data
     */
    public function getLimitedData($limit) {
        if ($limit <= 0) {
            return [];
        }

        return array_slice($this->data, 0, $limit);
    }

    /**
     * Get row by key field value (OCP: configurable key field)
     * @param string $value Value to search for
     * @return array|null Data row or null if not found
     */
    public function getByKeyField($value) {
        foreach ($this->data as $row) {
            if (isset($row[$this->keyField]) && strcasecmp($row[$this->keyField], $value) === 0) {
                return $row;
            }
        }

        return null;
    }

    /**
     * Get stock by ticker (convenience method, uses key field)
     * @param string $ticker Stock ticker symbol
     * @return array|null Stock data or null if not found
     * @deprecated Use getByKeyField() instead
     */
    public function getStockByTicker($ticker) {
        return $this->getByKeyField($ticker);
    }

    /**
     * Get available headers/columns
     * @return array Array of column names
     */
    public function getHeaders() {
        return $this->headers;
    }

    /**
     * Get total number of stocks
     * @return int Total count
     */
    public function getTotalCount() {
        return count($this->data);
    }

    /**
     * Check if CSV has been loaded
     * @return bool True if data exists
     */
    public function isLoaded() {
        return !empty($this->data);
    }

    /**
     * Get the configured key field name
     * @return string Key field name
     */
    public function getKeyField() {
        return $this->keyField;
    }

    /**
     * Set the key field name
     * @param string $keyField Key field name
     */
    public function setKeyField($keyField) {
        $this->keyField = $keyField;
    }
}
