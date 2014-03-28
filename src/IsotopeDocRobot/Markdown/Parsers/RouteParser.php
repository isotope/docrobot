<?php

namespace IsotopeDocRobot\Markdown\Parsers;


use IsotopeDocRobot\Markdown\AfterParserInterface;

class RouteParser extends AbstractParser implements AfterParserInterface
{
    /**
     * {@inheritdoc}
     */
    public function parseAfter($data)
    {
        $routing = $this->routing;
        $pageModel = $this->pageModel;
        $version = $this->version;
        $language = $this->language;

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