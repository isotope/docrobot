<?php

namespace IsotopeDocRobot\Routing;


class Route
{
    private $name = '';
    private $config = null;
    private $path = '';
    private $children = array();

    public function __construct($name, $config, $path)
    {
        $this->name = $name;
        $this->config = $config;
        $this->path = $path;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getTitle()
    {
        return $this->getConfig()->title;
    }

    public function getConfig()
    {
        return $this->config;
    }

    public function getPath()
    {
        return $this->path;
    }

    public function getAlias()
    {
        return $this->getConfig()->alias ?: $this->getName();
    }

    public function addChild($child)
    {
        $this->children[] = $child;
    }

    public function setChildren($children)
    {
        $this->children = $children;
    }

    public function getChildren()
    {
        return $this->children;
    }

    public function hasChildren()
    {
        return !empty($this->children);
    }
} 