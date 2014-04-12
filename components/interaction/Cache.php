<?php


namespace components\interaction;


class Cache extends Interaction
{
    private $processed = array();

    private function createFile($key)
    {
        $key     = str_replace('/', '', $key);
        $key     = preg_replace('/[^a-z0-9]/', '_', $key);
        $dirName = $this->cacheDir . DS;
        $maxLen  = strlen($key);
        if ($maxLen > $this->prefixLen) {
            $maxLen = $this->prefixLen;
        }
        for ($i = 0; $i < $maxLen; $i++) {
            $dirName .= $key{$i} . DS;
        }
        if (!is_dir($dirName)) {
            mkdir($dirName, 0775, true);
        }
        return $dirName . $key . '.cache';
    }

    public function isProcessed($key)
    {
        return isset($this->processed[$key]);
    }

    public function load($key)
    {
        $exists   = false;
        $fileName = $this->createFile($key);
        $content  = null;
        if (is_file($fileName) && is_readable($fileName)) {
            $border = time() - $this->lifetime;
            $mtime  = filemtime($fileName);
            if ($mtime < $border) {
                $this->debug(
                    sprintf(
                        'Дата модификации файла %s меньше %s, стираем кеш',
                        date('d.m.Y H:i:s', $mtime),
                        date('d.m.Y H:i:s', $border)
                    ),
                    3
                );
                unlink($fileName);
            } else {
                $exists  = true;
                $content = file_get_contents($fileName);
            }
        }
        $this->debug(
            sprintf(
                'Для ключа %s загрузили %sпустой кеш (%s)',
                $key,
                $content ? 'не' : '',
                $exists ? 'из файла' : 'файла нет'
            ),
            2
        );
        return $content ? $content : null;
    }

    public function save($key, $value)
    {
        $fileName = $this->createFile($key);
        file_put_contents($fileName, $value);
        $this->processed[$key] = '1';
        $this->debug(sprintf('Для ключа %s сохранили кеш в файл', $key), 2);
    }
}