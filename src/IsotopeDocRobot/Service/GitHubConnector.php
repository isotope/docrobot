<?php
/**
 * Created by JetBrains PhpStorm.
 * User: yanickwitschi
 * Date: 6/14/13
 * Time: 10:27 AM
 * To change this template use File | Settings | File Templates.
 */

namespace IsotopeDocRobot\Service;

use IsotopeDocRobot\Context\Context;
use Github\Client;
use Github\HttpClient\CachedHttpClient;

class GitHubConnector
{
    const githubUri = 'https://raw.githubusercontent.com/isotope/docs/{version}/{language}/{book}/';

    private $context = null;
    private $github = null;

    public function __construct(Context $context)
    {
        $this->context = $context;

        $this->github = new Client(
            new CachedHttpClient(array('cache_dir' => TL_ROOT . '/system/cache/isotope/github-api-cache'))
        );

        $this->github->authenticate($GLOBALS['TL_CONFIG']['iso_github_client_id'], $GLOBALS['TL_CONFIG']['iso_github_client_secret'], Client::AUTH_URL_CLIENT_ID);

        $this->createCacheDirIfNotExist();
    }

    public function updateAll()
    {
        $branch = $this->github->getHttpClient()->get('repos/isotope/docs/branches/' . $this->context->getVersion())->getBody();
        $headRef = $branch['commit']['sha'];

        $tree = $this->github->getHttpClient()->get('repos/isotope/docs/git/trees/' . $headRef . '?recursive=1')->getBody();

        foreach ((array) $tree['tree'] as $treeEntry) {
            if ($treeEntry['type'] == 'blob') {
                $this->updateFile($treeEntry['path']);
            }
        }
    }

    public function updateFile($path)
    {
        $bookPath = $this->context->getLanguage() . '/' . $this->context->getBook();

        if (strpos($path, $bookPath) !== false) {
            $path = str_replace($bookPath, '', $path);
            $data = $this->getFile($path);
            $this->cacheMirrorFile($path, $data);
        }
    }

    public function purgeCache()
    {
        // delete the cache
        $folder = new \Folder(sprintf('system/cache/isotope/docrobot-mirror/%s/%s/%s',
            $this->context->getVersion(),
            $this->context->getLanguage(),
            $this->context->getBook())
        );
        $folder->delete();
    }

    private function getFile($versionRelativeUri)
    {
        $url = str_replace(array (
            '{version}',
            '{language}',
            '{book}'
        ), array(
            $this->context->getVersion(),
            $this->context->getLanguage(),
            $this->context->getBook()
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
        $file = new \File(sprintf('system/cache/isotope/docrobot-mirror/%s/%s/%s/',
            $this->context->getVersion(),
            $this->context->getLanguage(),
            $this->context->getBook()
        ) . $relativePath);
        $file->write($data);
        $file->close();
    }

    private function createCacheDirIfNotExist()
    {
        new \Folder(sprintf('system/cache/isotope/docrobot-mirror/%s/%s/%s',
            $this->context->getVersion(),
            $this->context->getLanguage(),
            $this->context->getBook()
        ));
    }
}