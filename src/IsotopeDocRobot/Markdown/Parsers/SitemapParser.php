<?php

namespace IsotopeDocRobot\Markdown\Parsers;

use IsotopeDocRobot\Markdown\ParserInterface;
use IsotopeDocRobot\Markdown\RoutingAwareInterface;
use IsotopeDocRobot\Routing\Routing;
use IsotopeDocRobot\Service\GitHubBookParser;

class SitemapParser implements ParserInterface, RoutingAwareInterface
{
    /**
     * @var Routing
     */
    private $routing = null;

    /**
     * {@inheritdoc}
     */
    public function setRouting(Routing $routing)
    {
        $this->routing = $routing;
    }

    /**
     * {@inheritdoc}
     */
    public function register(GitHubBookParser $bookParser)
    {
        $bookParser->register($this, 'before', 'parseMarkdown');
    }

    /**
     * {@inheritdoc}
     */
    public function parseMarkdown($data)
    {
        return str_replace('<docrobot_sitemap>', $this->routing->generateSitemap(), $data);
    }
}