<?php

/**
 * Add palette to tl_module
 */
$GLOBALS['TL_DCA']['tl_module']['palettes']['isotope_docrobot']    = '{title_legend},name,headline,type;{config_legend},jumpTo,iso_docrobot_book,iso_docrobot_form;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID,space';

$GLOBALS['TL_DCA']['tl_module']['fields']['iso_docrobot_book'] = array
(
    'label'                   => &$GLOBALS['TL_LANG']['tl_module']['iso_docrobot_book'],
    'exclude'                 => true,
    'inputType'               => 'radio',
    'options'                 => trimsplit(',', $GLOBALS['TL_CONFIG']['iso_docrobot_books']),
    'eval'                    => array('mandatory'=>true),
    'sql'                     => "varchar(255) NOT NULL default ''"
);
$GLOBALS['TL_DCA']['tl_module']['fields']['iso_docrobot_form'] = array
(
    'label'                   => &$GLOBALS['TL_LANG']['tl_module']['iso_docrobot_form'],
    'exclude'                 => true,
    'inputType'               => 'radio',
    'foreignKey'              => 'tl_form.title',
    'sql'                     => "int(10) NOT NULL default '0'"
);