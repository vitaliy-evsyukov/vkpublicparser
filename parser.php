<?php

define('DS', DIRECTORY_SEPARATOR);

try {
    spl_autoload_register(
        function ($className) {
            $fileName = __DIR__ . DS . str_replace('\\', DS, $className) . '.php';
            if (!is_file($fileName) || !is_readable($fileName)) {
                throw new \Exception(sprintf('Для класса %s не удалось прочитать файл %s', $className, $fileName));
            }
            require_once $fileName;
        }
    );
    $config = new \components\system\Config();
    $config->setFileName(__DIR__ . DS . 'config.php');
    $application = new \components\system\Application($config);
    $application->get('login')->login();
    $longopts  = array(
        "group::",
        "politics::",
        "position::",
    );
    $options   = getopt('', $longopts);
    $filters   = array();
    $hasFilter = false;
    foreach ($longopts as $opt) {
        $opt           = str_replace(':', '', $opt);
        $filters[$opt] = isset($options[$opt]) ? (string) $options[$opt] : null;
        if (!is_null($filters[$opt])) {
            $hasFilter = true;
        }
    }
    if (!$hasFilter) {
        throw new Exception('Нет ни одного фильтра. Доступные фильтры: ' . implode(', ', array_keys($filters)));
    }
    $groupId = isset($options['group']) ? $options['group'] : 0;
    if (!is_numeric($groupId)) {
        $service = 'groups.communities';
    } else {
        $service = 'groups.subscribers';
    }
    $application->get($service)->getList($groupId, $filters);
} catch (\Exception $e) {
    die($e->getMessage() . PHP_EOL);
}