<?php
/**
 * SettingsManagerInterface - Contract for settings management
 * PHP 5.5 compatible
 */

interface SettingsManagerInterface {
    /**
     * Get all settings
     * @return array Array of all settings
     */
    public function getAllSettings();

    /**
     * Get setting by file name
     * @param string $fileName The report file name
     * @return array|null Setting data or null if not found
     */
    public function getSettingByFileName($fileName);

    /**
     * Add new setting
     * @param array $settingData Setting data
     * @return bool Success status
     */
    public function addSetting($settingData);

    /**
     * Update existing setting
     * @param string $fileName The report file name to update
     * @param array $settingData New setting data
     * @return bool Success status
     */
    public function updateSetting($fileName, $settingData);

    /**
     * Delete setting by file name
     * @param string $fileName The report file name to delete
     * @return bool Success status
     */
    public function deleteSetting($fileName);

    /**
     * Validate setting data
     * @param array $settingData Setting data to validate
     * @return array Array of validation errors (empty if valid)
     */
    public function validateSetting($settingData);
}
