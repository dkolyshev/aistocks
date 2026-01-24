<?php
/**
 * FileSystem - Default implementation of file system operations
 * PHP 5.5 compatible
 */

require_once dirname(dirname(__FILE__)) . "/Contracts/FileSystemInterface.php";

class FileSystem implements FileSystemInterface {
    /**
     * Check if file or directory exists
     * @param string $path Path to check
     * @return bool True if exists
     */
    public function exists($path) {
        return file_exists($path);
    }

    /**
     * Read file contents
     * @param string $path Path to file
     * @return string|false File contents or false on failure
     */
    public function read($path) {
        if (!$this->exists($path)) {
            return false;
        }
        return file_get_contents($path);
    }

    /**
     * Write content to file
     * @param string $path Path to file
     * @param string $content Content to write
     * @return int|false Number of bytes written or false on failure
     */
    public function write($path, $content) {
        return file_put_contents($path, $content);
    }

    /**
     * Delete file
     * @param string $path Path to file
     * @return bool True on success
     */
    public function delete($path) {
        if (!$this->exists($path)) {
            return false;
        }
        return unlink($path);
    }

    /**
     * Check if path is a directory
     * @param string $path Path to check
     * @return bool True if directory
     */
    public function isDirectory($path) {
        return is_dir($path);
    }

    /**
     * Get file modification time
     * @param string $path Path to file
     * @return int|false Modification timestamp or false on failure
     */
    public function getModificationTime($path) {
        if (!$this->exists($path)) {
            return false;
        }
        return filemtime($path);
    }

    /**
     * Open directory handle
     * @param string $path Directory path
     * @return resource|false Directory handle or false on failure
     */
    public function openDirectory($path) {
        if (!$this->isDirectory($path)) {
            return false;
        }
        return opendir($path);
    }

    /**
     * Read entry from directory handle
     * @param resource $handle Directory handle
     * @return string|false Entry name or false when done
     */
    public function readDirectory($handle) {
        return readdir($handle);
    }

    /**
     * Close directory handle
     * @param resource $handle Directory handle
     */
    public function closeDirectory($handle) {
        closedir($handle);
    }
}
