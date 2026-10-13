<?php
require 'vendor/autoload.php';

use Illuminate\View\Compilers\BladeCompiler;
use Illuminate\Filesystem\Filesystem;

$compiler = new BladeCompiler(new Filesystem(), sys_get_temp_dir());
$dirs = [
    'resources/views/v4_4_4/admin/Society',
    'resources/views/v4_4_4/admin/Layout',
    'resources/views/v4_4_4/admin/index.blade.php',
];
$errors = 0;
foreach ($dirs as $d) {
    $files = is_dir($d) ? glob("$d/*.blade.php") : [$d];
    foreach ($files as $file) {
        try {
            $compiler->compile($file);
            echo "OK   $file\n";
        } catch (\Throwable $e) {
            $errors++;
            echo "FAIL $file -- " . $e->getMessage() . "\n";
        }
    }
}
echo "---- $errors errors ----\n";
