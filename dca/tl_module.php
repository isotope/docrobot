<?php

/**
 * Add palette to tl_module
 */
$GLOBALS['TL_DCA']['tl_module']['palettes']['isotope_docrobot']    = '{title_legend},name,headline,type;{config_legend},iso_docrobot_book;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID,space';

$GLOBALS['TL_DCA']['tl_module']['fields']['iso_docrobot_book'] = array
(
    'label'                   => &$GLOBALS['TL_LANG']['tl_module']['iso_docrobot_book'],
    'exclude'                 => true,
    'inputType'               => 'radio',
    'options'                 => trimsplit(',', $GLOBALS['TL_CONFIG']['iso_docrobot_books']),
    'eval'                    => array('mandatory'=>true),
    'sql'                     => "varchar(255) NOT NULL default ''"
);