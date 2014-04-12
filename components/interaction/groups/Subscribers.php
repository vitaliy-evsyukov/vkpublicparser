<?php


namespace components\interaction\groups;

use components\interaction\Interaction;

class Subscribers extends Interaction
{
    protected $entityType = 'подписчикам';

    private $filtersNames = array(
        'position' => array('Место работы:', 'Работа'),
        'politics' => 'Полит. предпочтения:'
    );

    private $filters = array();

    /**
     * @todo: вынести фильтры в классы фильтров-хелперов с установкой значений и получением результата проверки
     * @param       $groupId
     * @param array $filters
     */
    public function getList($groupId, array $filters = array())
    {
        $this->filters = $filters;
        unset($filters['politics']);
        foreach ($this->filters as $filterName => $filterValue) {
            if (!isset($this->filtersNames[$filterName])) {
                $this->debug(sprintf('Удаляем %s из фильтров', $filterName));
                unset($this->filters[$filterName]);
            }
        }

        $data = array(
            'c' => array_merge(
                $filters,
                array(
                    'section' => 'people',
                    'group'   => $groupId,
                )
            )
        );

        $dom = new \DOMDocument();
        $url = static::URL . '/search?' . http_build_query($data);
        $this->loadPage($dom, $url);
        $this->parsePage($dom);
    }

    protected function getCount(\DOMDocument $dom)
    {
        return $dom->getElementById('filter_selector')->getElementsByTagName('span')->item(0)->textContent;
    }

    protected function parseEntity(\DOMDocument $dom, $url, $content)
    {
        if (!@$dom->loadHTML($content)) {
            throw new \LogicException('Не удалось распарсить страницу пользователя');
        }
        $xpath = new \DOMXPath($dom);
        $list  = $xpath->query('//dl[contains(@class,"pinfo_row")]');
        if ($list && $list->length) {
            foreach ($list as $definition) {
                $dNames  = $definition->getElementsByTagName('dt');
                $dValues = $definition->getElementsByTagName('dd');
                foreach ($dNames as $index => $name) {
                    $property = $name->textContent;
                    $vNode    = $dValues->item($index);
                    $value    = $vNode->textContent;
                    $html     = $vNode->ownerDocument->saveHTML($vNode);
                    $html     = strip_tags(preg_replace('#<br\s*/?>#i', PHP_EOL, $html));
                    $valid    = false;
                    foreach ($this->filters as $filterName => $filterValue) {
                        $filters = (array) $this->filtersNames[$filterName];
                        foreach ($filters as $filter) {
                            if ($property === $filter) {
                                $valid = ($filterValue === '') || (stripos($value, $filterValue) !== false);
                            }
                            if ($valid) {
                                break;
                            }
                        }
                    }
                    if ($valid) {
                        printf('%s%s: %s = %s%s', static::URL, $url, $property, $html, PHP_EOL);
                    }
                }
            }
        }
    }

    protected function loadEntity(\DOMDocument $dom, $url)
    {
        $content = $this->transport->doRequest(sprintf('%s%s?act=info', static::URL, $url));
        $this->parseEntity($dom, $url, $content);
        return $content;
    }
} 