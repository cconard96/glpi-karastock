<?php
/**
 * -------------------------------------------------------------------------
 * Karastock plugin for GLPI
 * Copyright (C) 2020 by the Karastock Development Team.
 *
 * https://github.com/pluginsGLPI/Karastock
 * -------------------------------------------------------------------------
 *
 * LICENSE
 *
 * This file is part of Karastock.
 *
 * Karastock is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 * 
 * Karastock is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with Karastock. If not, see <http://www.gnu.org/licenses/>.
 * --------------------------------------------------------------------------
 * 
 * @package   Karastock
 * @author    Karhel Tmarr
 * @copyright Copyright (c) 2021 Karastock plugin team
 * @license   GPLv3+
 *            http://www.gnu.org/licenses/gpl.txt
 * @link      https://github.com/karhel/glpi-karastock
 * @since     2021
 * --------------------------------------------------------------------------
 */

class PluginKarastockMenu extends CommonGLPI {  

    public static $rightname         = 'plugin_karastock_order';

    // --------------------------------------------------------------------
    //  PLUGIN MANAGEMENT - DATABASE INITIALISATION
    // --------------------------------------------------------------------

    /**
     * Install or update PluginKarastockMenu
     *
     * @param Migration $migration Migration instance
     * @param string    $version   Plugin current version
     *
     * @return boolean
     */
    public static function install(Migration $migration, $version)
    {
        // DO NOTHING
    }

     /**
     * Uninstall PluginKarastockMenu
     *
     * @return boolean
     */
    public static function uninstall()
    {
        // DO NOTHING
    }    

    // --------------------------------------------------------------------
    //  GLPI PLUGIN COMMON
    // --------------------------------------------------------------------

    public static function getTypeName($nb = 0) {
        return __("Stock", "karastock");
    }

    //! @copydoc CommonDBTM::getIcon()
    static function getIcon()
    {
        return "fas fa-cubes";
    } 
    
    static function getMenuContent() {
        global $CFG_GLPI;

        $menu = [
            'title' => self::getTypeName(2),
            'page'  => self::getSearchURL(false),
            'icon'  => self::getIcon(),
        ];

        if(PluginKarastockStock::canView()) {
            $menu['options']['stock'] = [
                'title' => PluginKarastockStock::getTypeName(1),
                'page'  => PluginKarastockStock::getSearchURL(false),
                'icon'  => PluginKarastockStock::getIcon(),
            ];

            $menu['options']['stock']['links'] = [];
        }

        if(PluginKarastockOrder::canView()) {
            $menu['options']['orders'] = [
                'title' => PluginKarastockOrder::getTypeName(2),
                'page'  => PluginKarastockOrder::getSearchURL(false),
                'icon'  => PluginKarastockOrder::getIcon(),
            ];

            if (PluginKarastockOrder::canCreate()) {
                $menu['options']['orders']['links'] = [
                   'search' => PluginKarastockOrder::getSearchURL(false),
                   'add'    => PluginKarastockOrder::getFormURL(false)
                ];
            }
        }
        
        if(PluginKarastockHistory::canView()) {
            $menu['options']['history'] = [
                'title' => PluginKarastockHistory::getTypeName(1),
                'page'  => PluginKarastockHistory::getSearchURL(false),
                'icon'  => PluginKarastockHistory::getIcon(),
            ];
            
            $menu['options']['history']['links'] = [];
        }

        return $menu;
    }
}