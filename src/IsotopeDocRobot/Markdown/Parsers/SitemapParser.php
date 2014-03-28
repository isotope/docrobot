<?php

namespace IsotopeDocRobot\Markdown\Parsers;

use IsotopeDocRobot\Markdown\BeforeParserInterface;

class SitemapParser implements BeforeParserInterface
{
    private $sitemap = '';

    public function __construct($sitemap)
    {
        // @todo implement this
        $this->sitemap = $sitemap;
    }

    /**
     * {@inheritdoc}
     */
    public function parseBefore($data)
    {
        return str_replace('<docrobot_sitemap>', $this->sitemap, $data);
    }
}