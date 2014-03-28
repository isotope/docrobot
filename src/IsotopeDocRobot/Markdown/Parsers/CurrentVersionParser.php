<?php

namespace IsotopeDocRobot\Markdown\Parsers;

use IsotopeDocRobot\Markdown\ParserInterface;

class CurrentVersionParser extends AbstractParser implements ParserInterface
{
    /**
     * {@inheritdoc}
     */
    public function parseMarkdown($data)
    {
        return str_replace('<docrobot_current_version>', $this->version, $data);
    }
}