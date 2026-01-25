<?php
/**
 * RequestValidator - Validates HTTP request properties
 * Single Responsibility: Handles HTTP request method validation
 * PHP 5.5 compatible
 */

class RequestValidator {
    /**
     * Error message for invalid request method
     */
    const INVALID_METHOD_MESSAGE = "Invalid request method";

    /**
     * Check if the current request uses the specified HTTP method
     * @param string $method Expected HTTP method (GET, POST, PUT, DELETE, etc.)
     * @return bool True if request method matches
     */
    public function isMethod($method) {
        return isset($_SERVER["REQUEST_METHOD"]) && $_SERVER["REQUEST_METHOD"] === strtoupper($method);
    }

    /**
     * Check if current request is a POST request
     * @return bool True if POST request
     */
    public function isPost() {
        return $this->isMethod("POST");
    }

    /**
     * Check if current request is a GET request
     * @return bool True if GET request
     */
    public function isGet() {
        return $this->isMethod("GET");
    }

    /**
     * Require a specific HTTP method, return error response if not matched
     * @param string $method Required HTTP method
     * @return array|null Null if method matches, error response array if not
     */
    public function requireMethod($method) {
        if (!$this->isMethod($method)) {
            return ["success" => false, "message" => self::INVALID_METHOD_MESSAGE];
        }
        return null;
    }

    /**
     * Require POST method, return error response if not POST
     * @return array|null Null if POST, error response array if not
     */
    public function requirePost() {
        return $this->requireMethod("POST");
    }
}
