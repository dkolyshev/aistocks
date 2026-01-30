<?php
/**
 * FileCacheService - File-based caching service
 * Implements simple file-based caching with TTL expiration
 * No external dependencies, follows Single Responsibility Principle
 * PHP 5.5 compatible
 */

class FileCacheService {
    private $cacheDir;

    /**
     * Constructor
     * @param string $cacheDir Directory path for cache storage
     */
    public function __construct($cacheDir) {
        $this->cacheDir = rtrim($cacheDir, '/');
        $this->ensureCacheDirectoryExists();
    }

    /**
     * Get data from cache
     * @param string $key Cache key
     * @return mixed|null Cached data or null if not found/expired
     */
    public function get($key) {
        $cacheFile = $this->getCacheFilePath($key);

        if (!file_exists($cacheFile)) {
            return null;
        }

        $content = file_get_contents($cacheFile);

        if ($content === false) {
            return null;
        }

        $cacheData = json_decode($content, true);

        if (!is_array($cacheData) || !isset($cacheData['expires_at']) || !isset($cacheData['data'])) {
            // Invalid cache format
            $this->clear($key);
            return null;
        }

        // Check expiration
        if (time() > $cacheData['expires_at']) {
            $this->clear($key);
            return null;
        }

        return $cacheData['data'];
    }

    /**
     * Store data in cache
     * @param string $key Cache key
     * @param mixed $data Data to cache
     * @param int $ttl Time to live in seconds
     * @return bool Success status
     */
    public function set($key, $data, $ttl) {
        $cacheFile = $this->getCacheFilePath($key);

        $cacheData = array(
            'expires_at' => time() + $ttl,
            'data' => $data
        );

        $json = json_encode($cacheData);

        if ($json === false) {
            error_log("FileCacheService: Failed to encode data for key: " . $key);
            return false;
        }

        $result = file_put_contents($cacheFile, $json);

        return $result !== false;
    }

    /**
     * Check if cache key exists and is valid
     * @param string $key Cache key
     * @return bool True if cache exists and not expired
     */
    public function has($key) {
        $cacheFile = $this->getCacheFilePath($key);

        if (!file_exists($cacheFile)) {
            return false;
        }

        $content = file_get_contents($cacheFile);

        if ($content === false) {
            return false;
        }

        $cacheData = json_decode($content, true);

        if (!is_array($cacheData) || !isset($cacheData['expires_at'])) {
            return false;
        }

        // Check if expired
        if (time() > $cacheData['expires_at']) {
            $this->clear($key);
            return false;
        }

        return true;
    }

    /**
     * Clear/delete cache entry
     * @param string $key Cache key
     * @return bool Success status
     */
    public function clear($key) {
        $cacheFile = $this->getCacheFilePath($key);

        if (!file_exists($cacheFile)) {
            return true;
        }

        return unlink($cacheFile);
    }

    /**
     * Get cache file path for a key
     * @param string $key Cache key
     * @return string Full file path
     */
    private function getCacheFilePath($key) {
        $safeKey = $this->sanitizeKey($key);
        return $this->cacheDir . '/' . $safeKey . '.cache';
    }

    /**
     * Sanitize cache key to safe filename
     * @param string $key Cache key
     * @return string Sanitized key
     */
    private function sanitizeKey($key) {
        return preg_replace('/[^a-zA-Z0-9_-]/', '_', $key);
    }

    /**
     * Ensure cache directory exists
     */
    private function ensureCacheDirectoryExists() {
        if (!file_exists($this->cacheDir)) {
            mkdir($this->cacheDir, 0755, true);
        }
    }
}
