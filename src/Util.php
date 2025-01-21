<?php

namespace WhatsAppPHP;

use WhatsAppPHP\Exception\DownloadMediaException;

/**
 * WhatsApp PHP utility class.
 * @package eugabrielsilva/whatsapp-php
 */
class Util
{
    /**
     * Converts a snake_case string to camelCase.
     * @param string $name String to convert.
     * @return string Returns the converted string.
     */
    public static function snakeToCamelCase(string $name)
    {
        $words = explode('_', $name);
        $camelCase = array_shift($words);
        foreach ($words as $word) {
            $camelCase .= ucfirst(strtolower($word));
        }
        return $camelCase;
    }

    /**
     * Formats a phone to international number.
     * @param string $number Number to format.
     * @return string Returns the formatted number.
     */
    public static function formatNumber(string $number)
    {
        return preg_replace('/[^0-9]/', '', $number);
    }

    /**
     * Downloads a remote file.
     * @param string $url Remote URL to download.
     * @param string $path Location folder in where to salve the file.
     * @param string $filename (Optional) Custom filename to set, leave blank to use the original filename.
     * @return string Returns the downloaded file location.
     */
    public static function downloadFile(string $url, string $path, ?string $filename = null): string
    {
        $readStream = @fopen($url, 'rb');
        if ($readStream === false) {
            throw new DownloadMediaException("Failed to open URL: {$url} for download.");
        }

        if (is_null($filename)) $filename = basename(parse_url($url, PHP_URL_PATH));
        $path = rtrim($path, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
        $fullPath = $path . $filename;

        $writeStream = @fopen($fullPath, 'wb');
        if ($writeStream === false) {
            fclose($readStream);
            throw new DownloadMediaException("Failed to create local file at: {$fullPath}.");
        }

        $chunkSize = 64 * 1024; // 64KB

        try {
            while (!feof($readStream)) {
                $chunk = fread($readStream, $chunkSize);
                if ($chunk === false) {
                    throw new DownloadMediaException("Error reading data from URL: {$url}.");
                }
                if (fwrite($writeStream, $chunk) === false) {
                    throw new DownloadMediaException("Error writing data to file: {$fullPath}.");
                }
            }
        } finally {
            fclose($readStream);
            fclose($writeStream);
        }

        return $fullPath;
    }

    /**
     * Checks if a string contains a substring.
     * @param string $haystack The string to search in.
     * @param string $needle The substring to search for.
     * @return bool True if contains, false otherwise.
     */
    public static function stringContains(string $haystack, string $needle)
    {
        return $needle !== '' && strpos($haystack, $needle) !== false;
    }
}
