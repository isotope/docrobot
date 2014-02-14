<?php

namespace IsotopeDocRobot\Markdown\Parsers;

use IsotopeDocRobot\Markdown\ParserInterface;


class RootParser implements ParserInterface
{
    private $version = null;

    public function __construct($version)
    {
        $this->version = $version;
    }

    /**
     * {@inheritdoc}
     */
    public function parse($data)
    {
        return str_replace('[docrobot_root]', 'system/cache/isotope/docrobot-mirror/' . $this->version, $data);
    }
}