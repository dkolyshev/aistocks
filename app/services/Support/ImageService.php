<?php
/**
 * ImageService - Handles image processing operations
 * PHP 5.5 compatible
 */

class ImageService {
    /**
     * Convert image file to base64 data URI
     * @param string $imagePath Path to image file
     * @return string Base64 data URI or empty string on failure
     */
    public function convertToDataUri($imagePath) {
        if (empty($imagePath) || !file_exists($imagePath)) {
            return "";
        }

        $imageData = file_get_contents($imagePath);
        if ($imageData === false) {
            return "";
        }

        $mimeType = $this->getMimeType($imagePath);
        $base64Data = base64_encode($imageData);

        return "data:" . $mimeType . ";base64," . $base64Data;
    }

    /**
     * Get image MIME type from file
     * @param string $filePath Path to image file
     * @return string MIME type
     */
    public function getMimeType($filePath) {
        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

        $mimeTypes = [
            "jpg" => "image/jpeg",
            "jpeg" => "image/jpeg",
            "png" => "image/png",
            "gif" => "image/gif",
            "webp" => "image/webp",
            "svg" => "image/svg+xml",
        ];

        if (isset($mimeTypes[$extension])) {
            return $mimeTypes[$extension];
        }

        // Fallback: try to detect with getimagesize if available
        if (function_exists("getimagesize")) {
            $info = @getimagesize($filePath);
            if ($info !== false && isset($info["mime"])) {
                return $info["mime"];
            }
        }

        // Default fallback
        return "image/jpeg";
    }

    /**
     * Check if file is a valid image
     * @param string $filePath Path to file
     * @return bool True if valid image
     */
    public function isValidImage($filePath) {
        if (empty($filePath) || !file_exists($filePath)) {
            return false;
        }

        $mimeType = $this->getMimeType($filePath);
        return strpos($mimeType, "image/") === 0;
    }
}
