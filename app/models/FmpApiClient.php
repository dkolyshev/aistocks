<?php
/**
 * FmpApiClient - Financial Modeling Prep API Client
 * Implements DataSourceInterface for interchangeable data sources
 * PHP 5.5 compatible - uses cURL for HTTP requests
 */

class FmpApiClient implements DataSourceInterface {
    private $apiKey;
    private $baseUrl;
    private $timeout;
    private $cacheTtl;
    private $data;
    private $headers;
    private $keyField;
    private $dataMapper;
    private $requestCount;
    private $requestTimestamps;
    private $cacheService;
    private $endpoint;
    private $filters;

    /**
     * Constructor
     * @param string $apiKey FMP API key
     * @param string $keyField Key field name for lookups (default: "Ticker")
     * @param FmpDataMapper|null $dataMapper Data mapper instance
     * @param FileCacheService|null $cacheService Cache service instance
     * @param string $endpoint API endpoint (default: "most-actives")
     * @param array $filters Client-side filters to apply to data
     */
    public function __construct($apiKey = null, $keyField = "Ticker", $dataMapper = null, $cacheService = null, $endpoint = "most-actives", $filters = array()) {
        $this->apiKey = $apiKey !== null ? $apiKey : FMP_API_KEY;
        $this->baseUrl = FMP_BASE_URL;
        $this->timeout = FMP_API_TIMEOUT;
        $this->cacheTtl = FMP_CACHE_TTL;
        $this->keyField = $keyField;
        $this->data = array();
        $this->headers = array();
        $this->dataMapper = $dataMapper;
        $this->requestCount = 0;
        $this->requestTimestamps = array();
        $this->endpoint = $endpoint;
        $this->filters = is_array($filters) ? $filters : array();

        // Use provided cache service or create default instance
        if ($cacheService !== null) {
            $this->cacheService = $cacheService;
        } else {
            $cacheDir = defined('FMP_CACHE_DIR') ? FMP_CACHE_DIR : __DIR__ . '/../../data/cache';
            $this->cacheService = new FileCacheService($cacheDir);
        }
    }

    /**
     * Load data from FMP API using configured endpoint
     * Fetches most-actives list, then enriches with profile data
     * @return bool Success status
     */
    public function load() {
        try {
            // Use configured endpoint to get list of stocks
            $response = $this->makeRequest("/" . $this->endpoint);

            if ($response === false || !isset($response) || empty($response)) {
                error_log("FmpApiClient: Failed to load data from API endpoint: " . $this->endpoint);
                return false;
            }

            // Decode JSON response
            $apiData = json_decode($response, true);

            if ($apiData === null || !is_array($apiData)) {
                error_log("FmpApiClient: Invalid JSON response");
                return false;
            }

            // Extract symbols from most-actives response
            $symbols = $this->extractSymbols($apiData);

            // Fetch profile data for all symbols
            $profileData = $this->fetchProfileData($symbols);

            // Merge most-actives data with profile data
            $enrichedData = $this->mergeWithProfileData($apiData, $profileData);

            // Map API response to standardized format
            if ($this->dataMapper !== null) {
                $this->data = $this->dataMapper->mapStockScreenerData($enrichedData);
                $this->headers = $this->dataMapper->getStandardHeaders();
            } else {
                // Fallback: use raw API data
                $this->data = $enrichedData;
                if (!empty($enrichedData)) {
                    $this->headers = array_keys($enrichedData[0]);
                }
            }

            // Apply client-side filters
            if (!empty($this->filters)) {
                $this->data = $this->applyFilters($this->data);
            }

            return true;

        } catch (Exception $e) {
            error_log("FmpApiClient: Exception during load - " . $e->getMessage());
            return false;
        }
    }

    /**
     * Extract symbols from API response
     * @param array $apiData Raw API response
     * @return array Array of symbol strings
     */
    private function extractSymbols($apiData) {
        $symbols = array();

        foreach ($apiData as $stock) {
            if (isset($stock['symbol']) && !empty($stock['symbol'])) {
                $symbols[] = $stock['symbol'];
            }
        }

        return $symbols;
    }

    /**
     * Fetch profile data for multiple symbols
     * @param array $symbols Array of stock symbols
     * @return array Associative array of profile data keyed by symbol
     */
    private function fetchProfileData($symbols) {
        $profileData = array();

        if (empty($symbols)) {
            return $profileData;
        }

        // Fetch profile for each symbol
        foreach ($symbols as $symbol) {
            $response = $this->makeRequest("/profile", array('symbol' => $symbol));

            if ($response !== false) {
                $profile = json_decode($response, true);

                // Profile endpoint returns array with single item
                if (is_array($profile) && !empty($profile)) {
                    $profileData[$symbol] = isset($profile[0]) ? $profile[0] : $profile;
                }
            }
        }

        return $profileData;
    }

    /**
     * Merge most-actives data with profile data
     * @param array $apiData Original most-actives data
     * @param array $profileData Profile data keyed by symbol
     * @return array Enriched data array
     */
    private function mergeWithProfileData($apiData, $profileData) {
        $enrichedData = array();

        foreach ($apiData as $stock) {
            $symbol = isset($stock['symbol']) ? $stock['symbol'] : '';
            $enrichedStock = $stock;

            // Merge profile data if available
            if (!empty($symbol) && isset($profileData[$symbol])) {
                $profile = $profileData[$symbol];

                // Add profile fields that are missing in most-actives
                $enrichedStock['sector'] = isset($profile['sector']) ? $profile['sector'] : '';
                $enrichedStock['industry'] = isset($profile['industry']) ? $profile['industry'] : '';
                $enrichedStock['country'] = isset($profile['country']) ? $profile['country'] : '';
                $enrichedStock['marketCap'] = isset($profile['marketCap']) ? $profile['marketCap'] : '';
                $enrichedStock['peRatio'] = isset($profile['peRatio']) ? $profile['peRatio'] : '';
                $enrichedStock['volume'] = isset($profile['volAvg']) ? $profile['volAvg'] : '';
                $enrichedStock['description'] = isset($profile['description']) ? $profile['description'] : '';

                // Use profile name if available (may be more complete)
                if (isset($profile['companyName']) && !empty($profile['companyName'])) {
                    $enrichedStock['name'] = $profile['companyName'];
                }
            }

            $enrichedData[] = $enrichedStock;
        }

        return $enrichedData;
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
            return array();
        }

        return array_slice($this->data, 0, $limit);
    }

    /**
     * Get row by key field value
     * @param string $value Value to search for (e.g., stock ticker)
     * @return array|null Data row or null if not found
     */
    public function getByKeyField($value) {
        // Search in loaded data
        foreach ($this->data as $row) {
            if (isset($row[$this->keyField]) && strcasecmp($row[$this->keyField], $value) === 0) {
                return $row;
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
     * Make HTTP request to FMP API using cURL
     * @param string $endpoint API endpoint (e.g., "/stock-screener")
     * @param array $params Query parameters
     * @return string|bool Response body or false on failure
     */
    private function makeRequest($endpoint, $params = array()) {
        // Check rate limit
        if (!$this->checkRateLimit()) {
            error_log("FmpApiClient: Rate limit exceeded");
            return false;
        }

        // Generate cache key before adding API key to params
        $paramsHash = md5(serialize($params));
        $cacheKey = 'fmp_' . str_replace('/', '_', trim($endpoint, '/')) . '_' . $paramsHash;

        // Check cache first
        $cachedResponse = $this->cacheService->get($cacheKey);

        if ($cachedResponse !== null) {
            return $cachedResponse;
        }

        // Add API key to parameters
        $params["apikey"] = $this->apiKey;

        // Build URL
        $url = $this->baseUrl . $endpoint . "?" . http_build_query($params);

        // Make cURL request
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            "Accept: application/json",
            "User-Agent: PHP-FMP-Client/1.0"
        ));

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);

        curl_close($ch);

        // Track request for rate limiting
        $this->trackRequest();

        // Handle errors
        if ($response === false) {
            error_log("FmpApiClient: cURL error - " . $error);
            return false;
        }

        if ($httpCode !== 200) {
            error_log("FmpApiClient: HTTP error " . $httpCode . " for endpoint " . $endpoint);
            return false;
        }

        // Cache successful response
        $this->cacheService->set($cacheKey, $response, $this->cacheTtl);

        return $response;
    }

    /**
     * Check if request is within rate limit
     * @return bool True if within limit
     */
    private function checkRateLimit() {
        $now = time();
        $windowStart = $now - 60; // 1-minute window

        // Remove timestamps outside the window
        $this->requestTimestamps = array_filter($this->requestTimestamps, function($timestamp) use ($windowStart) {
            return $timestamp > $windowStart;
        });

        // Check if under limit
        return count($this->requestTimestamps) < FMP_RATE_LIMIT;
    }

    /**
     * Track a request for rate limiting
     */
    private function trackRequest() {
        $this->requestTimestamps[] = time();
        $this->requestCount++;
    }

    /**
     * Apply client-side filters to loaded data
     * @param array $data Array of stock data
     * @return array Filtered data
     */
    private function applyFilters($data) {
        if (empty($data) || empty($this->filters)) {
            return $data;
        }

        $filtered = array();

        foreach ($data as $stock) {
            if ($this->matchesFilters($stock)) {
                $filtered[] = $stock;
            }
        }

        return $filtered;
    }

    /**
     * Check if a stock matches all filters
     * @param array $stock Stock data
     * @return bool True if matches all filters
     */
    private function matchesFilters($stock) {
        // Price filters - only apply if filter value is not empty
        if (!empty($this->filters['price_min'])) {
            $price = floatval(str_replace(',', '', $stock['Price']));
            if ($price < floatval($this->filters['price_min'])) {
                return false;
            }
        }

        if (!empty($this->filters['price_max'])) {
            $price = floatval(str_replace(',', '', $stock['Price']));
            if ($price > floatval($this->filters['price_max'])) {
                return false;
            }
        }

        // Market Cap filters (if available in data)
        if (!empty($this->filters['marketcap_min']) && !empty($stock['Market Cap'])) {
            $marketCap = floatval(str_replace(',', '', $stock['Market Cap']));
            if ($marketCap < floatval($this->filters['marketcap_min'])) {
                return false;
            }
        }

        if (!empty($this->filters['marketcap_max']) && !empty($stock['Market Cap'])) {
            $marketCap = floatval(str_replace(',', '', $stock['Market Cap']));
            if ($marketCap > floatval($this->filters['marketcap_max'])) {
                return false;
            }
        }

        // Exchange filter
        if (!empty($this->filters['exchange'])) {
            if (strcasecmp($stock['Exchange'], $this->filters['exchange']) !== 0) {
                return false;
            }
        }

        // Country filter (if available in data)
        if (!empty($this->filters['country']) && !empty($stock['Country'])) {
            if (strcasecmp($stock['Country'], $this->filters['country']) !== 0) {
                return false;
            }
        }

        return true;
    }
}
