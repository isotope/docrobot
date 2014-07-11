<?php

namespace IsotopeDocRobot\Markdown;

use IsotopeDocRobot\Context\Context;

interface ContextAwareInterface
{
    /**
     * Sets the context
     * @param   Context
     */
    public function setContext(Context $context);
}