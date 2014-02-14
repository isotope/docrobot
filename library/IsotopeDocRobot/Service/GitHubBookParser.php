<?php
/**
 * Created by JetBrains PhpStorm.
 * User: yanickwitschi
 * Date: 6/14/13
 * Time: 10:27 AM
 * To change this template use File | Settings | File Templates.
 */

namespace IsotopeDocRobot\Service;

use IsotopeDocRobot\Markdown\Parsers\MessageParser;
use IsotopeDocRobot\Markdown\Parsers\NewVersionParser;
use IsotopeDocRobot\Markdown\Parsers\RootParser;

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

        $this->createCacheDirIfNotExist();
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

    public function updateFromMirror()
    {
        // delete the cache
        $folder = new \Folder(sprintf('system/cache/isotope/docrobot/%s/%s/%s', $this->version, $this->language, $this->book));
        $folder->delete();

        foreach ($this->routeMap as $route => $path) {

            // do not handle redirect pages
            if ($this->routes[$route]->type != 'regular') {
                continue;
            }

            // for now only supporting the "index.md" file
            $path .= (($path !== '') ? '/' : '') . 'index.md';

            $data = sprintf('%s/system/cache/isotope/docrobot-mirror/%s/%s/%s/%s',
                TL_ROOT,
                $this->version,
                $this->language,
                $this->book,
                $path);

            $data = file_get_contents($data);

            // transform markdown to html
            $optimusPrime = new MarkdownParser($data);
            $optimusPrime->addParser(new NewVersionParser());
            $optimusPrime->addParser(new MessageParser());
            $optimusPrime->addParser(new RootParser($this->version));
            $this->cacheFile($route . '.html', $optimusPrime->parse());
        }
    }

    private function loadConfig()
    {
        $file = new \File(sprintf('system/cache/isotope/docrobot-mirror/%s/%s/%s/config.json', $this->version, $this->language, $this->book));
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
        $root = new \stdClass();
        $root->type = 'regular';
        $this->routeMap['root'] = '';
        $this->routes['root'] = $root;
    }

    private function createCacheDirIfNotExist()
    {
        new \Folder(sprintf('system/cache/isotope/docrobot/%s/%s/%s', $this->version, $this->language, $this->book));
    }

    private function cacheFile($relativePath, $data)
    {
        $file = new \File(sprintf('system/cache/isotope/docrobot/%s/%s/%s/', $this->version, $this->language, $this->book) . $relativePath);
        $file->write($data);
        $file->close();
    }
}