<?php
/**
 * Created by PhpStorm.
 * User: yanickwitschi
 * Date: 14.02.14
 * Time: 17:13
 */

namespace IsotopeDocRobot\Routing;


class Routing
{
    private $routeAliasMap = array();
    private $routes = array();
    private $config = array();

    public function __construct($configPath)
    {
        // Root
        $rootRouteConfig = new \stdClass();
        $rootRouteConfig->type = 'regular';
        $rootRoute = new Route('root', $rootRouteConfig, '', array());
        $this->routes['root'] = $rootRoute;

        $this->loadConfig($configPath);
        $this->generateRouteMap();
    }

    public function getConfig()
    {
        return $this->config;
    }

    public function getRoutes()
    {
        return $this->routes;
    }

    public function getRoute($route)
    {
        return $this->routes[$route];
    }

    public function getRouteAliasMap()
    {
        return $this->routeAliasMap;
    }

    public function getRootRoute()
    {
        return $this->getRoute('root');
    }

    public function getRouteForAlias($alias)
    {
        return $this->getRoute(array_search($alias, $this->routeAliasMap));
    }

    public function getHrefForRoute(Route $route, \PageModel $page, $version)
    {
        // use the alias if there is one
        $alias = ($route->getConfig()->alias) ? $route->getConfig()->alias : $route->getName();

        switch ($route->getConfig()->type) {
            case 'redirect':
                $alias = ($this->getRoute($route->getConfig()->targetRoute)->getAlias()) ? $this->getRoute($route->getConfig()->targetRoute)->getAlias() : $this->getRoute($route->getConfig()->targetRoute)->getName();
            // DO NOT BREAK HERE
            case 'regular':
                $href = \Controller::generateFrontendUrl($page->row(), '/v/' . $version . '/r/' . $alias);
                break;
        }

        return $href;
    }

    public function isSiblingOfOneInTrail(Route $route, $trail)
    {
        foreach ($trail as $trailRouteName) {
            $trailRoute = $this->getRoute($trailRouteName);
            if ($trailRoute->isSiblingOfMine($route)) {
                return true;
            }
        }

        return false;
    }

    private function loadConfig($configPath)
    {
        $file = new \File($configPath);
        $content = $file->getContent();
        $file->close();

        $this->config = json_decode($content);
    }

    private function generateRouteMap($config=false, $relativePath='', $level=0, $trail=array('root'))
    {
        $levelRoutes = array();
        $config = ($config) ? $config : $this->getConfig();
        foreach ($config as $route => $routeConfig) {
            // route path
            $routePath = (($relativePath) ? $relativePath . '/'  : '') . $route;
            $objRoute = new Route($route, $routeConfig, $routePath, $trail);

            $levelRoutes[] = $objRoute;
            $this->routes[$route]           = $objRoute;
            $this->routeAliasMap[$route]    = $objRoute->getAlias();

            // children
            if ($routeConfig->children) {
                $this->generateRouteMap($routeConfig->children, $routePath, ($level + 1), array_merge($trail, array($route)));

                // set children
                foreach ($routeConfig->children as $childroute => $childConfig) {
                    $objRoute->addChild($this->routes[$childroute]);
                }
            }

            // Root child?
            if ($level == 0) {
                $this->getRootRoute()->addChild($objRoute);
            }
        }

        // set siblings
        foreach ($levelRoutes as $route) {
            foreach ($levelRoutes as $rr) {
                if ($route !== $rr) {
                    $route->addSibling($rr);
                }
            }
        }
    }
} 