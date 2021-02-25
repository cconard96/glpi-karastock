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

function plugin_karastock_classes()
{
    return [
        'Profile',
        'Order',
        'OrderItem',
        'Menu'
    ];
}

/**
 * Plugin install process
 *
 * @return boolean
 */
function plugin_karastock_install() {
   
   $migration = new Migration(PLUGIN_KARASTOCK_VERSION);
   $classesToInstall = plugin_karastock_classes();

   foreach ($classesToInstall as $className) {

       require_once('inc/' . strtolower($className) . '.class.php');

       $fullclassname = 'PluginKarastock' . $className;
       $fullclassname::install($migration, PLUGIN_KARASTOCK_VERSION);
   }

   return true;
}

/**
 * Plugin uninstall process
 *
 * @return boolean
 */
function plugin_karastock_uninstall() {
   $classesToUninstall = plugin_karastock_classes();

   foreach ($classesToUninstall as $className) {

       require_once('inc/' . strtolower($className) . '.class.php');

       $fullclassname = 'PluginKarastock' . $className;
       $fullclassname::uninstall();
   }

   return true;
}
