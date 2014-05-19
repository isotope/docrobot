<?php
/**
 * Created by JetBrains PhpStorm.
 * User: yanickwitschi
 * Date: 6/14/13
 * Time: 10:27 AM
 * To change this template use File | Settings | File Templates.
 */

namespace IsotopeDocRobot\Service;

class GitHubConnector
{
    const githubUri = 'https://raw.githubusercontent.com/isotope/docs/{version}/{language}/{book}/';

    private $version = '';
    private $language = '';
    private $book = '';
    private $github = null;

    public function __construct($version, $language, $book)
    {
        $this->version = $version;
        $this->language = $language;
        $this->book = $book;

        $helper = new \IsotopeGithubHelper();
        $this->github = $helper->getClient();

        $this->createCacheDirIfNotExist();
    }

    public function updateAll()
    {
        $branch = $this->github->getHttpClient()->get('repos/isotope/docs/branches/' . $this->version)->getContent();
        $headRef = $branch['commit']['sha'];

        $tree = $this->github->getHttpClient()->get('repos/isotope/docs/git/trees/' . $headRef . '?recursive=1')->getContent();

        foreach ((array) $tree['tree'] as $treeEntry) {
            if ($treeEntry['type'] == 'blob') {
                $this->updateFile($treeEntry['path']);
            }
        }
    }

    public function updateFile($path)
    {
        $bookPath = $this->language . '/' . $this->book . '/';

        if (strpos($path, $bookPath) !== false) {
            $path = str_replace($bookPath, '', $path);
            $data = $this->getFile($path);
            $this->cacheMirrorFile($path, $data);
        }
    }

    public function purgeCache()
    {
        // delete the cache
        $folder = new \Folder(sprintf('system/cache/isotope/docrobot-mirror/%s/%s/%s', $this->version, $this->language, $this->book));
        $folder->delete();
    }

    private function getFile($versionRelativeUri)
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
        $req->redirect = true;
        $req->send($url);

        if (!$req->hasError()) {

            return $req->response;
        }

        return false;
    }

    private function cacheMirrorFile($relativePath, $data)
    {
        $file = new \File(sprintf('system/cache/isotope/docrobot-mirror/%s/%s/%s/', $this->version, $this->language, $this->book) . $relativePath);
        $file->write($data);
        $file->close();
    }

    private function createCacheDirIfNotExist()
    {
        new \Folder(sprintf('system/cache/isotope/docrobot-mirror/%s/%s/%s', $this->version, $this->language, $this->book));
    }
}