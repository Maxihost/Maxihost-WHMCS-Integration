<?php

spl_autoload_register(function ($className) {
    $className = ltrim($className, '\\');
    $fileName = '';
    $namespace = '';
    if ($lastNsPos = strripos($className, '\\')) {
        $namespace = substr($className, 0, $lastNsPos);
        $className = substr($className, $lastNsPos + 1);
        $fileName = str_replace('\\', DIRECTORY_SEPARATOR, $namespace) . DIRECTORY_SEPARATOR;
    }
    $dir = __DIR__ . DIRECTORY_SEPARATOR . $fileName . $className . '.php';
    $dirlowercase = __DIR__ . DIRECTORY_SEPARATOR . strtolower($fileName) . $className . '.php';

    if (file_exists($dir)) {
        require $dir;
        return true;
    }
    elseif(file_exists($dirlowercase)) {
        require $dirlowercase;
        return true;
    }
    return false;
});
