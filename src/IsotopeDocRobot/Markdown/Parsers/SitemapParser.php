<?php

namespace IsotopeDocRobot\Markdown\Parsers;

use IsotopeDocRobot\Markdown\ParserInterface;

class SitemapParser implements ParserInterface
{
    private $sitemap = '';

    public function __construct($sitemap)
    {
        $this->sitemap = $sitemap;
    }

    /**
     * {@inheritdoc}
     */
    public function parseMarkdown($data)
    {
        return str_replace('<docrobot_sitemap>', $this->sitemap, $data);
    }
}