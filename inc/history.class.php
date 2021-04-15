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

    private static function executeSearch($date1, $date2, $suppliers_id) {
        global $DB;  

        $suppliersTable     = Supplier::getTable();
        $suppliersFK        = Supplier::getForeignKeyField();
        $ticketsTable       = Ticket::getTable();
        $ticketsFK          = Ticket::getForeignKeyField();
        $entitiesTable      = Entity::getTable();
        $entitiesFK         = Entity::getForeignKeyField();        
        $ordersTable        = PluginKarastockOrder::getTable();
        $ordersFK           = PluginKarastockOrder::getForeignKeyField();
        $orderItemsTable    = PluginKarastockOrderItem::getTable();
        $orderItemsFK       = PluginKarastockOrderItem::getForeignKeyField();
    
        $query = "SELECT oi.*, o.name as 'ordername', o.suppliers_id, s.name as 'suppliername', e.name as 'entityname', o.bill_id, o.bill_received_at
            FROM $orderItemsTable as oi
            
            INNER JOIN $ordersTable as o
                ON o.id = oi.$ordersFK
            
            INNER JOIN $suppliersTable as s
                ON o.$suppliersFK = s.id
            
            LEFT JOIN $ticketsTable as t
                ON oi.$ticketsFK = t.id

            LEFT JOIN $entitiesTable as e
                ON t.$entitiesFK = e.id

            WHERE oi.withdrawal_at >= '$date1'
            AND oi.withdrawal_at <= '$date2'";
        
        if($suppliers_id) {

            $query .= " AND o.suppliers_id = $suppliers_id";
        }
        
        $result = $DB->query($query);
        return $result;
    }

    static function getFieldsName() {
        return array(
            __('Order', 'karastock'), 
            __('Supplier'),
            __('Type'),
            __('Model'),
            __('Cost'),
            __('Ticket'),
            __('Entity'),
            __('Comment'),
            __('Withdrawal at', 'karastock'),
            __('Invoice number', 'karastock'),
            __('Invoice date', 'karastock')
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
            Html::convDate($data['withdrawal_at']) ,
            $data['bill_id'],
            Html::convDate($data['bill_received_at'])
        );
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

        $supplier = array_key_exists('suppliers_id', $params) 
            ? $params['suppliers_id'] 
            : null;

        self::showSearchForm($date1, $date2, $supplier);

        $result = self::executeSearch($date1, $date2, $supplier);

        self::showExportButton($date1, $date2, $supplier);

        echo "<div class='center'>";
        echo "<table class='tab_cadre_fixehov'>";
        echo "<tr><th colspan='". count(self::getFieldsName()) ."' class='center'>" . __("Stock management", "karastock") . "</th></tr>";

        if($result) {

            echo "<tr>";
            foreach(static::getFieldsName() as $field) {
                echo "<th class='center'>" .$field. "</th>";
            }
            echo "</tr>";

            $number = $DB->numrows($result);            
            
            while ($data = $DB->fetchAssoc($result)) {                

                echo "<tr>";
                foreach(static::getFieldsValuesFromData($data) as $value) {
                    echo "<th class='center'>" .$value. "</th>";
                }
                echo "</tr>";

            }
        }

        echo "</table></div>";
    }

    private static function showSearchForm($date1, $date2, $suppliers_id = null) {

        $out = "<form method='get' name='form' action='" . self::getSearchURL(true) . "'><div class='center'>";

        $out .= "<table class='tab_cadre'>";
        $out .= "<tr class='tab_bg_2'><td class='right'>".__('Start date')."</td><td>";

        $out .= Html::showDateField(
            'date1',
            [
                'value'   => $date1,
                'display' => false
            ]
        );
        $out .= "</td><td rowspan='3' class='center'>";
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
        $out .= "<tr class='tab_bg_2'><td class='right'>".__('Supplier')."</td><td>";
        
        $out .= Supplier::dropdown([
            'name' => 'suppliers_id',
            'value' => $suppliers_id,
            'display' => false
        ]);

        $out .= "</td></tr>";
        $out .= "</table></div>";

        // form using GET method : CRSF not needed
        $out .= Html::closeForm(false);
        echo $out;
    }

    private static function showExportButton($date1, $date2, $suppliers_id = null) {
        
        $out = "<form method='get' name='form' style='margin:15px;' action='". self::getSearchURL(true) . "'><div class='center'>";
        $out .= "<input type='hidden' name='export' value='true' />";
        $out .= "<input type='hidden' name='date1' value='$date1' />";
        $out .= "<input type='hidden' name='date2' value='$date2' />";

        if($suppliers_id) {
            
            $out .= "<input type='hidden' name='suppliers_id' value='$suppliers_id' />";
        }

        $out .= "<span class='responsive_hidden'>" . __('Export to CSV') . "</span>";
        $out .= '<button type="submit" name="export" class="unstyled pointer" title="Exporter"><i class="far fa-save"></i><span class="sr-only">Exporter<span></button></div>';

        // form using GET method : CRSF not needed
        $out .= Html::closeForm(false);
        echo $out;
    }

    public static function exportReport($params) {   
        global $DB;       

        $date1 = array_key_exists('date1', $params) 
            ? $params['date1'] 
            : null;

        $date2 = array_key_exists('date2', $params) 
            ? $params['date2'] 
            : null;

        $suppliers_id = array_key_exists('suppliers_id', $params) 
            ? $params['suppliers_id'] 
            : null;

        if($date1 == null || $date2 == null) 
            return;
        
        $result = self::executeSearch($date1, $date2, $suppliers_id);

        if($result) {

            // filename
            $filename = 'history.csv';

            // open file to write
            $file = fopen($filename, 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

            // column names array
            $columns = self::getFieldsName();

            // write the columns
            fputcsv($file, $columns, ';');
            
            $number = $DB->numrows($result);            
            
            while ($data = $DB->fetchAssoc($result)) {

                $row = self::getFieldsValuesFromData($data);  

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