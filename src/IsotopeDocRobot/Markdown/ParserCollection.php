<?php

namespace IsotopeDocRobot\Markdown;

use IsotopeDocRobot\Context\Context;

class ParserCollection
{
    private $context = null;

    public function __construct(Context $context)
    {
        $this->context = $context;
    }

    public function addParser(ParserInterface $parser)
    {
        // set context
        $parser->setContext($this->context);

        $this->parsers[] = $parser;
    }

    public function parse($data)
    {
        foreach ($this->parsers as $parser) {
            $data = $parser->parseMarkdown($data);
        }

        $markdownParser = new \dflydev\markdown\MarkdownParser();
        $data = $markdownParser->transformMarkdown($data);

        foreach ($this->parsers as $parser) {
            $data = $parser->parseHtml($data);
        }

        return $data;
    }
}