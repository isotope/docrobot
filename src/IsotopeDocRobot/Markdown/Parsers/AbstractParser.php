<?php

namespace IsotopeDocRobot\Markdown\Parsers;

use IsotopeDocRobot\Context\Context;

class AbstractParser
{
    /**
     * @var \IsotopeDocRobot\Context\Context;
     */
    protected $context = null;

    /**
     * @param \IsotopeDocRobot\Context\Context $context
     */
    public function __construct(Context $context)
    {
        $this->context = $context;
    }

    /**
     * @return \IsotopeDocRobot\Context\Context
     */
    public function getContext()
    {
        return $this->context;
    }
}