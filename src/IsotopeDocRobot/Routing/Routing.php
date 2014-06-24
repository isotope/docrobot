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
    /* @var $currentRoute \IsotopeDocRobot\Routing\Route */
    protected $currentRoute = null;

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

    /**
     * @return Route
     */
    public function getRootRoute()
    {
        return $this->getRoute('root');
    }

    public function setCurrentRoute($currentRoute)
    {
        $this->currentRoute = $currentRoute;
    }

    public function getCurrentRoute()
    {
        return $this->currentRoute;
    }

    public function getRouteForAlias($alias)
    {
        return $this->getRoute(array_search($alias, $this->routeAliasMap));
    }

    public function getHrefForRoute(Route $route)
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

                $href = \Controller::generateFrontendUrl($this->context->getJumpToPageForLanguage()->row(), $strParams, $this->context->getLanguage());
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

    public function generateSitemap()
    {
        $currentRoute = $this->getCurrentRoute();
        $this->setCurrentRoute($this->getRootRoute());
        $sitemap = $this->generateNavigation(false, 1, true);
        $this->setCurrentRoute($currentRoute);
        return $sitemap;
    }

    public function generateNavigation($routes=false, $level=1, $blnIsSitemap=false, $blnSkipSubpages=false)
    {
        if ($routes === false) {
            $routes = $this->getRootRoute()->getChildren();
        }

        $objTemplate = new \FrontendTemplate('nav_default');
        $objTemplate->type = get_class($this);
        $objTemplate->level = 'level_' . $level;
        $items = array();

        /**
         * @var $route Route
         */
        foreach ($routes as $route) {

            $blnIsInTrail = in_array($route->getName(), $this->getCurrentRoute()->getTrail());

            if (!$blnIsSitemap) {
                // Only show route if it's one of those
                // - the current route
                // - a route in the trail
                // - a sibling of any route in the trail
                // - a sibling of the current route
                // - a child of the current route
                if (!(
                    $route === $this->getCurrentRoute()
                    || $blnIsInTrail
                    || $this->isSiblingOfOneInTrail($route, $this->getCurrentRoute()->getTrail())
                    || $this->getCurrentRoute()->isSiblingOfMine($route)
                    || $this->getCurrentRoute()->isChildOfMine($route)
                )) {
                    continue;
                }
            }

            // CSS class
            $strClass = ($blnIsInTrail) ? 'trail' : '';
            $strClass .=  ' ' . $route->getConfig()->type;

            // Incomplete
            if ($route->isIncomplete()) {
                $strClass .= ' incomplete';
            }

            // New
            if ($route->isNew()) {
                $strClass .= ' new';
            }
            // children
            $subitems = '';
            if ($route->hasChildren() && !$blnSkipSubpages) {
                $subitems = $this->generateNavigation($route->getChildren(), ($level + 1), $blnIsSitemap);
                $strClass .= ' subnav';
            }

            $row = array();
            $row['isActive']    = ($this->getCurrentRoute()->getName() == $route->getName()) ? true : false;
            $row['subitems']    = $subitems;
            $row['href']        = $this->getHrefForRoute($route);
            $row['title']       = specialchars($route->getTitle(), true);
            $row['pageTitle']   = specialchars($route->getTitle(), true);
            $row['link']        = $route->getTitle();
            $row['class']       = trim($strClass);
            $items[]            = $row;
        }

        // Add classes first and last
        if (!empty($items))
        {
            $last = count($items) - 1;

            $items[0]['class'] = trim($items[0]['class'] . ' first');
            $items[$last]['class'] = trim($items[$last]['class'] . ' last');
        }

        $objTemplate->items = $items;
        return !empty($items) ? $objTemplate->parse() : '';
    }

    public function generateBookNavigation()
    {
        $objCurrent = $this->getRootRoute();

        $arrRoutesByIndex = array_keys($this->getRoutes());
        $intCurrent = array_search($this->getCurrentRoute()->getName(), $arrRoutesByIndex);
        $intLast = count($arrRoutesByIndex) - 1;

        if ($intCurrent === 0) {
            $objNext = $this->getRoute($arrRoutesByIndex[1]);
            $arrRoutes = array($objCurrent, $objNext);
            return $this->generateNavigation($arrRoutes, 1, true, true);
        }

        if ($intCurrent === $intLast) {
            $objNext = $this->getRootRoute();
        } else {
            $objNext = $this->getRoute($arrRoutesByIndex[$intCurrent + 1]);
        }

        $objPrevious = $this->getRoute($arrRoutesByIndex[$intCurrent - 1]);
        $objCurrent = $this->getRoute($arrRoutesByIndex[$intCurrent]);
        $arrRoutes = array($objPrevious, $objCurrent, $objNext);
        return $this->generateNavigation($arrRoutes, 1, true, true);
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