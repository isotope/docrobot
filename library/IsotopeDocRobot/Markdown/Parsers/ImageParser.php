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
                $fileSize = @getimagesize($imagePath);

                $image = \Image::get($imagePath, $fileSize[0], $fileSize[1], '', null, true);

                if (!$image) {
                    return '###Image not found, please adjust documentation on GitHub!###';
                }

                return sprintf('<img src="%s" alt="%s" %s>',
                    $image,
                    $matches[2],
                    $fileSize[3]
                );
            },
            $data);
    }
}