<?php

namespace IsotopeDocRobot\Markdown\Parsers;

use IsotopeDocRobot\Context\Context;
use IsotopeDocRobot\Markdown\ContextAwareInterface;
use IsotopeDocRobot\Markdown\ParserInterface;
use IsotopeDocRobot\Service\GitHubBookParser;

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
    public function register(GitHubBookParser $bookParser)
    {
        if ($this->context->getType() === 'html') {
            $bookParser->register($this, 'before', 'parseMarkdown');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function parseMarkdown($data)
    {
        $replacement = '<div class="notification-box notification-box-$1">$2</div>';
        return preg_replace('#<docrobot_message type="(.*)">(.*)</docrobot_message>#U', $replacement, $data);
    }
}
