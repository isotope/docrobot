<?php

namespace IsotopeDocRobot\Markdown\Parsers;


use IsotopeDocRobot\Markdown\ParserInterface;
use IsotopeDocRobot\Markdown\RoutingAwareInterface;
use IsotopeDocRobot\Routing\Routing;
use IsotopeDocRobot\Service\GitHubBookParser;

class HeadingParser implements ParserInterface, RoutingAwareInterface
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
        $bookParser->register($this, 'after', 'parseHtml');
    }

    /**
     * {@inheritdoc}
     */
    public function parseHtml($data)
    {
        $url = $this->routing->getHrefForRoute($this->routing->getCurrentRoute());

        return preg_replace_callback(
            '/<h([1-6])>(.*)<\\/h[1-6]>/u',
            function($matches) use ($url) {
                $level = $matches[1];
                $content = $matches[2];
                $id = 'deeplink-' . standardize($content);

                return sprintf('<h%s id="%s">%s <a href="%s" title="%s" class="sub_permalink">#</a></h%s>',
                    $level,
                    $id,
                    $content,
                    $url . '#' . $id,
                    $GLOBALS['TL_LANG']['ISOTOPE_DOCROBOT']['deeplinkLabel'],
                    $level
                );
            },
            $data);
    }
}