<?php

namespace IsotopeDocRobot\Markdown\Parsers;


use IsotopeDocRobot\Markdown\BeforeParserInterface;

class IncompleteParser implements BeforeParserInterface
{
    /**
     * {@inheritdoc}
     */
    public function parseBefore($data)
    {
        $replacement = sprintf('<docrobot_message type="error"><p><strong>%s</strong></p><p>%s</p></docrobot_message>',
            $GLOBALS['TL_LANG']['ISOTOPE_DOCROBOT']['incompleteMsgTitle'],
            $GLOBALS['TL_LANG']['ISOTOPE_DOCROBOT']['incompleteMsgBody']);

        return str_replace('<docrobot_incomplete>', $replacement, $data);
    }
}