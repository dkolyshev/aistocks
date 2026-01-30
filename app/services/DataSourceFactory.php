<?php
/**
 * DataSourceFactory - Creates data source instances based on source name
 * Implements Factory Pattern for creating DataSourceInterface implementations
 * PHP 5.5 compatible
 */

class DataSourceFactory {
    private $dataDirectory;
    private $apiKey;
    private $dataMapper;
    private $cacheService;

    /**
     * Constructor
     * @param string $dataDirectory Path to CSV data directory
     * @param string|null $apiKey FMP API key (optional, uses config if null)
     * @param FmpDataMapper|null $dataMapper Data mapper instance (optional)
     * @param FileCacheService|null $cacheService Cache service instance (optional)
     */
    public function __construct($dataDirectory, $apiKey = null, $dataMapper = null, $cacheService = null) {
        $this->dataDirectory = $dataDirectory;
        $this->apiKey = $apiKey !== null ? $apiKey : (defined('FMP_API_KEY') ? FMP_API_KEY : '');
        $this->dataMapper = $dataMapper !== null ? $dataMapper : new FmpDataMapper();

        if ($cacheService !== null) {
            $this->cacheService = $cacheService;
        } else {
            $cacheDir = defined('FMP_CACHE_DIR') ? FMP_CACHE_DIR : __DIR__ . '/../../data/cache';
            $this->cacheService = new FileCacheService($cacheDir);
        }
    }

    /**
     * Create data source instance based on source name
     * @param string $sourceName Source name with type prefix (csv:filename or api:endpoint)
     * @param array|null $apiConfig Optional API configuration (endpoint and filters)
     * @return DataSourceInterface|null Data source instance or null if invalid
     * @throws InvalidArgumentException If source name format is invalid
     */
    public function create($sourceName, $apiConfig = null) {
        if (empty($sourceName)) {
            throw new InvalidArgumentException("Source name cannot be empty");
        }

        // Parse source name format: "type:name"
        $parts = explode(':', $sourceName, 2);

        if (count($parts) !== 2) {
            throw new InvalidArgumentException("Invalid source name format. Expected 'type:name' (e.g., 'csv:data.csv' or 'api:fmp-screener')");
        }

        $type = strtolower(trim($parts[0]));
        $name = trim($parts[1]);

        if (empty($name)) {
            throw new InvalidArgumentException("Source name cannot be empty after type prefix");
        }

        // Create appropriate data source based on type
        switch ($type) {
            case 'csv':
                return $this->createCsvDataSource($name);

            case 'api':
                return $this->createApiDataSource($name, $apiConfig);

            default:
                throw new InvalidArgumentException("Unknown source type: {$type}. Supported types: csv, api");
        }
    }

    /**
     * Create CSV data source
     * @param string $filename CSV file name
     * @return DataSourceInterface CSV data reader instance
     */
    private function createCsvDataSource($filename) {
        $filePath = $this->dataDirectory . '/' . $filename;

        if (!file_exists($filePath)) {
            throw new InvalidArgumentException("CSV file not found: {$filePath}");
        }

        return new CsvDataReader($filePath, "Ticker");
    }

    /**
     * Create API data source
     * @param string $endpoint API endpoint identifier
     * @param array|null $apiConfig API configuration (endpoint and filters)
     * @return DataSourceInterface FMP API client instance
     */
    private function createApiDataSource($endpoint, $apiConfig = null) {
        // Validate API endpoint
        $validEndpoints = array(
            'fmp-most-actives'
        );

        if (!in_array($endpoint, $validEndpoints)) {
            throw new InvalidArgumentException("Unknown API endpoint: {$endpoint}. Supported endpoints: " . implode(', ', $validEndpoints));
        }

        // Extract endpoint and filters from config
        $apiEndpoint = "most-actives";
        $filters = array();

        if (is_array($apiConfig)) {
            if (isset($apiConfig['endpoint'])) {
                $apiEndpoint = $apiConfig['endpoint'];
            }
            if (isset($apiConfig['filters']) && is_array($apiConfig['filters'])) {
                $filters = $apiConfig['filters'];
            }
        }

        // Create FMP API client with data mapper, cache service, endpoint, and filters
        return new FmpApiClient($this->apiKey, "Ticker", $this->dataMapper, $this->cacheService, $apiEndpoint, $filters);
    }

    /**
     * Check if source name is valid
     * @param string $sourceName Source name to validate
     * @return bool True if valid format
     */
    public function isValidSourceName($sourceName) {
        if (empty($sourceName)) {
            return false;
        }

        $parts = explode(':', $sourceName, 2);

        if (count($parts) !== 2) {
            return false;
        }

        $type = strtolower(trim($parts[0]));
        $name = trim($parts[1]);

        if (empty($name)) {
            return false;
        }

        return in_array($type, array('csv', 'api'));
    }

    /**
     * Parse source name into type and name components
     * @param string $sourceName Source name to parse
     * @return array Array with 'type' and 'name' keys, or empty array if invalid
     */
    public function parseSourceName($sourceName) {
        if (!$this->isValidSourceName($sourceName)) {
            return array();
        }

        $parts = explode(':', $sourceName, 2);

        return array(
            'type' => strtolower(trim($parts[0])),
            'name' => trim($parts[1])
        );
    }
}
