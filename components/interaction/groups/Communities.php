<?php


namespace components\interaction\groups;


use components\interaction\Interaction;

// @TODO: рефакторинг
class Communities extends Interaction
{
    protected $entityType = 'сообществам';

    private $filters = array();

    protected function getCount(\DOMDocument $dom)
    {
        $xpath = new \DOMXPath($dom);
        $list  = $xpath->query('//div[contains(@class,"summary")]');
        return $list->item(0)->textContent;
    }

    public function getList($query, $filters)
    {
        $this->filters = $filters;
        $dom           = new \DOMDocument();
        $data          = array(
            'q' => $query,
            'c' => array(
                'section' => 'communities'
            )
        );
        $this->loadPage($dom, static::URL . '/search?' . http_build_query($data));
        $this->parsePage($dom);
    }

    protected function loadEntity(\DOMDocument $dom, $href)
    {
        $content = $this->transport->doRequest(sprintf('%s%s?act=members', static::URL, $href));
        $this->parseEntity($dom, $href, $content);
        return $content;
    }

    protected function parseEntity(\DOMDocument $dom, $url, $content)
    {
        $this->checkContent($content);
        if (!@$dom->loadHTML($content)) {
            throw new \LogicException('Не удалось распарсить страницу сообщества');
        }
        $link = $dom->getElementById('fv_link')->getAttribute('href');
        preg_match('/[0-9]+/', $link, $matches);
        if (!empty($matches[0])) {
            $groupId     = (int) $matches[0];
            $subscribers = $this->container->get('groups.subscribers');
            $this->debug('Получаем подписчиков для группы ' . $groupId);
            $subscribers->getList($groupId, $this->filters);
        }
    }
} 