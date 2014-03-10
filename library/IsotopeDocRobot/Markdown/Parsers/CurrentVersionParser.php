<?php

namespace IsotopeDocRobot\Markdown\Parsers;

use IsotopeDocRobot\Markdown\BeforeParserInterface;

class CurrentVersionParser implements BeforeParserInterface
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
        return str_replace('<docrobot_current_version>', $this->version, $data);
    }
}