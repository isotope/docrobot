<?php

namespace IsotopeDocRobot\Markdown\Parsers;

use IsotopeDocRobot\Markdown\BeforeParserInterface;

class NewVersionParser extends AbstractParser implements BeforeParserInterface
{
    /**
     * {@inheritdoc}
     */
    public function parseBefore($data)
    {
        return preg_replace_callback(
            '#<docrobot_new_in_version version="(.*)">(.*)</docrobot_new_in_version>#U',
            function($matches) {

                return sprintf('<docrobot_message type="info"><p><strong>%s</strong></p>%s</docrobot_message>',
                    sprintf($GLOBALS['TL_LANG']['ISOTOPE_DOCROBOT']['newInVersion'], $matches[1]),
                    $matches[2]
                );
            },
            $data);
    }
}