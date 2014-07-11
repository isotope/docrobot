<?php

namespace IsotopeDocRobot\Markdown;

use IsotopeDocRobot\Routing\Routing;

interface RoutingAwareInterface
{
    /**
     * Sets the routing
     * @param   Routing
     */
    public function setRouting(Routing $routing);
}