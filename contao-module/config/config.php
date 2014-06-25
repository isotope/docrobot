<?php

/**
 * Back end modules
 */
$GLOBALS['BE_MOD']['system']['isotope_docrobot'] = array
(
    'tables'      => array('tl_iso_docrobot_settings'),
    'icon'        => 'system/modules/isotope_docrobot/assets/isotope.png'
);

/**
 * Front end modules
 */
$GLOBALS['FE_MOD']['miscellaneous']['isotope_docrobot'] = '\IsotopeDocRobot\Module';

/**
 * Maintenance
 */
$GLOBALS['TL_MAINTENANCE'][] = '\IsotopeDocRobot\Maintenance\Update';

/**
 * Hooks
 */
$GLOBALS['TL_HOOKS']['getSearchablePages'][] = array('\IsotopeDocRobot\Search\Indexer', 'addManualPagesToDSI');

/**
 * Parsers
 */
$GLOBALS['ISOTOPE_DOCROBOT']['parsers'] = array(
    new \IsotopeDocRobot\Markdown\Parsers\CurrentVersionParser(),
    new \IsotopeDocRobot\Markdown\Parsers\HeadingParser(),
    new \IsotopeDocRobot\Markdown\Parsers\ImageParser(),
    new \IsotopeDocRobot\Markdown\Parsers\MessageParser(),
    new \IsotopeDocRobot\Markdown\Parsers\NewVersionParser(),
    new \IsotopeDocRobot\Markdown\Parsers\RootParser(),
    new \IsotopeDocRobot\Markdown\Parsers\RouteParser(),
    new \IsotopeDocRobot\Markdown\Parsers\SitemapParser(),
    new \IsotopeDocRobot\Markdown\Parsers\CiconiaParser()
);