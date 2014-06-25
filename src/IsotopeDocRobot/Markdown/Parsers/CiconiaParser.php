<?php

namespace IsotopeDocRobot\Markdown\Parsers;

use IsotopeDocRobot\Context\Context;
use IsotopeDocRobot\Markdown\ContextAwareInterface;
use IsotopeDocRobot\Markdown\ParserInterface;
use IsotopeDocRobot\Service\GitHubBookParser;
use Ciconia\Ciconia;

class CiconiaParser implements ParserInterface, ContextAwareInterface
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
        if ($this->context->getType() == 'html') {
            $bookParser->register($this, 'main', 'parseMarkdown');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function parseMarkdown($data)
    {
        // main markdown parsing
        $ciconia = new Ciconia();
        return $ciconia->render($data);
    }
}