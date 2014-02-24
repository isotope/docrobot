<?php

namespace IsotopeDocRobot\Markdown\Parsers;


use IsotopeDocRobot\Markdown\AfterParserInterface;

class ImageParser implements AfterParserInterface
{
    private $language = null;
    private $book = null;
    private $pageModel = null;
    private $version = null;

    public function __construct($language, $book, $pageModel, $version)
    {
        $this->language = $language;
        $this->book = $book;
        $this->pageModel = $pageModel;
        $this->version = $version;
    }

    /**
     * {@inheritdoc}
     */
    public function parseAfter($data)
    {
        $language = $this->language;
        $book = $this->book;
        $pageModel = $this->pageModel;
        $version = $this->version;

        return preg_replace_callback(
            '#<docrobot_image path="(.*)" alt="(.*)">#U',
            function($matches) use ($language, $book, $pageModel, $version) {

                $imagePath = 'system/cache/isotope/docrobot-mirror/' . $version . '/' . $language . '/' . $book . '/' . $matches[1];
                $imageSize = @getimagesize($imagePath);

                $image      = \Image::get($imagePath, $imageSize[0], $imageSize[1], 'box', null, true);
                $thumb      = \Image::get($imagePath, 680, $imageSize[1], 'box', null, true);
                $thumbSize  = @getimagesize($thumb);

                if (!$image) {
                    return '###Image not found, please adjust documentation on GitHub!###';
                }

                return sprintf('<figure class="image_container"><a href="%s" data-lightbox="%s" title="%s"><span class="overlay zoom"></span><img src="%s" alt="%s" %s></a></figure>',
                    $thumb,
                    uniqid(),
                    $matches[2],
                    $image,
                    $matches[2],
                    $thumbSize[3]
                );
            },
            $data);
    }
}