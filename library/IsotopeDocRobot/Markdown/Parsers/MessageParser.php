<?php

namespace IsotopeDocRobot\Markdown\Parsers;


use IsotopeDocRobot\Markdown\AfterParserInterface;

class MessageParser implements AfterParserInterface
{
    /**
     * {@inheritdoc}
     */
    public function parseAfter($data)
    {
        $replacement = '<div class="notification-box notification-box-$1">$2</div>';
        return preg_replace('#<docrobot_message type="(.*)">(.*)</docrobot_message>#', $replacement, $data);
    }
}