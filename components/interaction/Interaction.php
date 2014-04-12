<?php


namespace components\interaction;


use components\Component;
use components\system\Application;
use components\system\Config;

abstract class Interaction extends Component
{
    const URL = 'http://m.vk.com';

    protected static $debugCounter = 0;

    protected $entityType;

    protected $cacheSuffix;

    protected $timeout;

    public function __construct(Config $config = null)
    {
        parent::__construct($config);
        $this->cacheSuffix = '_' . str_replace('\\', '_', strtolower(get_class($this)));
    }

    public function setApplication(Application $app)
    {
        parent::setApplication($app);
        if (!$this->timeout) {
            $this->timeout = max($this->application->getParameter('maxTimeout'), 2);
        }
    }

    protected function debug($message, $level = 1)
    {
        if ($level <= $this->application->getParameter('verbosity')) {
            $message = str_repeat(' ', ($level - 1) * 2) . $message;
            if (!(++static::$debugCounter % 50)) {
                $message .= sprintf(
                    '%sПотребление памяти: %s, время работы: %.2f секунд',
                    PHP_EOL,
                    $this->getMemoryUsage(),
                    microtime(true) - $_SERVER['REQUEST_TIME_FLOAT']
                );
            }
            $this->output($message, STDERR);
        }
    }

    protected function result($message)
    {
        $this->output($message, STDOUT);
    }

    protected function output($message, $handle)
    {
        fwrite($handle, $message . PHP_EOL);
    }

    protected function getCount(\DOMDocument $dom)
    {
        return '';
    }

    protected function showPager($dom, $url)
    {
        $query = parse_url($url, PHP_URL_QUERY);
        parse_str($query, $queryParts);
        if (isset($queryParts['offset'])) {
            $offset = $queryParts['offset'];
        } else {
            $offset = 'с начала';
        }
        $data  = $this->getCount($dom);
        $count = (int) preg_replace('/[^0-9]/', '', $data);
        if (!$count) {
            $count = 'всем';
        }
        $this->debug(sprintf('Начинаем итерацию по %s %s, смещение %s', $count, $this->entityType, $offset));
    }

    protected function loadPage(\DOMDocument $dom, $url)
    {
        if (!@$dom->loadHTML($this->transport->doRequest($url))) {
            throw new \LogicException('Не удалось распарсить ответ сервиса');
        }
        $this->showPager($dom, $url);
    }

    protected function loadEntity(\DOMDocument $dom, $href)
    {
        return array();
    }

    protected function parseEntity(\DOMDocument $dom, $href, $data)
    {
        return true;
    }

    protected function parsePage(\DOMDocument $dom)
    {
        $cache = $this->cache;
        while (true) {
            $xpath   = new \DOMXPath($dom);
            $list    = $xpath->query('//div[contains(@class,"results")]');
            $process = false;
            if ($list && $list->length) {
                $list = $list->item(0);
                foreach ($list->getElementsByTagName('a') as $el) {
                    $href = $el->getAttribute('href');
                    if (strpos($href, '/search') === 0) {
                        $process = static::URL . $href;
                    } else {
                        $cacheKey = $href . $this->cacheSuffix;
                        if (!$cache->isProcessed($cacheKey)) {
                            $data = $cache->load($cacheKey);
                            if (!$data) {
                                $time = rand(1, $this->timeout);
                                $this->debug(sprintf('Засыпаем на %d секунд и грузим %s', $time, $href), 3);
                                sleep($time);
                            } else {
                                $this->parseEntity($dom, $href, $data);
                            }
                            // всегда сохраняем в кеш - продлевает время жизни и устанавливает флаг процессинга
                            $cache->save($cacheKey, $this->loadEntity($dom, $href));
                        } else {
                            $this->debug('Уже обработали ' . $href);
                        }
                    }
                }
            }
            if (!$process) {
                break;
            } else {
                $time = rand(1, $this->timeout);
                $this->debug(sprintf('Засыпаем на %d секунд и грузим %s', $time, $process), 2);
                sleep($time);
                $this->loadPage($dom, $process);
            }
        }
    }

    protected function checkContent($content) {
        if (preg_match('/Вы попытались загрузить более одной однотипной страницы/', $content)) {
            throw new \Exception('Получен БАН');
        }
    }

    private function getMemoryUsage()
    {
        $memUsage = memory_get_peak_usage(true);
        $base     = log($memUsage, 1024);
        $data     = array('', 'KB', 'MB', 'GB', 'TB');
        $suffix   = $data[(int) floor($base)];
        return sprintf(
            '%.2f %s',
            pow(1024, $base - floor($base)),
            $suffix
        );
    }
}