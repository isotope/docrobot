<?php

namespace IsotopeDocRobot\Markdown\Parsers;


use IsotopeDocRobot\Context\Context;
use IsotopeDocRobot\Markdown\ContextAwareInterface;
use IsotopeDocRobot\Markdown\ParserInterface;
use IsotopeDocRobot\Service\GitHubBookParser;

class ImageParser implements ParserInterface, ContextAwareInterface
{
    /**
     * @var Context
     */
    private $context = null;

    /**
     * {@inheritdoc}
     */
    public function setContext(Context $context)
    {
        $this->context = $context;
    }

    /**
     * {@inheritdoc}
     */
    public function register(GitHubBookParser $bookParser)
    {
        if ($this->context->getType() === 'html') {
            $bookParser->register($this, 'before', 'parseMarkdown');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function parseMarkdown($data)
    {
        $type = $this->context->getType();
        $language = $this->context->getLanguage();
        $book = $this->context->getBook();
        $version = $this->context->getVersion();

        return preg_replace_callback(
            '#<docrobot_image path="(.*)" alt="(.*)">#U',
            function($matches) use ($type, $language, $book, $version) {
                $objFile = new \File('system/cache/isotope/docrobot-mirror/' . $version . '/' . $language . '/' . $book . '/' . $matches[1], true);

                // No image found
                if (!$objFile->exists()) {
                    return '###Image not found, please adjust documentation on GitHub!###';
                }

                $bookImagesPath = 'system/cache/isotope/docrobot/' . $type . '/' . $version . '/' . $language . '/' . $book . '/images';
                $this->ensureImagesFolder($bookImagesPath);

                $mode = 'box';
                $strCacheKey = substr(md5('-w' . $objFile->width . '-h' . $objFile->height . '-' . $objFile->path . '-' . $mode . '-' . $objFile->mtime), 0, 8);
                $strCacheName = $bookImagesPath . '/' . $objFile->filename . '-' . $strCacheKey . '.' . $objFile->extension;
                $image = \Image::get($objFile->path, $objFile->width, $objFile->height, $mode, $strCacheName);

                $blockStart = "\n<div>";
                $blockEnd = "</div>\n";

                // No resize necessary
                if ($objFile->width <= 680) {
                    return sprintf('%s<img src="%s" alt="%s" width="%s" height="%s">%s',
                        $blockStart,
                        $image,
                        $matches[2],
                        $objFile->width,
                        $objFile->height,
                        $blockEnd
                    );
                }

                // Generate thumbnail
                $thumb      = \Image::get($objFile->path, 680, $objFile->height, $mode, null, true);
                $thumbSize  = @getimagesize($thumb);

                // Needed because the Automator regularly deletes our thumbnails and thus we use an InsertTag here.
                // This makes sure that we cache the InsertTag (instead of a path to assets/images) which will always
                // get replaced.
                $thumbInsertTag = '{{image::' . $objFile->path . '?width=680&height=' . $objFile->height .'&mode=' . $mode . '&alt=' . $matches[2] . '}}';

                return sprintf('%s<figure class="image_container"><a href="%s" data-lightbox="%s" title="%s"><span class="overlay zoom"></span>%s</a></figure>%s',
                    $blockStart,
                    $image,
                    uniqid(),
                    $matches[2],
                    $thumbInsertTag,
                    $blockEnd
                );
            },
            $data);
    }

    private function ensureImagesFolder($bookImagesPath)
    {
        new \Folder($bookImagesPath);
        $objFile = new \File($bookImagesPath . '/.htaccess', true);
        if ($objFile->exists()) {
            return;
        }

        $objFile->write("<IfModule !mod_authz_core.c>\n  Order allow,deny\n  Allow from all\n</IfModule>\n<IfModule mod_authz_core.c>\n  Require all granted\n</IfModule>");
        $objFile->close();
    }
}