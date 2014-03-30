<?php

namespace IsotopeDocRobot\Markdown\Parsers;


use IsotopeDocRobot\Context\Context;
use IsotopeDocRobot\Markdown\ParserInterface;
use IsotopeDocRobot\Markdown\RoutingAwareInterface;
use IsotopeDocRobot\Routing\Routing;

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
    public function parseMarkdown($data)
    {
        $routing = $this->routing;
        $version = $this->context->getVersion();
        $language = $this->context->getLanguage();

        // @todo find out how to pass this one
        $pageModel = $this->pageModel;

        return preg_replace_callback(
            '#<docrobot_route name="([^"]*)"( path="([^"]*)")?>([^<]*)</docrobot_route>#U',
            function($matches) use ($routing, $pageModel, $version, $language) {

                $route = $routing->getRoute($matches[1]);

                if ($route === null) {
                    return 'Route "' . $matches[1] . '" does not exist. Please fix the documentation on GitHub!';
                }

                return sprintf('<a href="%s">%s</a>',
                    $routing->getHrefForRoute(
                        $route,
                        $pageModel,
                        $version,
                        $language
                    ) . (($matches[3]) ?: ''),
                    $matches[4]
                );
            },
            $data);
    }
}