<?php

namespace IsotopeDocRobot\Markdown\Parsers;


use IsotopeDocRobot\Context\Context;
use IsotopeDocRobot\Markdown\ContextAwareInterface;
use IsotopeDocRobot\Markdown\ParserInterface;
use IsotopeDocRobot\Service\GitHubBookParser;

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
    public function register(GitHubBookParser $bookParser)
    {
        $bookParser->register($this, 'before', 'parseMarkdown');
    }

    /**
     * {@inheritdoc}
     */
    public function parseMarkdown($data)
    {
        return str_replace('<docrobot_root>', 'system/cache/isotope/docrobot-mirror/' . $this->context->getVersion(), $data);
    }
}