<?php


namespace IsotopeDocRobot\Service;


use IsotopeDocRobot\Context\Context;
use IsotopeDocRobot\Routing\Route;
use IsotopeDocRobot\Routing\Routing;

class GitHubCachedBookParser
{
    private $cacheRoot = '';
    /* @var GitHubBookParser */
    private $bookParser = null;
    private $bookPath = '';

    public function __construct($cacheRoot, GitHubBookParser $bookParser)
    {
        $this->cacheRoot = $cacheRoot;
        $this->bookParser = $bookParser;

        $this->bookPath = sprintf($cacheRoot . '/%s/%s/%s/%s',
            $this->bookParser->getContext()->getType(),
            $this->bookParser->getContext()->getVersion(),
            $this->bookParser->getContext()->getLanguage(),
            $this->bookParser->getContext()->getBook()
        );

        // Create a cache dir if not exists
        new \Folder($this->bookPath);
    }

    /**
     * @return string
     */
    public function parseAllRoutes()
    {
        $data = '';
        foreach ($this->bookParser->getRouting()->getRoutes() as $route) {
            $this->parseRoute($route);
        }

        return $data;
    }

    /**
     * @return string
     */
    public function parseRoute(Route $route)
    {
        if ($this->isInCache($route->getName())) {
            return $this->getFromCache($route->getName());
        }

        return $this->saveToCache(
            $route->getName(),
            $this->bookParser->parseRoute($route)
        );
    }

    public function purgeCache()
    {
        $folder = new \Folder($this->bookPath);
        $folder->delete();
    }

    /**
     * @return bool
     */
    private function isInCache($routeName)
    {
        return file_exists(TL_ROOT . '/' . $this->bookPath . '/' . $routeName);
    }

    /**
     * @return string
     */
    private function saveToCache($routeName, $data)
    {
        $file = new \File($this->bookPath . '/' . $routeName);
        $file->write($data);
        $file->close();
        return $data;
    }

    /**
     * @return string
     */
    private function getFromCache($routeName)
    {
        return file_get_contents(TL_ROOT . '/' . $this->bookPath . '/' . $routeName);
    }
}