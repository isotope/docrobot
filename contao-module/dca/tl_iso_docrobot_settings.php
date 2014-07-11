<?php

/**
 * System configuration for docrobot
 */
$GLOBALS['TL_DCA']['tl_iso_docrobot_settings'] = array
(

    // Config
    'config' => array
    (
        'dataContainer'               => 'File',
        'closed'                      => true
    ),

    // Palettes
    'palettes' => array
    (
        'default'                     => '{iso_docrobot_legend},iso_docrobot_versions,iso_docrobot_books,iso_docrobot_languages,iso_github_client_id,iso_github_client_secret',
    ),

    // Fields
    'fields' => array
    (
        'iso_docrobot_versions' => array
        (
            'label'         => &$GLOBALS['TL_LANG']['tl_iso_docrobot_settings']['iso_docrobot_versions'],
            'inputType'     => 'text',
            'eval'          => array('tl_class'=>'w50')
        ),
        'iso_docrobot_books' => array
        (
            'label'         => &$GLOBALS['TL_LANG']['tl_iso_docrobot_settings']['iso_docrobot_books'],
            'inputType'     => 'text',
            'eval'          => array('tl_class'=>'w50')
        ),
        'iso_docrobot_languages' => array
        (
            'label'         => &$GLOBALS['TL_LANG']['tl_iso_docrobot_settings']['iso_docrobot_languages'],
            'inputType'     => 'multiColumnWizard',
            'eval'          => array
            (
                'columnFields' => array
                 (
                    'language' => array
                     (
                        'label'         => &$GLOBALS['TL_LANG']['tl_iso_docrobot_settings']['tl_iso_docrobot_settings_language'],
                        'inputType'     => 'text',
                        'eval'          => array('style'=>'width: 150px;')
                     ),
                    'page' => array
                     (
                        'label'         => &$GLOBALS['TL_LANG']['tl_iso_docrobot_settings']['tl_iso_docrobot_settings_page'],
                        'inputType'     => 'pageTree',
                        'eval'          => array('fieldType'=>'radio')
                    ),
                ),
                'tl_class'  => 'clr'
            )
        ),
        'iso_github_client_id' => array
        (
            'label'         => &$GLOBALS['TL_LANG']['tl_iso_docrobot_settings']['iso_github_client_id'],
            'inputType'     => 'text',
            'eval'          => array('tl_class'=>'w50')
        ),
        'iso_github_client_secret' => array
        (
            'label'         => &$GLOBALS['TL_LANG']['tl_iso_docrobot_settings']['iso_github_client_secret'],
            'inputType'     => 'text',
            'eval'          => array('tl_class'=>'w50')
        ),
    )
);