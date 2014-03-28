<?php

namespace IsotopeDocRobot\Markdown\Parsers;


use IsotopeDocRobot\Markdown\BeforeParserInterface;

class RootParser extends AbstractParser implements BeforeParserInterface
{
    /**
     * {@inheritdoc}
     */
    public function parseBefore($data)
    {
        return str_replace('<docrobot_root>', 'system/cache/isotope/docrobot-mirror/' . $this->version, $data);
    }
}