<?php

namespace IsotopeDocRobot\Markdown\Parsers;


use IsotopeDocRobot\Context\Context;
use IsotopeDocRobot\Markdown\ContextAwareInterface;
use IsotopeDocRobot\Markdown\ParserInterface;
use IsotopeDocRobot\Markdown\RoutingAwareInterface;
use IsotopeDocRobot\Routing\Routing;
use IsotopeDocRobot\Service\GitHubBookParser;

class RouteParser implements ParserInterface, ContextAwareInterface, RoutingAwareInterface
{
    /**
     * @var Context
     */
    private $context = null;

    /**
     * @var Routing
     */
    private $routing = null;

    /**
     * {@inheritdoc}
     */
    public function setContext(Context $context)
    {
        $this->context = $context;
    }

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
        $routing = $this->routing;

        return preg_replace_callback(
            '#<docrobot_route name="([^"]*)"( path="([^"]*)")?>([^<]*)</docrobot_route>#U',
            function($matches) use ($routing) {

                $route = $routing->getRoute($matches[1]);

                if ($route === null) {
                    return 'Route "' . $matches[1] . '" does not exist. Please fix the documentation on GitHub!';
                }

                return sprintf('<a href="%s">%s</a>',
                    $routing->getHrefForRoute($route) . (($matches[3]) ?: ''),
                    $matches[4]
                );
            },
            $data);
    }
}