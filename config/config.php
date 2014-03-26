<?php

/**
 * Back end modules
 */
$GLOBALS['BE_MOD']['system']['isotope_docrobot'] = array
(
    'tables'      => array('tl_iso_docrobot_settings'),
    'icon'        => 'system/modules/isotope-docrobot/assets/isotope.png'
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