<?php

$root = __DIR__ . '/../tests';

$files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root));

foreach ($files as $file) {
    if (!$file->isFile() || $file->getExtension() !== 'php') {
        continue;
    }

    $path = $file->getPathname();
    $contents = file_get_contents($path);

    if (strpos($contents, '/** @test */') === false) {
        continue;
    }

    $updated = preg_replace('/\n\s*\/\*\*\s*@test\s*\*\/\s*\n\s*public function/', "\n    #[\PHPUnit\\Framework\\Attributes\\Test]\n    public function", $contents);

    if ($updated !== null) {
        file_put_contents($path, $updated);
    }
}
