<?php

namespace IsotopeDocRobot\Markdown\Parsers;


use IsotopeDocRobot\Markdown\AfterParserInterface;

class HeadingParser implements AfterParserInterface
{
    /**
     * {@inheritdoc}
     */
    public function parseAfter($data)
    {
        return preg_replace_callback(
            '#<h(\d)>([^</h(\d)]*)</h\d>#U',
            function($matches) {
                $level = $matches[1];
                $content = $matches[2];
                $id = 'deeplink-' . standardize($content);

                return sprintf('<h%s id="%s">%s <a href="%s" title="%s" class="sub_permalink">#</a></h%s>',
                    $level,
                    $id,
                    $content,
                    \Environment::get('request') . '#' . $id,
                    $GLOBALS['TL_LANG']['ISOTOPE_DOCROBOT']['deeplinkLabel'],
                    $level
                );
            },
            $data);
    }
}