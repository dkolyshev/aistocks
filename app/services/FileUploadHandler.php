<?php
/**
 * FileUploadHandler - Handles file uploads (images and PDFs)
 * PHP 5.5 compatible
 */

class FileUploadHandler {
    private $uploadDir;
    private $allowedTypes;
    private $maxFileSize;
    private $errors;

    /**
     * Constructor
     * @param string $uploadDir Directory for uploads
     * @param string $allowedTypes Comma-separated list of allowed MIME types
     * @param int $maxFileSize Maximum file size in bytes
     */
    public function __construct($uploadDir, $allowedTypes, $maxFileSize) {
        $this->uploadDir = rtrim($uploadDir, "/") . "/";
        $this->allowedTypes = explode(",", $allowedTypes);
        $this->maxFileSize = $maxFileSize;
        $this->errors = [];
    }

    /**
     * Handle file upload
     * @param array $file $_FILES array element
     * @param string $customName Optional custom filename (without extension)
     * @return string|false Uploaded filename or false on failure
     */
    public function upload($file, $customName = null) {
        $this->errors = [];

        // Check if file was uploaded
        if (!isset($file["error"]) || is_array($file["error"])) {
            $this->errors[] = "Invalid file upload";
            return false;
        }

        // Check for upload errors
        if ($file["error"] !== UPLOAD_ERR_OK) {
            $this->errors[] = $this->getUploadErrorMessage($file["error"]);
            return false;
        }

        // Validate file size
        if ($file["size"] > $this->maxFileSize) {
            $this->errors[] = "File size exceeds maximum allowed size";
            return false;
        }

        // Validate file type
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file["tmp_name"]);
        finfo_close($finfo);

        if (!in_array($mimeType, $this->allowedTypes)) {
            $this->errors[] = "File type not allowed";
            return false;
        }

        // Generate filename
        if ($customName !== null) {
            $extension = $this->getFileExtension($file["name"]);
            $filename = $this->sanitizeFilename($customName) . "." . $extension;
        } else {
            $filename = $this->sanitizeFilename($file["name"]);
        }

        // Ensure upload directory exists
        if (!is_dir($this->uploadDir)) {
            if (!mkdir($this->uploadDir, 0755, true)) {
                $this->errors[] = "Failed to create upload directory";
                return false;
            }
        }

        $destination = $this->uploadDir . $filename;

        // Move uploaded file
        if (!move_uploaded_file($file["tmp_name"], $destination)) {
            $this->errors[] = "Failed to move uploaded file";
            return false;
        }

        return $filename;
    }

    /**
     * Check if file upload exists in request
     * @param string $fieldName Form field name
     * @return bool True if file was uploaded
     */
    public function hasUploadedFile($fieldName) {
        return isset($_FILES[$fieldName]) && $_FILES[$fieldName]["error"] !== UPLOAD_ERR_NO_FILE;
    }

    /**
     * Get upload errors
     * @return array Array of error messages
     */
    public function getErrors() {
        return $this->errors;
    }

    /**
     * Get last error message
     * @return string Error message
     */
    public function getLastError() {
        return !empty($this->errors) ? end($this->errors) : "";
    }

    /**
     * Sanitize filename
     * @param string $filename Original filename
     * @return string Sanitized filename
     */
    private function sanitizeFilename($filename) {
        // Remove any path components
        $filename = basename($filename);

        // Remove special characters
        $filename = preg_replace("/[^a-zA-Z0-9._-]/", "-", $filename);

        return $filename;
    }

    /**
     * Get file extension
     * @param string $filename Filename
     * @return string File extension
     */
    private function getFileExtension($filename) {
        $parts = explode(".", $filename);
        return strtolower(end($parts));
    }

    /**
     * Get upload error message
     * @param int $errorCode PHP upload error code
     * @return string Error message
     */
    private function getUploadErrorMessage($errorCode) {
        switch ($errorCode) {
            case UPLOAD_ERR_INI_SIZE:
                return "File exceeds upload_max_filesize directive";
            case UPLOAD_ERR_FORM_SIZE:
                return "File exceeds MAX_FILE_SIZE directive";
            case UPLOAD_ERR_PARTIAL:
                return "File was only partially uploaded";
            case UPLOAD_ERR_NO_FILE:
                return "No file was uploaded";
            case UPLOAD_ERR_NO_TMP_DIR:
                return "Missing temporary folder";
            case UPLOAD_ERR_CANT_WRITE:
                return "Failed to write file to disk";
            case UPLOAD_ERR_EXTENSION:
                return "File upload stopped by extension";
            default:
                return "Unknown upload error";
        }
    }

    /**
     * Delete file from upload directory
     * @param string $filename Filename to delete
     * @return bool Success status
     */
    public function deleteFile($filename) {
        $filepath = $this->uploadDir . $filename;

        if (!file_exists($filepath)) {
            return false;
        }

        return unlink($filepath);
    }
}
