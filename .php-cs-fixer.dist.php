<?php

$finder = new PhpCsFixer\Finder()
    ->in([
        __DIR__ . '/src',
        __DIR__ . '/tests',
    ])
    ->filter(function (\SplFileInfo $file) {
        $realPath = $file->getRealPath();
        if ($realPath === false) {
            return true;
        }

        return $realPath !== realpath(__DIR__ . '/src/Kernel.php');
    })
;

return (new PhpCsFixer\Config())
    ->setRules([
        '@Symfony' => true,
        // Ensure every file ends with a newline
        'single_blank_line_at_eof' => true,
        // Add: declare(strict_types=1); at the top of PHP files (where applicable)
        'declare_strict_types' => true,
        // Import global classes
        'global_namespace_import' => [
            'import_classes' => true,
        ],
        // Disable Yoda conditions
        'yoda_style' => false,
    ])
    ->setFinder($finder)
;
