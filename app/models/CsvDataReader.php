<?php
/**
 * CsvDataReader - Reads and parses CSV stock data
 * PHP 5.5 compatible
 */

class CsvDataReader {
    private $csvFile;
    private $headers;
    private $data;

    /**
     * Constructor
     * @param string $csvFile Path to CSV file
     */
    public function __construct($csvFile) {
        $this->csvFile = $csvFile;
        $this->headers = [];
        $this->data = [];
    }

    /**
     * Load and parse CSV file
     * @return bool Success status
     */
    public function load() {
        if (!file_exists($this->csvFile)) {
            return false;
        }

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
     * Get stock by ticker
     * @param string $ticker Stock ticker symbol
     * @return array|null Stock data or null if not found
     */
    public function getStockByTicker($ticker) {
        foreach ($this->data as $stock) {
            if (isset($stock["Ticker"]) && strcasecmp($stock["Ticker"], $ticker) === 0) {
                return $stock;
            }
        }

        return null;
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
     * Format market cap value
     * @param string $marketCap Market cap value
     * @return string Formatted market cap
     */
    public static function formatMarketCap($marketCap) {
        $value = floatval($marketCap);

        if ($value >= 1000) {
            return '$' . number_format($value / 1000, 2) . "T";
        } elseif ($value >= 1) {
            return '$' . number_format($value, 2) . "B";
        } else {
            return '$' . number_format($value * 1000, 2) . "M";
        }
    }
}
