<?php

namespace IsotopeDocRobot\Markdown;

use IsotopeDocRobot\Service\GitHubBookParser;

interface ParserInterface
{
    /**
     * Method to register a parser
     * @param GitHubBookParser $bookParser
     * @return mixed
     */
    public function register(GitHubBookParser $bookParser);
}