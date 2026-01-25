<?php
/**
 * FieldStateResolver - Resolves template field states for form display
 * PHP 5.5 compatible
 */

class FieldStateResolver {
    /**
     * Resolve the state for a template field
     * @param bool $editMode Whether in edit mode
     * @param array|null $editData The edit data array
     * @param string $fieldName The field name (e.g., "report_intro_html")
     * @return string The field state (default, custom, or empty)
     */
    public static function resolve($editMode, $editData, $fieldName) {
        if (!$editMode || $editData === null) {
            return "default";
        }

        $stateKey = $fieldName . "_state";
        if (isset($editData[$stateKey])) {
            return $editData[$stateKey];
        }

        // Legacy: if no state but has content, treat as custom
        if (!empty($editData[$fieldName])) {
            return "custom";
        }

        return "default";
    }

    /**
     * Resolve states for all template fields
     * @param bool $editMode Whether in edit mode
     * @param array|null $editData The edit data array
     * @return array Associative array of field states
     */
    public static function resolveAll($editMode, $editData) {
        return [
            "report_intro_html" => self::resolve($editMode, $editData, "report_intro_html"),
            "stock_block_html" => self::resolve($editMode, $editData, "stock_block_html"),
            "disclaimer_html" => self::resolve($editMode, $editData, "disclaimer_html"),
        ];
    }
}
