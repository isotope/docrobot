<?php

namespace IsotopeDocRobot\Markdown\Parsers;

use IsotopeDocRobot\Markdown\BeforeParserInterface;

class NewVersionParser implements BeforeParserInterface
{
    /**
     * {@inheritdoc}
     */
    public function parseBefore($data)
    {
        $replacement = sprintf('<docrobot_message type="info"><p><strong>%s</strong></p>$2</docrobot_message>', $GLOBALS['TL_LANG']['ISOTOPE_DOCROBOT']['newInVersion']);
        return preg_replace('#<docrobot_new_in_version version="(.*)">(.*)</docrobot_new_in_version>#', $replacement, $data);
    }
}