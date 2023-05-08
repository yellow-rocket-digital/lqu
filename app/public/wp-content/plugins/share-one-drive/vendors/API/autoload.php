<?php

/**
 * @internal
 *
 * @param mixed $className
 */
function shareonedrive_api_php_client_autoload($className)
{
    $classPath = explode('_', $className);
    if ('SODOneDrive' != $classPath[0]) {
        return;
    }
    // Drop 'OneDrive', and maximum class file path depth in this project is 3.
    $classPath = array_slice($classPath, 1, 2);

    $filePath = dirname(__FILE__).'/OneDrive/'.implode('/', $classPath).'.php';
    if (file_exists($filePath)) {
        require_once $filePath;
    }
}

\spl_autoload_register('shareonedrive_api_php_client_autoload');
