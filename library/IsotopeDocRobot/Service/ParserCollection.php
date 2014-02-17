<?php

namespace IsotopeDocRobot\Service;

use IsotopeDocRobot\Markdown\AfterParserInterface;
use IsotopeDocRobot\Markdown\BeforeParserInterface;

class ParserCollection
{
    private $data;
    private $beforeParsers = array();
    private $afterParsers = array();

    public function setData($data)
    {
        $this->data = $data;
    }

    public function addParser($parser)
    {
        $blnImplemented = false;

        if ($parser instanceof BeforeParserInterface) {
            $this->beforeParsers[] = $parser;
            $blnImplemented = true;
        }

        if ($parser instanceof AfterParserInterface) {
            $this->afterParsers[] = $parser;
            $blnImplemented = true;
        }

        if (!$blnImplemented) {
            throw new \InvalidArgumentException('Must implement either BeforeParserInterface or AfterParserInterface!');
        }
    }

    public function parse()
    {
        foreach ($this->beforeParsers as $parser) {
            $this->data = $parser->parseBefore($this->data);
        }

        $markdownParser = new \dflydev\markdown\MarkdownParser();
        $this->data = $markdownParser->transformMarkdown($this->data);

        foreach ($this->afterParsers as $parser) {
            $this->data = $parser->parseAfter($this->data);
        }

        return $this->data;
    }
}