<?php
/**
 * SettingsManager - Handles CRUD operations for report settings
 * PHP 5.5 compatible
 */

class SettingsManager implements SettingsManagerInterface {
    private $settingsFile;
    private $fileSystem;
    private $settingsCache = null;

    /**
     * Constructor - only assigns dependencies, no side effects
     * @param string $settingsFile Path to settings JSON file
     * @param FileSystemInterface|null $fileSystem File system abstraction (optional)
     */
    public function __construct($settingsFile, $fileSystem = null) {
        $this->settingsFile = $settingsFile;
        $this->fileSystem = $fileSystem !== null ? $fileSystem : new FileSystem();
    }

    /**
     * Ensure settings file exists, create with empty array if not
     * Call this explicitly from composition root during bootstrap
     * @return bool True if file exists or was created successfully
     */
    public function ensureFileExists() {
        if ($this->fileSystem->exists($this->settingsFile)) {
            return true;
        }

        $emptySettings = [];
        return $this->fileSystem->write($this->settingsFile, json_encode($emptySettings, JSON_PRETTY_PRINT)) !== false;
    }

    /**
     * Get all settings
     * @return array Array of all settings
     */
    public function getAllSettings() {
        if ($this->settingsCache !== null) {
            return $this->settingsCache;
        }

        if (!$this->fileSystem->exists($this->settingsFile)) {
            $this->settingsCache = [];
            return $this->settingsCache;
        }

        $content = $this->fileSystem->read($this->settingsFile);
        if ($content === false) {
            $this->settingsCache = [];
            return $this->settingsCache;
        }

        $settings = json_decode($content, true);
        if (!is_array($settings)) {
            $this->settingsCache = [];
            return $this->settingsCache;
        }

        // Migrate settings to new format on retrieval for backward compatibility
        $migratedSettings = [];
        foreach ($settings as $setting) {
            $migratedSettings[] = $this->migrateSettingFormat($setting);
        }

        $this->settingsCache = $migratedSettings;

        return $this->settingsCache;
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
                return $this->migrateSettingFormat($setting);
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

        // Migrate to new format before adding
        $settingData = $this->migrateSettingFormat($settingData);

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

        // Migrate to new format before updating
        $settingData = $this->migrateSettingFormat($settingData);

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

        $result = $this->fileSystem->write($this->settingsFile, $json) !== false;

        if ($result) {
            $this->settingsCache = $settings;
        }

        return $result;
    }

    /**
     * Migrate old settings format to new format
     * Converts api_placeholder to data_source and adds data_source_type
     * @param array $settingData Setting data to migrate
     * @return array Migrated setting data
     */
    public function migrateSettingFormat($settingData) {
        // If already using new format, return as-is
        if (isset($settingData["data_source"]) && isset($settingData["data_source_type"])) {
            return $settingData;
        }

        // Convert old api_placeholder to new data_source
        if (isset($settingData["api_placeholder"]) && !isset($settingData["data_source"])) {
            $settingData["data_source"] = $settingData["api_placeholder"];
            unset($settingData["api_placeholder"]);
        }

        // Add data_source_type if missing (default to csv for backward compatibility)
        if (!isset($settingData["data_source_type"])) {
            $settingData["data_source_type"] = "csv";
        }

        return $settingData;
    }

    /**
     * Migrate all settings in the file to new format
     * @return bool Success status
     */
    public function migrateAllSettings() {
        $allSettings = $this->getAllSettings();
        $migrated = false;

        foreach ($allSettings as $index => $setting) {
            $migratedSetting = $this->migrateSettingFormat($setting);
            if ($migratedSetting !== $setting) {
                $allSettings[$index] = $migratedSetting;
                $migrated = true;
            }
        }

        if ($migrated) {
            return $this->saveSettings($allSettings);
        }

        return true;
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

        // Support both old (api_placeholder) and new (data_source) field names for backward compatibility
        if (empty($settingData["data_source"]) && empty($settingData["api_placeholder"])) {
            $errors[] = "Data source is required";
        }

        // Validate data_source_type if present
        if (isset($settingData["data_source_type"])) {
            $validTypes = ["csv", "api"];
            if (!in_array($settingData["data_source_type"], $validTypes)) {
                $errors[] = "Data source type must be 'csv' or 'api'";
            }

            // Validate api_config when data_source_type is "api"
            if ($settingData["data_source_type"] === "api") {
                if (empty($settingData["api_config"])) {
                    $errors[] = "API configuration is required when data source type is 'api'";
                } elseif (is_array($settingData["api_config"])) {
                    if (empty($settingData["api_config"]["endpoint"])) {
                        $errors[] = "API endpoint is required in API configuration";
                    }
                    // Filters are optional, no validation needed
                }
            }
        }

        if (!isset($settingData["stock_count"]) || $settingData["stock_count"] < 1) {
            $errors[] = "Stock count must be at least 1";
        }

        // Validate HTML template fields when state is "custom"
        $templateFields = [
            "report_intro_html" => "Report Intro HTML",
            "stock_block_html" => "Stock Block HTML",
            "disclaimer_html" => "Disclaimer HTML",
        ];

        foreach ($templateFields as $fieldKey => $fieldLabel) {
            $stateKey = $fieldKey . "_state";
            $state = isset($settingData[$stateKey]) ? $settingData[$stateKey] : "default";

            if ($state === "custom" && empty(trim($settingData[$fieldKey]))) {
                $errors[] = $fieldLabel . " is required when set to Custom";
            }
        }

        return $errors;
    }
}
