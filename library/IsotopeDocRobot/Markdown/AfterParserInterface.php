<?php

namespace IsotopeDocRobot\Markdown;


interface AfterParserInterface
{
    /**
     * Parses the data after the markdown parser
     * @param   string
     * @return  string
     */
    public function parseAfter($data);
}