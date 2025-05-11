<?php

$directories = [
    'app/Console',
    'app/Events',
    'app/Jobs',
    'app/Http',
    'app/MTurk',
    'app/Models',
    'app/QueueManager'
];

foreach ($directories as $dir) {
    $files = glob("$dir/*.php");
    foreach ($files as $file) {
        $content = file_get_contents($file);
        
        // Update namespace declarations
        $content = str_replace('namespace oceler\\', 'namespace App\\', $content);
        
        // Update use statements
        $content = str_replace('use oceler\\', 'use App\\', $content);
        
        // Update class references
        $content = str_replace('\\oceler\\', '\\App\\', $content);
        $content = str_replace('\'oceler\\', '\'App\\', $content);
        $content = str_replace('"oceler\\', '"App\\', $content);
        
        file_put_contents($file, $content);
    }
} 