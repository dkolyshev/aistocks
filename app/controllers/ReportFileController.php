<?php
/**
 * ReportFileController - Handles report file operations
 * Single Responsibility: Only handles report file listing and deletion
 * PHP 5.5 compatible
 */

class ReportFileController {
    private $reportsDir;
    private $fileSystem;
    private $dateFormat;
    private $requestValidator;

    /**
     * Constructor with dependency injection
     * @param string $reportsDir Reports directory path
     * @param FileSystemInterface|null $fileSystem File system abstraction
     * @param string $dateFormat Date format for display
     * @param RequestValidator|null $requestValidator Request validator
     */
    public function __construct($reportsDir, $fileSystem = null, $dateFormat = "Y-m-d H:i:s", $requestValidator = null) {
        $this->reportsDir = rtrim($reportsDir, "/");
        $this->fileSystem = $fileSystem !== null ? $fileSystem : new FileSystem();
        $this->dateFormat = $dateFormat;
        $this->requestValidator = $requestValidator !== null ? $requestValidator : new RequestValidator();
    }

    /**
     * Handle the request based on action
     * @return array Response with success status and message
     */
    public function handleRequest() {
        $error = $this->requestValidator->requirePost();
        if ($error !== null) {
            return $error;
        }

        $action = isset($_POST["action"]) ? $_POST["action"] : "";

        if ($action === Action::DELETE_REPORT) {
            return $this->handleDeleteReport();
        } elseif ($action === Action::DELETE_ALL_REPORTS) {
            return $this->handleDeleteAllReports();
        }

        return ["success" => false, "message" => "Unknown action"];
    }

    /**
     * Get list of report files from reports directory
     * @return array Array of report file info (filename, created date)
     */
    public function getReportFiles() {
        $files = [];

        if (!$this->fileSystem->isDirectory($this->reportsDir)) {
            return $files;
        }

        $dirHandle = $this->fileSystem->openDirectory($this->reportsDir);
        if ($dirHandle === false) {
            return $files;
        }

        while (($filename = $this->fileSystem->readDirectory($dirHandle)) !== false) {
            $filePath = $this->reportsDir . "/" . $filename;

            // Skip directories, hidden files, and .htaccess
            if ($this->fileSystem->isDirectory($filePath) || $this->isHiddenFile($filename)) {
                continue;
            }

            $modTime = $this->fileSystem->getModificationTime($filePath);
            $files[] = [
                "filename" => $filename,
                "created" => date($this->dateFormat, $modTime),
                "path" => $filePath,
            ];
        }

        $this->fileSystem->closeDirectory($dirHandle);

        // Sort by filename (case-insensitive) ascending
        usort($files, function ($a, $b) {
            return strcasecmp($a["filename"], $b["filename"]);
        });

        return $files;
    }

    /**
     * Handle delete single report file request
     * @return array Response with success status and message
     */
    public function handleDeleteReport() {
        $filename = isset($_POST["report_filename"]) ? $_POST["report_filename"] : "";

        if (empty($filename)) {
            return ["success" => false, "message" => "Filename is required"];
        }

        // Sanitize filename to prevent directory traversal
        $filename = basename($filename);
        $filePath = $this->reportsDir . "/" . $filename;

        if (!$this->fileSystem->exists($filePath)) {
            return ["success" => false, "message" => "File not found"];
        }

        $result = $this->fileSystem->delete($filePath);
        $message = $result ? "Report file deleted successfully" : "Failed to delete report file";

        return ["success" => $result, "message" => $message];
    }

    /**
     * Handle delete all report files request
     * @return array Response with success status and message
     */
    public function handleDeleteAllReports() {
        if (!$this->fileSystem->isDirectory($this->reportsDir)) {
            return ["success" => false, "message" => "Reports directory not found"];
        }

        $deletedCount = 0;
        $errorCount = 0;

        $dirHandle = $this->fileSystem->openDirectory($this->reportsDir);
        if ($dirHandle === false) {
            return ["success" => false, "message" => "Cannot open reports directory"];
        }

        while (($filename = $this->fileSystem->readDirectory($dirHandle)) !== false) {
            $filePath = $this->reportsDir . "/" . $filename;

            // Skip directories, hidden files, and .htaccess
            if ($this->fileSystem->isDirectory($filePath) || $this->isHiddenFile($filename)) {
                continue;
            }

            if ($this->fileSystem->delete($filePath)) {
                $deletedCount++;
            } else {
                $errorCount++;
            }
        }

        $this->fileSystem->closeDirectory($dirHandle);

        if ($errorCount > 0) {
            $message = "Deleted {$deletedCount} files, failed to delete {$errorCount} files";
            return ["success" => $deletedCount > 0, "message" => $message];
        }

        $message = "Deleted {$deletedCount} report files successfully";
        return ["success" => true, "message" => $message];
    }

    /**
     * Check if filename is a hidden file (starts with dot) or .htaccess
     * @param string $filename Filename to check
     * @return bool True if hidden file
     */
    private function isHiddenFile($filename) {
        return strlen($filename) === 0 || $filename[0] === "." || $filename === ".htaccess";
    }
}
