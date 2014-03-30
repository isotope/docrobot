<?php

namespace IsotopeDocRobot\Markdown;

use IsotopeDocRobot\Context\Context;
use IsotopeDocRobot\Routing\Routing;

class ParserCollection
{
    private $context = null;
    private $routing = null;
    private $parsers = array();

    public function __construct(Context $context, Routing $routing)
    {
        $this->context = $context;
        $this->routing = $routing;
    }

    public function addParser(ParserInterface $parser)
    {
        // set context
        if ($parser instanceof ContextAwareInterface) {
            $parser->setContext($this->context);
        }

        // set routing
        if ($parser instanceof RoutingAwareInterface) {
            $parser->setRouting($this->routing);
        }

        $this->parsers[] = $parser;
    }

    /**
     * @param array $parsers
     */
    public function setParsers($parsers)
    {
        foreach ($parsers as $parser) {
            $this->addParser($parser);
        }
    }

    /**
     * @return array
     */
    public function getParsers()
    {
        return $this->parsers;
    }
}