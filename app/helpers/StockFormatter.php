<?php
/**
 * StockFormatter - Formats stock-related values for display
 * PHP 5.5 compatible
 */

class StockFormatter {
    /**
     * Format market cap value for display
     * @param string|float $marketCap Market cap value (in billions)
     * @return string Formatted market cap with currency symbol
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

    /**
     * Format percentage value
     * @param string|float $value Percentage value
     * @param int $decimals Number of decimal places
     * @return string Formatted percentage with % symbol
     */
    public static function formatPercentage($value, $decimals = 2) {
        $numValue = floatval($value);
        return number_format($numValue, $decimals) . "%";
    }

    /**
     * Format currency value
     * @param string|float $value Currency value
     * @param int $decimals Number of decimal places
     * @param string $symbol Currency symbol
     * @return string Formatted currency
     */
    public static function formatCurrency($value, $decimals = 2, $symbol = "$") {
        $numValue = floatval($value);
        return $symbol . number_format($numValue, $decimals);
    }

    /**
     * Format large number with abbreviation
     * @param string|float $value Number value
     * @param int $decimals Number of decimal places
     * @return string Formatted number with K/M/B/T suffix
     */
    public static function formatLargeNumber($value, $decimals = 2) {
        $numValue = floatval($value);

        if ($numValue >= 1000000000000) {
            return number_format($numValue / 1000000000000, $decimals) . "T";
        } elseif ($numValue >= 1000000000) {
            return number_format($numValue / 1000000000, $decimals) . "B";
        } elseif ($numValue >= 1000000) {
            return number_format($numValue / 1000000, $decimals) . "M";
        } elseif ($numValue >= 1000) {
            return number_format($numValue / 1000, $decimals) . "K";
        } else {
            return number_format($numValue, $decimals);
        }
    }
}
