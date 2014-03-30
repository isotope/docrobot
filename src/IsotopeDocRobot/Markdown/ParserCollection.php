<?php

namespace IsotopeDocRobot\Markdown;

use IsotopeDocRobot\Context\Context;
use IsotopeDocRobot\Routing\Routing;

class ParserCollection
{
    private $context = null;
    private $routing = null;

    public function __construct(Context $context, Routing $routing)
    {
        $this->context = $context;
        $this->routing = $routing;
    }

    public function addParser(ParserInterface $parser)
    {
        // set context
        if ($parser instanceof ContextAwareInterface) {
            $parser->setContext($this->context);
        }

        // set routing
        if ($parser instanceof RoutingAwareInterface) {
            $parser->setRouting($this->routing);
        }

        $this->parsers[] = $parser;
    }

    public function parse($data)
    {
        foreach ($this->parsers as $parser) {
            $data = $parser->parseMarkdown($data);
        }

        // @todo get rid of this one
        $markdownParser = new \dflydev\markdown\MarkdownParser();
        $data = $markdownParser->transformMarkdown($data);

        return $data;
    }
}