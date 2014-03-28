<?php

namespace IsotopeDocRobot\Markdown\Parsers;


use IsotopeDocRobot\Markdown\ParserInterface;

class ImageParser extends AbstractParser implements ParserInterface
{
    /**
     * {@inheritdoc}
     */
    public function parseMarkdown($data)
    {
        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function parseHtml($data)
    {
        return preg_replace_callback(
            '#<docrobot_image path="(.*)" alt="(.*)">#U',
            $this->getMarkupForImageClosure(),
            $data);
    }

    private function getMarkupForImageClosure()
    {
        $language = $this->language;
        $book = $this->book;
        $pageModel = $this->pageModel;
        $version = $this->version;

        return function($matches) use ($language, $book, $pageModel, $version) {

            $imagePath = 'system/cache/isotope/docrobot-mirror/' . $version . '/' . $language . '/' . $book . '/' . $matches[1];
            $imageSize = @getimagesize($imagePath);

            $image      = \Image::get($imagePath, $imageSize[0], $imageSize[1], 'box', null, true);

            // No image found
            if (!$image) {
                return '###Image not found, please adjust documentation on GitHub!###';
            }

            // No resize necessary
            if ($imageSize[0] <= 680) {
                return sprintf('<img src="%s" alt="%s" %s>',
                    $image,
                    $matches[2],
                    $imageSize[3]
                );
            }

            // Generate thumbnail
            $thumb      = \Image::get($imagePath, 680, $imageSize[1], 'box', null, true);
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