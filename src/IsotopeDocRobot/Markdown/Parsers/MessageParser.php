<?php

namespace IsotopeDocRobot\Markdown\Parsers;

use IsotopeDocRobot\Markdown\ParserInterface;

class MessageParser extends AbstractParser implements ParserInterface
{
    /**
     * {@inheritdoc}
     */
    public function parseMarkdown($data)
    {
        $replacement = '<div class="notification-box notification-box-$1">$2</div>';
        return preg_replace('#<docrobot_message type="(.*)">(.*)</docrobot_message>#U', $replacement, $data);
    }
}