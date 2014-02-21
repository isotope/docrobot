<?php

/**
 * Front end modules
 */
$GLOBALS['FE_MOD']['miscellaneous']['isotope_docrobot'] = '\IsotopeDocRobot\Module';

$GLOBALS['TL_MAINTENANCE'][] = '\IsotopeDocRobot\Maintenance\Update';

/**
 * Hooks
 */
$GLOBALS['TL_HOOKS']['dsi_searchablePages'][] = array('\IsotopeDocRobot\Search\Indexer', 'addManualPagesToDSI');

/**
 * Versions, languages and books
 */
$GLOBALS['ISOTOPE_DOCROBOT_VERSIONS']   = array('2.0');

// language -> page id mapper (there might be languages in the future that are probably on English page)
$GLOBALS['ISOTOPE_DOCROBOT_LANGUAGES']  = array(
    'de'    => 23,
    //'en'    => 22
);
$GLOBALS['ISOTOPE_DOCROBOT_BOOKS']      = array('manual');