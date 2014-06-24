<?php
/**
 * Created by PhpStorm.
 * User: yanickwitschi
 * Date: 14.02.14
 * Time: 17:13
 */

namespace IsotopeDocRobot\Routing;


use IsotopeDocRobot\Context\Context;

class Routing
{
    private $context = null;
    private $routeAliasMap = array();
    private $routes = array();
    private $config = array();

    public function __construct(Context $context)
    {
        $this->context = $context;

        // set path
        $configPath = sprintf('system/cache/isotope/docrobot-mirror/%s/%s/%s/config.json',
            $this->context->getVersion(),
            $this->context->getLanguage(),
            $this->context->getBook()
        );

        // Root
        $rootRouteConfig = new \stdClass();
        $rootRouteConfig->type = 'regular';
        $rootRoute = new Route('root', $rootRouteConfig, '', array());
        $this->routes['root'] = $rootRoute;

        if (!$this->loadConfig($configPath)) {
            throw new \InvalidArgumentException('Invalid config path!');
        }

        $this->generateRouteMap();
    }

    public function setRootTitle($title)
    {
        return $this->getRootRoute()->setTitle($title);
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

    public function getHrefForRoute(Route $route, \PageModel $page)
    {
        // use the alias if there is one
        $alias = ($route->getConfig()->alias) ? $route->getConfig()->alias : $route->getName();

        switch ($route->getConfig()->type) {
            case 'redirect':
                $alias = ($this->getRoute($route->getConfig()->targetRoute)->getAlias()) ? $this->getRoute($route->getConfig()->targetRoute)->getAlias() : $this->getRoute($route->getConfig()->targetRoute)->getName();
            // DO NOT BREAK HERE
            case 'regular':
                $strParams = '/v/' . $this->context->getVersion();

                if ($alias !== 'root') {
                    $strParams .= '/r/' . $alias;
                }

                $href = \Controller::generateFrontendUrl($page->row(), $strParams, $this->context->getLanguage());
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
        if (!is_file(TL_ROOT . '/' . $configPath)) {
            return false;
        }

        $file = new \File($configPath);
        $content = $file->getContent();
        $file->close();

        $this->config = json_decode($content);
        return true;
    }

    private function generateRouteMap($config=false, $relativePath='', $level=0, $trail=array('root'))
    {
        $levelRoutes = array();
        $config = ($config) ? $config : $this->getConfig();
        foreach ($config as $route => $routeConfig) {

            // check duplicates
            if (isset($this->routes[$route])) {
                throw new \RuntimeException('Route "'. $route . '" is defined multiple times. Routes have to be unique!');
            }

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