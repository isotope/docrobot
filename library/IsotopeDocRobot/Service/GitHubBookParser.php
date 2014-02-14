<?php
/**
 * Created by JetBrains PhpStorm.
 * User: yanickwitschi
 * Date: 6/14/13
 * Time: 10:27 AM
 * To change this template use File | Settings | File Templates.
 */

namespace IsotopeDocRobot\Service;

class GitHubBookParser
{
    private $version = '';
    private $language = '';
    private $book = '';
    private $config = array();
    private $routeMap = array();
    private $routeAliasMap = array();
    private $routes = array();

    public function __construct($version, $language, $book)
    {
        $this->version = $version;
        $this->language = $language;
        $this->book = $book;

        $this->loadConfig();
        $this->generateRouteMap();
    }

    public function getConfig()
    {
        return $this->config;
    }

    public function getRouteMap()
    {
        return $this->routeMap;
    }

    public function getRoutes()
    {
        return $this->routes;
    }

    public function getRouteAliasMap()
    {
        return $this->routeAliasMap;
    }

    private function loadConfig()
    {
        $file = new \File(sprintf('system/cache/isotope/docrobot/%s/%s/%s/config.json', $this->version, $this->language, $this->book));
        $content = $file->getContent();
        $file->close();

        $this->config = json_decode($content);
    }

    private function generateRouteMap($config=false, $relativePath='')
    {
        $config = ($config) ? $config : $this->getConfig();
        foreach ($config as $route => $routeConfig) {
            // alias handling
            if ($routeConfig->alias) {
                $this->routeAliasMap[$route] = $routeConfig->alias;
            }

            // route path
            $routePath = (($relativePath) ? $relativePath . '/'  : '') . $route;

            $this->routeMap[$route] = $routePath;
            $this->routes[$route] = $routeConfig;

            // children
            if ($routeConfig->children) {
                $this->generateRouteMap($routeConfig->children, $routePath);
            }
        }

        // include the root in the map
        $this->routeMap['root'] = '';
    }
}