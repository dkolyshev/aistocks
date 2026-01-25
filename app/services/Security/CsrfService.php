<?php
/**
 * CsrfService - Session-based CSRF token management
 * Single Responsibility: Generate and validate CSRF tokens
 * PHP 5.5 compatible
 */

class CsrfService implements CsrfServiceInterface {
    /**
     * @var string Session key for storing the token
     */
    const SESSION_KEY = "_csrf_token";

    /**
     * @var string Form field name for the token
     */
    const FIELD_NAME = "csrf_token";

    /**
     * @var int Token length in bytes (32 bytes = 64 hex characters)
     */
    const TOKEN_LENGTH = 32;

    /**
     * Constructor - ensures session is started
     */
    public function __construct() {
        $this->ensureSessionStarted();
    }

    /**
     * {@inheritdoc}
     */
    public function getToken() {
        if (!isset($_SESSION[self::SESSION_KEY]) || empty($_SESSION[self::SESSION_KEY])) {
            $_SESSION[self::SESSION_KEY] = $this->generateToken();
        }
        return $_SESSION[self::SESSION_KEY];
    }

    /**
     * {@inheritdoc}
     */
    public function validateToken($token) {
        if (empty($token) || !isset($_SESSION[self::SESSION_KEY])) {
            return false;
        }

        // Use timing-safe comparison to prevent timing attacks
        return $this->timingSafeEquals($_SESSION[self::SESSION_KEY], $token);
    }

    /**
     * {@inheritdoc}
     */
    public function regenerateToken() {
        $_SESSION[self::SESSION_KEY] = $this->generateToken();
        return $_SESSION[self::SESSION_KEY];
    }

    /**
     * {@inheritdoc}
     */
    public function getTokenFieldName() {
        return self::FIELD_NAME;
    }

    /**
     * Generate a cryptographically secure random token
     * @return string Hex-encoded token
     */
    private function generateToken() {
        // PHP 5.5 compatible: use openssl_random_pseudo_bytes
        $bytes = openssl_random_pseudo_bytes(self::TOKEN_LENGTH, $strong);

        if ($bytes === false || !$strong) {
            // Fallback for systems without strong randomness
            $bytes = $this->fallbackRandomBytes(self::TOKEN_LENGTH);
        }

        return bin2hex($bytes);
    }

    /**
     * Fallback random bytes generation for systems without OpenSSL strong randomness
     * Uses cryptographically secure sources only (PHP 5.5 compatible)
     * @param int $length Number of bytes
     * @return string Random bytes
     * @throws RuntimeException If no secure random source is available
     */
    private function fallbackRandomBytes($length) {
        // Try /dev/urandom (Unix/Linux)
        if (is_readable("/dev/urandom")) {
            $handle = fopen("/dev/urandom", "rb");
            if ($handle !== false) {
                $bytes = fread($handle, $length);
                fclose($handle);
                if ($bytes !== false && strlen($bytes) === $length) {
                    return $bytes;
                }
            }
        }

        // Try mcrypt if available
        if (function_exists("mcrypt_create_iv")) {
            /** @disregard P1010, P1011 */
            $bytes = mcrypt_create_iv($length, MCRYPT_DEV_URANDOM);
            if ($bytes !== false && strlen($bytes) === $length) {
                return $bytes;
            }
        }

        // No secure random source available - fail secure
        throw new RuntimeException(
            "Unable to generate cryptographically secure CSRF token. " . "No secure random source available (OpenSSL, /dev/urandom, or mcrypt required)."
        );
    }

    /**
     * Timing-safe string comparison to prevent timing attacks
     * PHP 5.5 compatible (hash_equals was added in PHP 5.6)
     * @param string $known Known string
     * @param string $user User-provided string
     * @return bool True if equal
     */
    private function timingSafeEquals($known, $user) {
        // Use hash_equals if available (PHP 5.6+)
        if (function_exists("hash_equals")) {
            return hash_equals($known, $user);
        }

        // Manual timing-safe comparison for PHP 5.5
        // Hash both inputs to normalize length and prevent length-based timing leaks
        $knownHash = hash("sha256", $known);
        $userHash = hash("sha256", $user);
        $hashLen = strlen($knownHash);

        // XOR the length difference into result to avoid early return
        $result = strlen($known) ^ strlen($user);

        for ($i = 0; $i < $hashLen; $i++) {
            $result |= ord($knownHash[$i]) ^ ord($userHash[$i]);
        }

        return $result === 0;
    }

    /**
     * Ensure PHP session is started
     */
    private function ensureSessionStarted() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }
}
