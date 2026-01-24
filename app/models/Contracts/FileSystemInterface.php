<?php
/**
 * FileSystemInterface - Contract for file system operations
 * PHP 5.5 compatible
 */

interface FileSystemInterface {
    /**
     * Check if file or directory exists
     * @param string $path Path to check
     * @return bool True if exists
     */
    public function exists($path);

    /**
     * Read file contents
     * @param string $path Path to file
     * @return string|false File contents or false on failure
     */
    public function read($path);

    /**
     * Write content to file
     * @param string $path Path to file
     * @param string $content Content to write
     * @return int|false Number of bytes written or false on failure
     */
    public function write($path, $content);

    /**
     * Delete file
     * @param string $path Path to file
     * @return bool True on success
     */
    public function delete($path);

    /**
     * Check if path is a directory
     * @param string $path Path to check
     * @return bool True if directory
     */
    public function isDirectory($path);

    /**
     * Get file modification time
     * @param string $path Path to file
     * @return int|false Modification timestamp or false on failure
     */
    public function getModificationTime($path);

    /**
     * Open directory handle
     * @param string $path Directory path
     * @return resource|false Directory handle or false on failure
     */
    public function openDirectory($path);

    /**
     * Read entry from directory handle
     * @param resource $handle Directory handle
     * @return string|false Entry name or false when done
     */
    public function readDirectory($handle);

    /**
     * Close directory handle
     * @param resource $handle Directory handle
     */
    public function closeDirectory($handle);
}
