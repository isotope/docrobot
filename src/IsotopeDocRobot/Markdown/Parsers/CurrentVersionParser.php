<?php

namespace IsotopeDocRobot\Markdown\Parsers;

use IsotopeDocRobot\Context\Context;
use IsotopeDocRobot\Markdown\ContextAwareInterface;
use IsotopeDocRobot\Markdown\ParserInterface;

class CurrentVersionParser implements ParserInterface, ContextAwareInterface
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
        return str_replace('<docrobot_current_version>', $this->context->getVersion(), $data);
    }
}