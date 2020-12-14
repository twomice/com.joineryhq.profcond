<?php

use CRM_Profcond_ExtensionUtil as E;
/*
 * Settings metadata file
 */
return [
  'com.joineryhq.profcond' => [
    'group_name' => 'com.joineryhq.profcond',
    'name' => 'com.joineryhq.profcond',
    'type' => 'Array',
    'html_type' => 'textarea',
    'default' => FALSE,
    'add' => '5.0',
    'title' => E::ts('Profile Conditionals'),
    'is_domain' => 0,
    'is_contact' => 0,
    'description' => E::ts('An array of profile conditional settings.'),
    'help_text' => NULL,
  ],
];
