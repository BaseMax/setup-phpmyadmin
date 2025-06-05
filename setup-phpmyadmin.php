<?php
// --- Helper Functions ---
function logMsg($msg) {
    echo '[' . date('H:i:s') . "] $msg\n";
}

function deleteDir($dir) {
    if (!file_exists($dir)) return;
    foreach (array_diff(scandir($dir), ['.', '..']) as $file) {
        $path = "$dir/$file";
        is_dir($path) ? deleteDir($path) : unlink($path);
    }
    rmdir($dir);
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
    die("❌ Failed to download from $downloadUrl\n");
}
file_put_contents($zipFile, $download);

// --- Step 2: Create necessary directories ---
safeMkdir($pmaDir);
safeMkdir($tempDir);

// --- Step 3: Extract zip to temp ---
$zip = new ZipArchive();
if ($zip->open($zipFile) !== TRUE) {
    unlink($zipFile);
    die("❌ Failed to open ZIP file.\n");
}

logMsg("Extracting ZIP to temporary directory...");
$zip->extractTo($tempDir);
$zip->close();

// Detect top-level folder inside ZIP
$firstEntry = $zip->getNameIndex(0);
$baseFolder = explode('/', $firstEntry)[0];
$sourcePath = "$tempDir/$baseFolder";

// --- Step 4: Copy extracted files into 'pma/' ---
logMsg("Moving files to '$pmaDir/'...");

$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($sourcePath, RecursiveDirectoryIterator::SKIP_DOTS),
    RecursiveIteratorIterator::SELF_FIRST
);

foreach ($iterator as $item) {
    $targetPath = $pmaDir . DIRECTORY_SEPARATOR . $iterator->getSubPathName();
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
