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
    const SESSION_KEY = '_csrf_token';

    /**
     * @var string Form field name for the token
     */
    const FIELD_NAME = 'csrf_token';

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
     * @param int $length Number of bytes
     * @return string Random bytes
     */
    private function fallbackRandomBytes($length) {
        $bytes = '';
        for ($i = 0; $i < $length; $i++) {
            $bytes .= chr(mt_rand(0, 255));
        }
        return $bytes;
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
        if (function_exists('hash_equals')) {
            return hash_equals($known, $user);
        }

        // Manual timing-safe comparison for PHP 5.5
        $knownLen = strlen($known);
        $userLen = strlen($user);

        if ($knownLen !== $userLen) {
            return false;
        }

        $result = 0;
        for ($i = 0; $i < $knownLen; $i++) {
            $result |= ord($known[$i]) ^ ord($user[$i]);
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
