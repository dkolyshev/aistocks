<?php
/**
 * SettingsManager - Handles CRUD operations for report settings
 * PHP 5.5 compatible
 */

class SettingsManager {
    private $settingsFile;

    /**
     * Constructor
     * @param string $settingsFile Path to settings JSON file
     */
    public function __construct($settingsFile) {
        $this->settingsFile = $settingsFile;

        if (!file_exists($this->settingsFile)) {
            $this->initializeSettingsFile();
        }
    }

    /**
     * Initialize settings file with empty array
     */
    private function initializeSettingsFile() {
        $emptySettings = [];
        file_put_contents($this->settingsFile, json_encode($emptySettings, JSON_PRETTY_PRINT));
    }

    /**
     * Get all settings
     * @return array Array of all settings
     */
    public function getAllSettings() {
        if (!file_exists($this->settingsFile)) {
            return [];
        }

        $content = file_get_contents($this->settingsFile);
        $settings = json_decode($content, true);

        return is_array($settings) ? $settings : [];
    }

    /**
     * Get setting by file name
     * @param string $fileName The report file name
     * @return array|null Setting data or null if not found
     */
    public function getSettingByFileName($fileName) {
        $allSettings = $this->getAllSettings();

        foreach ($allSettings as $setting) {
            if (isset($setting["file_name"]) && $setting["file_name"] === $fileName) {
                return $setting;
            }
        }

        return null;
    }

    /**
     * Add new setting
     * @param array $settingData Setting data
     * @return bool Success status
     */
    public function addSetting($settingData) {
        $allSettings = $this->getAllSettings();

        // Check if file name already exists
        if ($this->getSettingByFileName($settingData["file_name"]) !== null) {
            return false;
        }

        $allSettings[] = $settingData;

        return $this->saveSettings($allSettings);
    }

    /**
     * Update existing setting
     * @param string $fileName The report file name to update
     * @param array $settingData New setting data
     * @return bool Success status
     */
    public function updateSetting($fileName, $settingData) {
        $allSettings = $this->getAllSettings();
        $updated = false;

        foreach ($allSettings as $index => $setting) {
            if (isset($setting["file_name"]) && $setting["file_name"] === $fileName) {
                $allSettings[$index] = $settingData;
                $updated = true;
                break;
            }
        }

        if (!$updated) {
            return false;
        }

        return $this->saveSettings($allSettings);
    }

    /**
     * Delete setting by file name
     * @param string $fileName The report file name to delete
     * @return bool Success status
     */
    public function deleteSetting($fileName) {
        $allSettings = $this->getAllSettings();
        $newSettings = [];
        $found = false;

        foreach ($allSettings as $setting) {
            if (!isset($setting["file_name"]) || $setting["file_name"] !== $fileName) {
                $newSettings[] = $setting;
            } else {
                $found = true;
            }
        }

        if (!$found) {
            return false;
        }

        return $this->saveSettings($newSettings);
    }

    /**
     * Save settings to file
     * @param array $settings Settings array
     * @return bool Success status
     */
    private function saveSettings($settings) {
        $json = json_encode($settings, JSON_PRETTY_PRINT);

        if ($json === false) {
            return false;
        }

        return file_put_contents($this->settingsFile, $json) !== false;
    }

    /**
     * Validate setting data
     * @param array $settingData Setting data to validate
     * @return array Array of validation errors (empty if valid)
     */
    public function validateSetting($settingData) {
        $errors = [];

        if (empty($settingData["file_name"])) {
            $errors[] = "File name is required";
        }

        if (empty($settingData["report_title"])) {
            $errors[] = "Report title is required";
        }

        if (!isset($settingData["stock_count"]) || $settingData["stock_count"] < 1) {
            $errors[] = "Stock count must be at least 1";
        }

        return $errors;
    }
}
