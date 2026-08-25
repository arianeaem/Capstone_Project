<?php

$emptyFiles = [];
foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator('resources/views')) as $file) {
    if ($file->isFile() && str_ends_with($file->getFilename(), '.blade.php')) {
        if ($file->getSize() === 0) {
            $emptyFiles[] = $file->getPathname();
        }
    }
}

if (empty($emptyFiles)) {
    echo "NO EMPTY BLADE FILES FOUND.\n";
} else {
    echo "EMPTY BLADE FILES FOUND:\n";
    foreach ($emptyFiles as $ef) {
        echo " - " . $ef . "\n";
    }
}
