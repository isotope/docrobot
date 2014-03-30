<?php

namespace IsotopeDocRobot\Markdown\Parsers;


use IsotopeDocRobot\Markdown\ParserInterface;

class RootParser implements ParserInterface
{
    /**
     * {@inheritdoc}
     */
    public function parseMarkdown($data)
    {
        return str_replace('<docrobot_root>', 'system/cache/isotope/docrobot-mirror/' . $this->version, $data);
    }
}