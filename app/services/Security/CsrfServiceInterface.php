<?php
/**
 * CsrfServiceInterface - Contract for CSRF protection service
 * PHP 5.5 compatible
 */

interface CsrfServiceInterface {
    /**
     * Generate or retrieve the current CSRF token
     * @return string CSRF token
     */
    public function getToken();

    /**
     * Validate a submitted CSRF token
     * @param string $token Token to validate
     * @return bool True if valid
     */
    public function validateToken($token);

    /**
     * Regenerate the CSRF token (after successful validation)
     * @return string New token
     */
    public function regenerateToken();

    /**
     * Get the form field name for the CSRF token
     * @return string Field name
     */
    public function getTokenFieldName();
}
