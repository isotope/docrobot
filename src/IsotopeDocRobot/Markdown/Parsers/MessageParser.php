<?php

namespace IsotopeDocRobot\Markdown\Parsers;

use IsotopeDocRobot\Context\Context;
use IsotopeDocRobot\Markdown\ContextAwareInterface;
use IsotopeDocRobot\Markdown\ParserInterface;

class MessageParser implements ParserInterface, ContextAwareInterface
{
    /**
     * @var Context
     */
    private $context = null;

    /**
     * {@inheritdoc}
     */
    public function setContext(Context $context)
    {
        $this->context = $context;
    }

    /**
     * {@inheritdoc}
     */
    public function parseMarkdown($data)
    {
        if ($this->context->getType() === 'html') {
            $replacement = '<div class="notification-box notification-box-$1">$2</div>';
        } else {
            // @todo define replacement for other types
        }

        return preg_replace('#<docrobot_message type="(.*)">(.*)</docrobot_message>#U', $replacement, $data);
    }
}