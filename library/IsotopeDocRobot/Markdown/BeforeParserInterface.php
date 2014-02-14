<?php

namespace IsotopeDocRobot\Markdown;


interface BeforeParserInterface
{
    /**
     * Parses the data before the markdown parser
     * @param   string
     * @return  string
     */
    public function parseBefore($data);
}