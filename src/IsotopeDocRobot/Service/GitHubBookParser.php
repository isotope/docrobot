<?php


namespace IsotopeDocRobot\Service;

use IsotopeDocRobot\Routing\Route;

class GitHubBookParser
{
    private $data = '';
    private $parserCollection = null;
    private $languageBackup = '';

    public function __construct($data, $parserCollection)
    {
        $this->data = $data;
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
        $this->loadLanguage();

        // delete the cache
        $folder = new \Folder(sprintf('system/cache/isotope/docrobot/%s/%s/%s', $this->version, $this->language, $this->book));
        $folder->delete();

        /* @var $route \IsotopeDocRobot\Routing\Route */
        foreach ($this->routing->getRoutes() as $route) {

            // do not handle redirect pages
            if ($route->getConfig()->type != 'regular') {
                continue;
            }

            $this->cacheFile(
                $route->getName() . '.html',
                $this->parserCollection->parse(
                    $route->getContent(

                    )
                )
            );
        }

        $this->resetLanguage();
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