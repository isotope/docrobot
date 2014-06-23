<?php

namespace IsotopeDocRobot\Markdown\Parsers;


use IsotopeDocRobot\Context\Context;
use IsotopeDocRobot\Markdown\ContextAwareInterface;
use IsotopeDocRobot\Markdown\ParserInterface;

class RootParser implements ParserInterface, ContextAwareInterface
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
        return str_replace('<docrobot_root>', 'system/cache/isotope/docrobot-mirror/' . $this->context->getVersion(), $data);
    }
}