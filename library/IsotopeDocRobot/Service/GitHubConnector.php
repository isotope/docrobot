<?php
/**
 * Created by JetBrains PhpStorm.
 * User: yanickwitschi
 * Date: 6/14/13
 * Time: 10:27 AM
 * To change this template use File | Settings | File Templates.
 */

namespace IsotopeDocRobot\Service;


use IsotopeDocRobot\Markdown\Parsers\NewVersionParser;

class GitHubConnector
{
    const githubUri = 'https://raw.github.com/isotope/docs/{version}/{language}/{book}/';

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

        $this->createCacheDirsIfNotExist();
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

    public function refreshConfigurationFile()
    {
        $data = $this->makeRequest('config.json');
        $this->cacheFile('config.json', $data);

        // load the config file
        $this->loadConfig();

        // update route map
        $this->generateRouteMap();
    }

    public function updateAll()
    {
        foreach (array_keys($this->routeMap) as $route) {
            $this->updateRoute($route);
        }
    }

    public function updateRoute($route)
    {
        $path = $this->routeMap[$route];
        $data = $this->makeRequest($path . '/index.md');

        // transform markdown to html
        $optimusPrime = new MarkdownParser($data);
        $optimusPrime->addParser(new NewVersionParser());
        $data = $optimusPrime->parse();

        $this->cacheFile($route . '.html', $data);
    }


    private function makeRequest($versionRelativeUri)
    {
        $url = str_replace(array (
            '{version}',
            '{language}',
            '{book}'
        ), array(
            $this->version,
            $this->language,
            $this->book
        ), self::githubUri) . $versionRelativeUri;

        $req = new \Request();
        $req->send($url);

        if (!$req->hasError()) {

            return $req->response;
        }

        return false;
    }

    private function cacheFile($relativePath, $data)
    {
        $file = new \File(sprintf('system/cache/isotope/docrobot/%s/%s/%s/', $this->version, $this->language, $this->book) . $relativePath);
        $file->write($data);
        $file->close();
    }

    private function createCacheDirsIfNotExist()
    {
        new \Folder(sprintf('system/cache/isotope/docrobot/%s/%s/%s', $this->version, $this->language, $this->book));
    }


    public function loadConfig()
    {
        $file = new \File(sprintf('system/cache/isotope/docrobot/%s/%s/%s/config.json', $this->version, $this->language, $this->book));
        $content = $file->getContent();
        $file->close();

        $this->config = json_decode($content);
    }

    public function generateRouteMap($config=false, $relativePath='')
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