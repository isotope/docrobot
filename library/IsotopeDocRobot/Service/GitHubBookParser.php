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
use IsotopeDocRobot\Markdown\Parsers\RouteParser;

class GitHubBookParser
{
    private $version = '';
    private $language = '';
    private $book = '';
    private $routing = null;

    public function __construct($version, $language, $book, $routing)
    {
        $this->version = $version;
        $this->language = $language;
        $this->book = $book;
        $this->routing = $routing;

        $this->createCacheDirIfNotExist();
    }

    public function updateFromMirror()
    {
        // delete the cache
        $folder = new \Folder(sprintf('system/cache/isotope/docrobot/%s/%s/%s', $this->version, $this->language, $this->book));
        $folder->delete();

        foreach ($this->routing->getRoutes() as $route) {

            // do not handle redirect pages
            if ($route->getConfig()->type != 'regular') {
                continue;
            }

            // for now only supporting the "index.md" file
            $path = $route->getPath();
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
            $optimusPrime->addParser(new RouteParser($this->routing));
            $this->cacheFile($route->getName() . '.html', $optimusPrime->parse());
        }
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