<?php
/*
 *
 * @Name: setup-phpmyadmin
 * @Author: Max Base
 * @Date: 06/06/2025
 * @Repository: https://github.com/BaseMax/setup-phpmyadmin
 *
*/

error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
set_time_limit(0);
ini_set('memory_limit', '10240000000M');
ini_set('log_errors', '1');
ini_set('error_log', __DIR__ . '/phpmyadmin_setup_errors.log');

// --- Helper Functions ---
function isCli() {
    return (php_sapi_name() === 'cli' || defined('STDIN'));
}

function println($msg) {
    echo $msg . (isCli() ? "\n" : "<br>\n");
}

function logMsg($msg) {
    println('[' . date('H:i:s') . "] $msg");
}

function deleteDir($dir) {
    if (!file_exists($dir)) return;

    $items = array_diff(scandir($dir), ['.', '..']);
    foreach ($items as $item) {
        $path = "$dir/$item";

        if (is_dir($path)) {
            deleteDir($path);
        } else {
            if (!@unlink($path)) {
                logMsg("⚠️ Failed to delete file: $path");
            }
        }
    }

    if (!@rmdir($dir)) {
        logMsg("⚠️ Failed to remove directory: $dir");
    }
}

function safeMkdir($dir, $permissions = 0755) {
    if (!is_dir($dir)) {
        mkdir($dir, $permissions, true);
    }
}

// --- Configuration ---
$downloadUrl = 'https://www.phpmyadmin.net/downloads/phpMyAdmin-latest-all-languages.zip';
$zipFile = 'phpmyadmin.zip';
$pmaDir = 'pma';
$tempDir = 'temp_pma_extract';

// --- Step 1: Download zip ---
logMsg("Downloading phpMyAdmin...");
$download = file_get_contents($downloadUrl);
if ($download === false) {
    println("❌ Failed to download from $downloadUrl");
    exit(1);
}
file_put_contents($zipFile, $download);

// --- Step 2: Create necessary directories ---
safeMkdir($pmaDir);
safeMkdir($tempDir);

if (!extension_loaded('zip')) {
    println("❌ PHP zip extension is not loaded.");
    exit(1);
}

if (!class_exists('ZipArchive')) {
    println("❌ PHP Zip extension is not installed or enabled.");
    exit(1);
}

// --- Step 3: Extract zip to temp ---
$zip = new ZipArchive();
if ($zip->open($zipFile) !== TRUE) {
    @unlink($zipFile);
    println("❌ Failed to open ZIP file.");
    exit(1);
}

// Detect top-level folder inside ZIP
$firstEntry = $zip->getNameIndex(0);
if ($firstEntry === false) {
    println("❌ Failed to read ZIP entries.");
    @unlink($zipFile);
    exit(1);
}

$baseFolder = explode('/', $firstEntry)[0];
$sourcePath = "$tempDir/$baseFolder";

logMsg("Extracting ZIP to temporary directory...");
$zip->extractTo($tempDir);
$zip->close();

// --- Step 4: Copy extracted files into 'pma/' ---
logMsg("Moving files to '$pmaDir/'...");

$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($sourcePath, RecursiveDirectoryIterator::SKIP_DOTS),
    RecursiveIteratorIterator::SELF_FIRST
);

foreach ($iterator as $item) {
    $subPath = $iterator->getInnerIterator()->getSubPathName();
    $targetPath = $pmaDir . DIRECTORY_SEPARATOR . $subPath;

    if ($item->isDir()) {
        safeMkdir($targetPath);
    } else {
        copy($item, $targetPath);
    }
}

// --- Step 5: Cleanup ---
logMsg("Cleaning up temporary files...");
@unlink($zipFile);
deleteDir($tempDir);

logMsg("✅ phpMyAdmin is ready in the '$pmaDir/' directory.");
