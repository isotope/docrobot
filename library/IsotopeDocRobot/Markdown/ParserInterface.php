<?php

namespace IsotopeDocRobot\Markdown;


interface ParserInterface
{
    /**
     * Parses the data
     *
     * @param string
     *
     * @return string
     */
    public function parse($data);
}