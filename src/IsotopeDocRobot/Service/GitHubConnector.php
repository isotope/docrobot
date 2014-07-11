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
        $branch = $this->github->getHttpClient()->get('repos/isotope/docs/branches/' . $this->context->getVersion())->json();
        $headRef = $branch['commit']['sha'];

        $tree = $this->github->getHttpClient()->get('repos/isotope/docs/git/trees/' . $headRef . '?recursive=1')->json();

        $validPath = $this->context->getLanguage() . '/' . $this->context->getBook() . '/';
        $validPathLength = strlen($validPath);

        foreach ((array) $tree['tree'] as $treeEntry) {
            // Only update blobs and if in current path
            $bookPath = substr($treeEntry['path'], 0, $validPathLength);
            $relativePath = substr($treeEntry['path'], $validPathLength);

            if ($treeEntry['type'] == 'blob' && $bookPath === $validPath) {
                $this->updateFile($relativePath);
            }
        }
    }

    /**
     * Updates a certain file. Path must be relative to version, language and book!
     * @param $path
     */
    public function updateFile($path)
    {
        $data = $this->getFile($path);
        $this->cacheMirrorFile($path, $data);
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

    private function getFile($path)
    {
        $url = sprintf('repos/isotope/docs/contents/%s/%s/%s?ref=%s',
            $this->context->getLanguage(),
            $this->context->getBook(),
            $path,
            $this->context->getVersion() // branch as ref
        );

        try {
            $json = $this->github->getHttpClient()->get($url)->json();
            if ($json['encoding'] == 'base64') {
                return base64_decode($json['content']);
            } else {
                return '';
            }
        } catch (\Exception $e) {
            return '';
        }
    }

    private function cacheMirrorFile($path, $data)
    {
        $file = new \File(sprintf('system/cache/isotope/docrobot-mirror/%s/%s/%s/',
                $this->context->getVersion(),
                $this->context->getLanguage(),
                $this->context->getBook()
            ) . $path);
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