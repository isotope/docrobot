<?php

namespace IsotopeDocRobot\Markdown\Parsers;


use IsotopeDocRobot\Context\Context;
use IsotopeDocRobot\Markdown\ContextAwareInterface;
use IsotopeDocRobot\Markdown\ParserInterface;

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
    public function parseMarkdown($data)
    {
        if ($this->context->getType() === 'html') {
            $closure = $this->getMarkupForImageClosure();
        } else {
            // @todo define closure for other types
        }

        return preg_replace_callback(
            '#<docrobot_image path="(.*)" alt="(.*)">#U',
            $closure,
            $data);
    }

    private function getMarkupForImageClosure()
    {
        $language = $this->context->getLanguage();
        $book = $this->context->getBook();
        $version = $this->context->getVersion();

        return function($matches) use ($language, $book, $version) {
            $objFile = new \File('system/cache/isotope/docrobot-mirror/' . $version . '/' . $language . '/' . $book . '/' . $matches[1], true);

            // No image found
            if (!$objFile->exists()) {
                return '###Image not found, please adjust documentation on GitHub!###';
            }

            $mode = 'box';
            $strCacheKey = substr(md5('-w' . $objFile->width . '-h' . $objFile->height . '-' . $objFile->path . '-' . $mode . '-' . $objFile->mtime), 0, 8);
            $strCacheName = 'assets/images/' . substr($strCacheKey, -1) . '/' . $objFile->filename . '-' . $strCacheKey . '.' . $objFile->extension;
            $image      = \Image::get($objFile->path, $objFile->width, $objFile->height, $mode, $strCacheName);

            // No resize necessary
            if ($objFile->width <= 680) {
                return sprintf('<img src="%s" alt="%s" width="%s" height="%s">',
                    $image,
                    $matches[2],
                    $objFile->width,
                    $objFile->height
                );
            }

            // Generate thumbnail
            $thumb      = \Image::get($objFile->path, 680, $objFile->height, $mode, null, true);
            $thumbSize  = @getimagesize($thumb);

            return sprintf('<figure class="image_container"><a href="%s" data-lightbox="%s" title="%s"><span class="overlay zoom"></span><img src="%s" alt="%s" %s></a></figure>',
                $image,
                uniqid(),
                $matches[2],
                $thumb,
                $matches[2],
                $thumbSize[3]
            );
        };
    }
}