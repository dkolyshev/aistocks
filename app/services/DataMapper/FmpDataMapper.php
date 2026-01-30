<?php
/**
 * FmpDataMapper - Maps FMP API responses to standardized CSV format
 * Implements Single Responsibility Principle - only handles data mapping
 * PHP 5.5 compatible
 */

class FmpDataMapper {
    private $standardHeaders;

    /**
     * Constructor
     */
    public function __construct() {
        // Define standard headers matching CSV format
        $this->standardHeaders = [
            "Ticker",
            "Company",
            "Sector",
            "Industry",
            "Country",
            "Market Cap",
            "P/E",
            "Price",
            "Change",
            "Volume",
            "Description",
            "Exchange",
        ];
    }

    /**
     * Get standard headers (CSV column format)
     * @return array Array of standard column names
     */
    public function getStandardHeaders() {
        return $this->standardHeaders;
    }

    /**
     * Map stock screener data (array of stocks)
     * @param array $apiData Raw API response array
     * @return array Array of mapped stock data
     */
    public function mapStockScreenerData($apiData) {
        if (!is_array($apiData)) {
            return [];
        }

        $mappedData = [];

        foreach ($apiData as $stock) {
            $mappedStock = $this->mapStockData($stock);
            if ($mappedStock !== null) {
                $mappedData[] = $mappedStock;
            }
        }

        return $mappedData;
    }

    /**
     * Map single stock data from most-actives endpoint enriched with profile data
     * Most-actives returns: symbol, name, price, change, changesPercentage, exchange
     * Profile adds: sector, industry, country, marketCap, peRatio, volume, description
     * @param array $stock Raw stock data from API (enriched with profile data)
     * @return array|null Mapped stock data or null
     */
    private function mapStockData($stock) {
        if (!is_array($stock)) {
            return null;
        }

        return [
            "Ticker" => $this->getValue($stock, "symbol", ""),
            "Company" => $this->getValue($stock, "name", ""),
            "Sector" => $this->getValue($stock, "sector", ""),
            "Industry" => $this->getValue($stock, "industry", ""),
            "Country" => $this->getValue($stock, "country", ""),
            "Market Cap" => $this->formatMarketCap($this->getValue($stock, "marketCap", "")),
            "P/E" => $this->formatRatio($this->getValue($stock, "peRatio", "")),
            "Price" => $this->formatPrice($this->getValue($stock, "price", 0)),
            "Change" => $this->formatChange($this->getValue($stock, "changesPercentage", 0)),
            "Volume" => $this->formatVolume($this->getValue($stock, "volume", "")),
            "Description" => $this->getValue($stock, "description", ""),
            "Exchange" => $this->getValue($stock, "exchange", ""),
        ];
    }

    /**
     * Get value from array with fallback
     * @param array $data Source array
     * @param string $key Key to get
     * @param mixed $default Default value
     * @return mixed Value or default
     */
    private function getValue($data, $key, $default = "") {
        if (isset($data[$key])) {
            return $data[$key];
        }

        return $default;
    }

    /**
     * Format market cap value
     * @param mixed $value Market cap value
     * @return string Formatted market cap
     */
    private function formatMarketCap($value) {
        if ($value === null || $value === "") {
            return "0";
        }

        $numValue = floatval($value);

        // If already in millions, return as is
        if ($numValue < 1000000000) {
            return number_format($numValue, 2, ".", "");
        }

        // Convert to millions if in billions
        $millions = $numValue / 1000000;

        return number_format($millions, 2, ".", "");
    }

    /**
     * Format price value
     * @param mixed $value Price value
     * @return string Formatted price
     */
    private function formatPrice($value) {
        if ($value === null || $value === "") {
            return "0.00";
        }

        return number_format(floatval($value), 2, ".", "");
    }

    /**
     * Format ratio value (P/E, etc.)
     * @param mixed $value Ratio value
     * @return string Formatted ratio
     */
    private function formatRatio($value) {
        if ($value === null || $value === "" || $value === 0) {
            return "";
        }

        return number_format(floatval($value), 2, ".", "");
    }

    /**
     * Format change percentage
     * @param mixed $value Change percentage value
     * @return string Formatted change with % sign
     */
    private function formatChange($value) {
        if ($value === null || $value === "") {
            return "0.00%";
        }

        $numValue = floatval($value);

        // If value is already a percentage (> 100 or < -100), divide by 100
        if (abs($numValue) > 100) {
            $numValue = $numValue / 100;
        }

        return number_format($numValue, 2, ".", "") . "%";
    }

    /**
     * Format volume value
     * @param mixed $value Volume value
     * @return string Formatted volume
     */
    private function formatVolume($value) {
        if ($value === null || $value === "") {
            return "0";
        }

        return number_format(intval($value), 0, "", "");
    }

}
