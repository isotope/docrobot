<?php

namespace IsotopeDocRobot\Markdown\Parsers;

use IsotopeDocRobot\Markdown\ParserInterface;


class NewVersionParser implements ParserInterface
{
    /**
     * {@inheritdoc}
     */
    public function parse($data)
    {
        $replacement = '<div class="new_in_version">New in version $1: $2</div>';
        return preg_replace('/\[new_in_version\:\:(.*)\](.*)/', $replacement, $data);
    }
}