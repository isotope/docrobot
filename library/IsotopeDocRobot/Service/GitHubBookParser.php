<?php


namespace IsotopeDocRobot\Service;

use IsotopeDocRobot\Routing\Route;

class GitHubBookParser
{
    private $version = '';
    private $language = '';
    private $book = '';
    private $routing = null;
    private $parserCollection = null;
    private $languageBackup = '';

    public function __construct($version, $language, $book, $routing, $parserCollection)
    {
        $this->version = $version;
        $this->language = $language;
        $this->book = $book;
        $this->routing = $routing;
        $this->parserCollection = $parserCollection;
        $this->languageBackup = $GLOBALS['TL_LANGUAGE'];

        $this->createCacheDirIfNotExist();
    }

    public function loadLanguage()
    {
        \System::loadLanguageFile('default', $this->language);
    }

    public function resetLanguage()
    {
        \System::loadLanguageFile('default', $this->languageBackup);
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

            $path = sprintf('%s/system/cache/isotope/docrobot-mirror/%s/%s/%s/%s',
                TL_ROOT,
                $this->version,
                $this->language,
                $this->book,
                $path);

            if (!is_file($path)) {
                continue;
            }

            $this->parserCollection->setData(file_get_contents($path));
            $this->cacheFile($route->getName() . '.html', $this->parserCollection->parse());
        }
    }

    public function getContentForRoute(Route $route)
    {
        $path = sprintf('%s/system/cache/isotope/docrobot/%s/%s/%s/%s.html',
            TL_ROOT,
            $this->version,
            $this->language,
            $this->book,
            $route->getName());

        if (is_file($path)) {
            $strContent = file_get_contents($path);
        } else {
            return '';
        }

        $this->parserCollection->setData($strContent);
        return $this->parserCollection->parse();
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