<?php
/**
 * ControllerInterface - Base contract for controllers
 * PHP 5.5 compatible
 */

interface ControllerInterface {
    /**
     * Handle the request and return response
     * @return array Response with success status and message
     */
    public function handleRequest();
}
