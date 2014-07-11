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
     * @param string $version
     */
    public function setVersion($version)
    {
        $this->version = $version;
    }

    /**
     * @return string
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * @param string $language
     */
    public function setLanguage($language)
    {
        $this->language = $language;
    }

    /**
     * @return string
     */
    public function getLanguage()
    {
        return $this->language;
    }

    /**
     * @return \PageModel
     */
    public function getJumpToPageForLanguage()
    {
        $arrLanguageSettings = deserialize($GLOBALS['TL_CONFIG']['iso_docrobot_languages'], true);
        foreach($arrLanguageSettings as $arrLanguage) {
            if ($arrLanguage['language'] == $this->getLanguage()) {
                return \PageModel::findByPk($arrLanguage['page']);
            }
        }

        throw new \BadMethodCallException('No page found for this language!');
    }

    /**
     * @param string
     */
    public function setBook($book)
    {
        $this->book = $book;
    }

    /**
     * @return string
     */
    public function getBook()
    {
        return $this->book;
    }
} 