<?php
/*
 -------------------------------------------------------------------------
 karastock plugin for GLPI
 Copyright (C) 2021 by the karastock Development Team.

 https://github.com/pluginsGLPI/karastock
 -------------------------------------------------------------------------

 LICENSE

 This file is part of karastock.

 karastock is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 karastock is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with karastock. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

define('PLUGIN_KARASTOCK_VERSION', '1.4');

// Minimal GLPI version, inclusive
define("PLUGIN_KARASTOCK_MIN_GLPI", "10.0");
// Maximum GLPI version, exclusive
define("PLUGIN_KARASTOCK_MAX_GLPI", "10.1");

/**
 * Init hooks of the plugin.
 * REQUIRED
 *
 * @return void
 */
function plugin_init_karastock() {
   global $PLUGIN_HOOKS;

   $PLUGIN_HOOKS['csrf_compliant']['karastock'] = true;

   if (Session::getLoginUserID()) {

      // Profile Rights Management
      Plugin::registerClass('PluginKarastockProfile', array('addtabon' => array('Profile')));
      
      // Init current profile
      $PLUGIN_HOOKS['change_profile']['karastock'] = ['PluginKarastockProfile', 'initProfile'];   
      
      // Register Order class and add menu
      Plugin::registerClass('PluginKarastockMenu');
      $PLUGIN_HOOKS["menu_toadd"]['karastock'] = ['management' => 'PluginKarastockMenu'];

      // Add custom Stylesheet
      $PLUGIN_HOOKS['add_css']['karastock'] = 'karastock.css'; 
   }
}


/**
 * Get the name and the version of the plugin
 * REQUIRED
 *
 * @return array
 */
function plugin_version_karastock() {
   return [
      'name'           => 'Karastock',
      'version'        => PLUGIN_KARASTOCK_VERSION,
      'author'         => '<a href="http://www.phoen-ix.fr">Karhel</a>',
      'license'        => 'GPLV3+',
      'homepage'       => '',
      'requirements'   => [
         'glpi' => [
            'min' => PLUGIN_KARASTOCK_MIN_GLPI,
            'max' => PLUGIN_KARASTOCK_MAX_GLPI,
         ]
      ]
   ];
}

/**
 * Check pre-requisites before install
 * OPTIONNAL, but recommanded
 *
 * @return boolean
 */
function plugin_karastock_check_prerequisites() {

   //Version check is not done by core in GLPI < 9.2 but has to be delegated to core in GLPI >= 9.2.
   $version = preg_replace('/^((\d+\.?)+).*$/', '$1', GLPI_VERSION);
   if (version_compare($version, PLUGIN_KARASTOCK_MIN_GLPI, '<') 
      || (version_compare($version, PLUGIN_KARASTOCK_MAX_GLPI, '>='))) {
      
         echo "This plugin requires GLPI >= " . PLUGIN_KARASTOCK_MIN_GLPI . " and < " . PLUGIN_KARASTOCK_MAX_GLPI;
      return false;
   }
   return true;
}

/**
 * Check configuration process
 *
 * @param boolean $verbose Whether to display message on failure. Defaults to false
 *
 * @return boolean
 */
function plugin_karastock_check_config($verbose = false) {
   if (true) { // Your configuration check
      return true;
   }

   if ($verbose) {
      echo __('Installed / not configured', 'karastock');
   }
   return false;
}
