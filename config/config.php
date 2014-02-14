<?php

/**
 * Front end modules
 */
$GLOBALS['FE_MOD']['miscellaneous']['isotope_docrobot'] = '\IsotopeDocRobot\Module';

$GLOBALS['TL_MAINTENANCE'][] = '\IsotopeDocRobot\Maintenance\Update';

/**
 * Versions, languages and books
 */
$GLOBALS['ISOTOPE_DOCROBOT_VERSIONS']   = array('2.0', '1.4');
$GLOBALS['ISOTOPE_DOCROBOT_LANGUAGES']  = array('de');
$GLOBALS['ISOTOPE_DOCROBOT_BOOKS']      = array('manual');