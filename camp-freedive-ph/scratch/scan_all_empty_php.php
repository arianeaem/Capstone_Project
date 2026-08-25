<?php

$dirs = ['app', 'routes', 'config', 'database', 'resources'];
$emptyFiles = [];

foreach ($dirs as $dir) {
    if (!is_dir($dir)) continue;
    foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir)) as $file) {
        if ($file->isFile() && $file->getExtension() === 'php') {
            if ($file->getSize() === 0) {
                $emptyFiles[] = $file->getPathname();
            }
        }
    }
}

if (empty($emptyFiles)) {
    echo "ALL PHP FILES HAVE CONTENT (NO EMPTY FILES).\n";
} else {
    echo "EMPTY PHP FILES FOUND:\n";
    foreach ($emptyFiles as $ef) {
        echo " - " . $ef . "\n";
    }
}
