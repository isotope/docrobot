<?php


/**
 * Register namespaces
 */
NamespaceClassLoader::add('dflydev', 'system/modules/isotope-docrobot/library');
NamespaceClassLoader::add('IsotopeDocRobot', 'system/modules/isotope-docrobot/library');

/**
 * Register the templates
 */
TemplateLoader::addFiles(array
(
    'mod_isotope_docrobot'               => 'system/modules/isotope-docrobot/templates',
    'be_isotope_docrobot_maintenance'    => 'system/modules/isotope-docrobot/templates'
));