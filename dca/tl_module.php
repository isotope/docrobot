<?php

/**
 * Add palette to tl_module
 */
$GLOBALS['TL_DCA']['tl_module']['palettes']['isotope_docrobot']    = '{title_legend},name,headline,type;{config_legend},possible_versions;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID,space';

$GLOBALS['TL_DCA']['tl_module']['fields']['possible_versions'] = array
(
    'label'                   => &$GLOBALS['TL_LANG']['tl_module']['possible_versions'],
    'exclude'                 => true,
    'inputType'               => 'text',
    'eval'                    => array('tl_class'=>'w50'),
    'sql'                     => "varchar(255) NOT NULL default ''"
);