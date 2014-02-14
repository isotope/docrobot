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

class GitHubConnector
{
    const githubUri = 'https://raw.github.com/isotope/docs/{version}/{language}/{book}/';

    private $version = '';
    private $language = '';
    private $book = '';
    private $config = array();
    private $routeMap = array();

    public function __construct($version, $language, $book)
    {
        $this->version = $version;
        $this->language = $language;
        $this->book = $book;

        $this->createCacheDirsIfNotExist();
    }

    public function refreshConfigurationFile()
    {
        $data = $this->makeRequest('config.json');
        $this->cacheFile('config.json', $data);
    }

    public function updateAll()
    {
        // https://api.github.com/repos/isotope/docs/branches/2.0
        // https://api.github.com/repos/isotope/docs/git/trees/9e1d068472c02ea9ce0c0ea0e5286d3cb7872e9c?recursive=1
        foreach (array_keys($this->routeMap) as $route) {
            $this->updateRoute($route);
        }
    }

    public function updateFile($path)
    {
        $path = $this->routeMap[$route];
        $data = $this->makeRequest($path);

        // transform markdown to html
        $optimusPrime = new MarkdownParser($data);
        $optimusPrime->addParser(new NewVersionParser());
        $optimusPrime->addParser(new MessageParser());
        $optimusPrime->addParser(new RootParser($this->version));
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

    private function cacheMirrorFile($relativePath, $data)
    {
        $file = new \File(sprintf('system/cache/isotope/docrobot-mirror/%s/%s/%s/', $this->version, $this->language, $this->book) . $relativePath);
        $file->write($data);
        $file->close();
    }

    private function createCacheDirsIfNotExist()
    {
        new \Folder(sprintf('system/cache/isotope/docrobot/%s/%s/%s', $this->version, $this->language, $this->book));
        new \Folder(sprintf('system/cache/isotope/docrobot-mirror/%s/%s/%s', $this->version, $this->language, $this->book));
    }
}