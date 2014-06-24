<?php

namespace IsotopeDocRobot\Markdown\Parsers;


use IsotopeDocRobot\Markdown\ParserInterface;
use IsotopeDocRobot\Service\GitHubBookParser;

class HeadingParser implements ParserInterface
{
    /**
     * {@inheritdoc}
     */
    public function register(GitHubBookParser $bookParser)
    {
        $bookParser->register($this, 'after', 'parseHtml');
    }

    /**
     * {@inheritdoc}
     */
    public function parseHtml($data)
    {
        return preg_replace_callback(
            '/<h([1-6])>(.*)<\\/h[1-6]>/u',
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