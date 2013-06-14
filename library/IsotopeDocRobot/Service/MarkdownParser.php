<?php

namespace IsotopeDocRobot\Service;

class MarkdownParser
{
    private $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function parse()
    {
        $markdownParser = new \dflydev\markdown\MarkdownParser();
        $this->parseNewInVersion();
        return $markdownParser->transformMarkdown($this->data);
    }

    private function parseNewInVersion()
    {
        $replacement = '<div class="new_in_version">New in version $1: $2</div>';
        $this->data = preg_replace('/\[new_in_version\:\:(.*)\](.*)/', $replacement, $this->data);
    }
}