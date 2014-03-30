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