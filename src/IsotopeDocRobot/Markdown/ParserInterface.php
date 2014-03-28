<?php

namespace IsotopeDocRobot\Markdown;

interface ParserInterface
{
    /**
     * Parses the data when it's Markdown
     * @param   string
     * @return  string
     */
    public function parseMarkdown($data);
}