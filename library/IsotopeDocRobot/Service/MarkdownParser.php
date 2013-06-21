<?php

namespace IsotopeDocRobot\Service;

use IsotopeDocRobot\Markdown\ParserInterface;

class MarkdownParser
{
    private $data;
    private $parsers = array();

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function addParser(ParserInterface $parser)
    {
        $this->parsers[] = $parser;
    }

    public function parse()
    {
        foreach ($this->parsers as $parser) {
            $this->data = $parser->parse($this->data);
        }

        $markdownParser = new \dflydev\markdown\MarkdownParser();
        return $markdownParser->transformMarkdown($this->data);
    }
}