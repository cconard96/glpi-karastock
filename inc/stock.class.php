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

class PluginKarastockStock extends CommonDBTM {  

    public static $rightname         = 'plugin_karastock_stock';

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

    static function getFieldsName() {
        return array(
            __('Type'), 
            __('Model'),
            __('Quantity'),
            __('Status'),
            __('Cost'),
            __('Ticket'),
            __('Entity'),
            __('Comment')
        );
    }

    static function getFieldsValuesFromData($data) {
        return array(
            $data['ordername'],
            $data['suppliername'],
            PluginKarastockOrderItem::getTypes($data['type']),
            $data['model'],
            ($data['cost'] > 0 ? $data['cost'] : ""),
            ($data['tickets_id'] > 0 ? $data['tickets_id'] : ""),
            ($data['tickets_id'] > 0 ? $data['entityname'] : ""),
            $data['comment'],
            Html::convDate($data['withdrawal_at']) 
        );
    }

    static function show() {
        global $DB;

        $query = "SELECT count(*) as 'count', `type`, `model`, o.is_received, o.`name` AS 'ordername'
            FROM glpi_plugin_karastock_orderitems as oi 
            INNER JOIN glpi_plugin_karastock_orders as o on o.`id` = oi.`plugin_karastock_orders_id` 
            WHERE is_withdrawaled = 0 AND o.is_received = 1 
            GROUP BY `type`,`model`
            
            UNION
            
            SELECT count(*) as 'count', `type`, `model`, o.is_received, o.`name` AS 'ordername' 
            FROM glpi_plugin_karastock_orderitems as oi 
            INNER JOIN glpi_plugin_karastock_orders as o on o.`id` = oi.`plugin_karastock_orders_id` 
            WHERE is_withdrawaled = 0 AND o.is_received = 0 
            GROUP BY `type`,`model` 
            ORDER BY `type`,`model`
        ";

        $result = $DB->query($query);

        self::showExportButton();
                
        echo "<div class='center'>";
        echo "<table class='tab_cadre_fixehov'>";
        echo "<tr><th colspan='4' class='center'>" . __("Stock management", "karastock") . "</th></tr>";

        if($result) {

            echo "<tr><th class='center'>" . __('Type') . "</th>";
            echo "<th class='center'>" . __('Model') . "</th>";
            echo "<th class='center'>" . __('Quantity') . "</th>";
            echo "<th class='center'>" . __('Status') . "</th></tr>";

            $number = $DB->numrows($result);            
            
            while ($data = $DB->fetchAssoc($result)) {
                              
                echo "<tr><td class='center'><a href='".Toolbox::getItemTypeSearchURL('PluginKarastockStock')."?type=" . $data['type']. "'>" . PluginKarastockOrderItem::getTypes($data['type']) . "</a></td>";
                echo "<td class='center'><a href='".Toolbox::getItemTypeSearchURL('PluginKarastockStock')."?type=" . $data['type']. "&model=" . $data['model']. "'>" . $data['model'] . "</a></td>";
                echo "<td class='center'>" . $data['count'] . "</td>";
                echo "<td class='center'>" . 
                    ($data['is_received'] == 1 
                    ? "<i class='fas fa-check'></i>" 
                    : "<i class='fas fa-shipping-fast'></i>" ) 
                . "</td></tr>";
            }
        }
        
        echo "</table></div>";
    }

    static function showType($type)
    {
        global $DB;
        
        $query = "SELECT count(*) as 'count', `type`, `model`, `plugin_karastock_orders_id`, o.`is_received`, o.`name` AS 'ordername' 
            FROM glpi_plugin_karastock_orderitems as oi 
            INNER JOIN glpi_plugin_karastock_orders as o on o.`id` = oi.`plugin_karastock_orders_id` 
            WHERE is_withdrawaled = 0 AND o.`is_received` = 1 AND oi.type = '$type'
            GROUP BY `type`,`model`
            
            UNION
            
            SELECT count(*) as 'count', `type`, `model`, `plugin_karastock_orders_id`, o.`is_received`, o.`name` AS 'ordername' 
            FROM glpi_plugin_karastock_orderitems as oi 
            INNER JOIN glpi_plugin_karastock_orders as o on o.`id` = oi.`plugin_karastock_orders_id` 
            WHERE is_withdrawaled = 0 AND o.`is_received` = 0 AND oi.type = '$type'
            GROUP BY `type`,`model`

            ORDER BY `type`,`model`
        ";

        $result = $DB->query($query);
                
        echo "<div class='center'>";
        echo "<table class='tab_cadre_fixehov'>";
        echo "<tr><th colspan='6' class='center'>" . __("Stock management", "karastock") . " - " . __('Type')  . " : ". $type . "</th></tr>";

        if($result) {

            echo "<tr><th class='center'>" . __('Type') . "</th>";
            echo "<th class='center'>" . __('Model') . "</th>";
            echo "<th class='center'>" . __('Quantity') . "</th>";
            echo "<th class='center'>" . __('Status') . "</th></tr>";

            $number = $DB->numrows($result);            
            
            while ($data = $DB->fetchAssoc($result)) {
                
                echo "<tr><td class='center'>" . PluginKarastockOrderItem::getTypes($data['type']) . "</td>";
                echo "<td class='center'><a href='".Toolbox::getItemTypeSearchURL('PluginKarastockStock')."?type=" . $data['type']. "&model=" . $data['model']. "'>" . $data['model'] . "</a></td>";
                echo "<td class='center'>" . $data['count'] . "</td>";
                echo "<td class='center'>" . 
                    ($data['is_received'] == 1 
                    ? "<i class='fas fa-check'></i>" 
                    : "<i class='fas fa-shipping-fast'></i>" ) 
                . "</td></tr>";
            }
        }
        
        echo "</table></div>";
    }

    static function showModel($type, $model) {

        global $DB;
        
        $query = "SELECT count(*) as 'count', `type`, `model`, `tickets_id`, `plugin_karastock_orders_id`, o.`is_received`, o.`name` AS 'ordername'
            FROM glpi_plugin_karastock_orderitems as oi 
            INNER JOIN glpi_plugin_karastock_orders as o 
                ON o.`id` = oi.`plugin_karastock_orders_id` 
            WHERE is_withdrawaled = 0 
                AND o.`is_received` = 1 
                AND oi.type = '$type' 
                AND oi.model = '$model'
            GROUP BY plugin_karastock_orders_id
                        
            UNION
            
            SELECT count(*) as 'count', `type`, `model`, `tickets_id`, `plugin_karastock_orders_id`, o.`is_received`, o.`name` AS 'ordername' 
            FROM glpi_plugin_karastock_orderitems as oi 
            INNER JOIN glpi_plugin_karastock_orders as o 
                ON o.`id` = oi.`plugin_karastock_orders_id` 
            WHERE is_withdrawaled = 0 
                AND o.`is_received` = 0 
                AND oi.type = '$type' 
                AND oi.model = '$model'
            GROUP BY plugin_karastock_orders_id

            ORDER BY `type`,`model`
        ";

        $result = $DB->query($query);
                
        echo "<div class='center'>";
        echo "<table class='tab_cadre_fixehov'>";
        echo "<tr><th colspan='5' class='center'>" . __("Stock management", "karastock") . " - " . __('Type')  . " : ". $type . " - " . __('Model') . " : " . $model . "</th></tr>";

        if($result) {

            echo "<tr><th class='center'>" . __('Order', 'karastock') . "</th>";
            echo "<th class='center'>" . __('Type') . "</th>";
            echo "<th class='center'>" . __('Model') . "</th>";
            echo "<th class='center'>" . __('Quantity', 'karastock') . "</th>";
            echo "<th class='center'>" . __('Status') . "</th></tr>";

            $number = $DB->numrows($result);            
            
            while ($data = $DB->fetchAssoc($result)) {

                echo "<tr><td class='center'><a href='". PluginKarastockOrder::getFormURLWithID($data[PluginKarastockOrder::getForeignKeyField()]) ."'>" . $data['ordername'] . "</a></td>";
                echo "<td class='center'><a href='".Toolbox::getItemTypeSearchURL('PluginKarastockStock')."?type=" . $data['type']. "'>" . PluginKarastockOrderItem::getTypes($data['type']) . "</a></td>";
                echo "<td class='center'>" . $data['model'] . "</td>";
                echo "<td class='center'>" . $data['count'] . "</td>";
                echo "<td class='center'>" . 
                    ($data['is_received'] == 1 
                    ? "<i class='fas fa-check'></i>" 
                    : "<i class='fas fa-shipping-fast'></i>" ) 
                . "</td></tr>";
            }
        }
        
        echo "</table></div>";
    }       

    private static function showExportButton() {
        
        $out = "<form method='get' name='form' style='margin:15px;' action='". self::getSearchURL(true) . "'><div class='center'>";
        $out .= "<input type='hidden' name='export' value='true' />";

        $out .= "<span class='responsive_hidden'>" . __('Export to CSV') . "</span>";
        $out .= '<button type="submit" name="export" class="unstyled pointer" title="Exporter"><i class="far fa-save"></i><span class="sr-only">Exporter<span></button></div>';

        // form using GET method : CRSF not needed
        $out .= Html::closeForm(false);
        echo $out;
    }

    public static function exportReport($params) {   
        global $DB;
        
        $query = "SELECT count(*) as 'count', oi.`type`, oi.`model`, oi.`tickets_id`, oi.`cost`, oi.`comment`, `plugin_karastock_orders_id`, o.`is_received`, o.`name` AS 'ordername', e.`name` as 'entityname', s.name as 'suppliername'
            FROM glpi_plugin_karastock_orderitems as oi 
                
            INNER JOIN glpi_plugin_karastock_orders as o 
                ON o.`id` = oi.`plugin_karastock_orders_id`
                
            INNER JOIN glpi_suppliers as s 
                ON o.suppliers_id = s.id 
                
            LEFT JOIN glpi_tickets as t 
                ON oi.tickets_id = t.id 
            
            LEFT JOIN glpi_entities as e 
                ON t.entities_id = e.id
                
            WHERE is_withdrawaled = 0
            
            GROUP BY `type`,`model`,oi.`plugin_karastock_orders_id`";

        $result = $DB->query($query);

        if($result) {

            // filename
            $filename = 'stock.csv';

            // open file to write
            $file = fopen($filename, 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

            // column names array
            $columns =  array(
                __('Type'), 
                __('Model'),
                __('Order', 'karastock'),
                __('Supplier'),
                __('Quantity'),
                __('Status'),
                __('Cost'),
                __('Ticket'),
                __('Comment')
            );

            // write the columns
            fputcsv($file, $columns, ';');
            
            $number = $DB->numrows($result);            
            
            while ($data = $DB->fetchAssoc($result)) {

                $row = array(
                    PluginKarastockOrderItem::getTypes($data['type']),
                    $data['model'],
                    $data['ordername'],
                    $data['suppliername'],
                    $data['count'],
                    $data['is_received'],
                    ($data['cost'] > 0 ? $data['cost'] : ""),
                    ($data['tickets_id'] > 0 ? $data['tickets_id'] : ""),
                    $data['comment']
                );  

                // write the data
                fputcsv($file, $row, ';');
            }

            fclose($file);
        
            header('Content-type: text/csv; charset=utf-8');
            header('Content-disposition:attachment; filename="'.$filename.'"');
            readfile($filename);
        }   
    }
}