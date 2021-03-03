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

class PluginKarastockHistory extends CommonDBTM {  

    public static $rightname         = 'plugin_karastock_history';

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
        return __("History", "karastock");
    }

    //! @copydoc CommonDBTM::getIcon()
    static function getIcon()
    {
        return "fas fa-history";
    }

    static function show($params = []) {
        global $DB;  
        
        $year = date("Y")-1;

        $date1 = array_key_exists('date1', $params) 
            ? $params['date1'] 
            : date("Y-m-d", mktime(1, 0, 0, (int)date("m"), (int)date("d"), $year));

        $date2 = array_key_exists('date2', $params) 
            ? $params['date2'] 
            : date("Y-m-d");

        //$supplier = array_key_exists('date1', $params) ? $params['date1'] : '';

        self::showSearchForm($date1, $date2);
    
        $query = "SELECT oi.*, o.name as 'ordername', o.suppliers_id
            FROM glpi_plugin_karastock_orderitems as oi
            INNER JOIN glpi_plugin_karastock_orders as o
                ON o.id = oi.plugin_karastock_orders_id
            WHERE oi.out_of_stock_at >= '$date1'
            AND oi.out_of_stock_at <= '$date2'";
        
        $result = $DB->query($query);
        
        echo "<div class='center'>";
        echo "<table class='tab_cadre_fixehov'>";
        echo "<tr><th colspan='7' class='center'>" . __("Stock management", "karastock") . "</th></tr>";

        if($result) {

            echo "<tr><th class='center'>" . __('Order') . "</th>";
            echo "<th class='center'>" . __('Type') . "</th>";
            echo "<th class='center'>" . __('Model') . "</th>";
            echo "<th class='center'>" . __('Cost') . "</th>";
            echo "<th class='center'>" . __('Ticket ID') . "</th>";
            echo "<th class='center'>" . __('Comment') . "</th>";
            echo "<th class='center'>" . __('Out of stock at', 'karastock') . "</th></tr>";

            $number = $DB->numrows($result);            
            
            while ($data = $DB->fetch_assoc($result)) {
                
                echo "<tr><td class='center'>" . $data['ordername'] . "</td>";
                echo "<td class='center'>" . $data['type'] . "</td>";
                echo "<td class='center'>" . $data['model'] . "</td>";
                echo "<td class='center'>" . ($data['cost'] > 0 ? $data['cost'] : "") . "</td>";
                echo "<td class='center'>" . ($data['tickets_id'] > 0 ? $data['tickets_id'] : "") . "</td>";
                echo "<td class='center'>" . $data['comment'] . "</td>";
                echo "<td class='center'>" .  Html::convDate($data['out_of_stock_at']) . "</td></tr>";
            }
        }

        echo "</table></div>";
    }

    private static function showSearchForm($date1, $date2) {

        $out = "<form method='get' name='form' action='" . PluginKarastockHistory::getSearchURL(true) . "'><div class='center'>";

        $out .= "<table class='tab_cadre'>";
        $out .= "<tr class='tab_bg_2'><td class='right'>".__('Start date')."</td><td>";

        $out .= Html::showDateField(
            'date1',
            [
                'value'   => $date1,
                'display' => false
            ]
        );
        $out .= "</td><td rowspan='2' class='center'>";
        $out .= "<input type='submit' class='submit' value='".__s('Display report')."'></td></tr>";

        $out .= "<tr class='tab_bg_2'><td class='right'>".__('End date')."</td><td>";
        $out .= Html::showDateField(
            'date2',
            [
                'value'   => $date2,
                'display' => false
            ]
        );

        $out .= "</td></tr>";
        $out .= "</table></div>";

        // form using GET method : CRSF not needed
        $out .= Html::closeForm(false);
        echo $out;
    }
}