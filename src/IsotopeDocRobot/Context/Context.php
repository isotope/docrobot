<?php

namespace IsotopeDocRobot\Context;


class Context
{
    protected $type = '';
    protected $version = null;
    protected $book = null;
    protected $language = null;

    /**
     * @param string type
     */
    public function __construct($type)
    {
        $this->type = $type;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param null $version
     */
    public function setVersion($version)
    {
        $this->version = $version;
    }

    /**
     * @return null
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * @param null $language
     */
    public function setLanguage($language)
    {
        $this->language = $language;
    }

    /**
     * @return null
     */
    public function getLanguage()
    {
        return $this->language;
    }

    /**
     * @param null $book
     */
    public function setBook($book)
    {
        $this->book = $book;
    }

    /**
     * @return null
     */
    public function getBook()
    {
        return $this->book;
    }
} 