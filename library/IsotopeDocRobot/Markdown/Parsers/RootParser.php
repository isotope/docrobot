<?php

namespace IsotopeDocRobot\Markdown\Parsers;


use IsotopeDocRobot\Markdown\BeforeParserInterface;

class RootParser implements BeforeParserInterface
{
    private $version = null;

    public function __construct($version)
    {
        $this->version = $version;
    }

    /**
     * {@inheritdoc}
     */
    public function parseBefore($data)
    {
        return str_replace('<docrobot_root>', 'system/cache/isotope/docrobot-mirror/' . $this->version, $data);
    }
}