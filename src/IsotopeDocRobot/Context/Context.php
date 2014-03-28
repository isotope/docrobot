<?php

namespace IsotopeDocRobot\Context;


class Context
{
    protected $routing = null;
    protected $version = null;
    protected $book = null;
    protected $language = null;
    protected $pageModel = null;

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
     * @param \IsotopeDocRobot\Markdown\Parsers\Routing|null $routing
     */
    public function setRouting($routing)
    {
        $this->routing = $routing;
    }

    /**
     * @return \IsotopeDocRobot\Markdown\Parsers\Routing|null
     */
    public function getRouting()
    {
        return $this->routing;
    }

    /**
     * @param null|\PageModel $pageModel
     */
    public function setPageModel($pageModel)
    {
        $this->pageModel = $pageModel;
    }

    /**
     * @return null|\PageModel
     */
    public function getPageModel()
    {
        return $this->pageModel;
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