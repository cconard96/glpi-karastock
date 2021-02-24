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

if (!defined('GLPI_ROOT')) {
    die("Sorry. You can't access this file directly");
}


class PluginKarastockOrderItem_Order extends CommonDBRelation {

    static public $itemtype_1, $items_id_1, $itemtype_2, $items_id_2;   
    public $dohistory                = true;

    public static function init() {

        self::$itemtype_1 = PluginKarastockOrderItem::class;
        self::$items_id_1 = PluginKarastockOrderItem::getForeignKeyField();

        self::$itemtype_2 = PluginKarastockOrder::class;
        self::$items_id_2 = PluginKarastockOrder::getForeignKeyField();
    }

    // --------------------------------------------------------------------
    //  PLUGIN MANAGEMENT - DATABASE INITIALISATION
    // --------------------------------------------------------------------

    /**
     * Install or update PluginKarastockOrderItem_Order
     *
     * @param Migration $migration Migration instance
     * @param string    $version   Plugin current version
     *
     * @return boolean
     */
    public static function install(Migration $migration, $version) {
        
        global $DB;
        $table = self::getTable();

        if (!$DB->tableExists($table)) {

            $migration->displayMessage(sprintf(__("Installing %s"), $table));

            $query = "CREATE TABLE `$table` (
                `id` int(11) NOT NULL auto_increment,
                `" . self::$items_id_1 . "` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_plugin_karastock_orderitems (id)',
                `" . self::$items_id_2 . "` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_plugin_karastock_orders (id)',

                PRIMARY KEY  (`id`)
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";

            $DB->query($query) or die("error creating $table " . $DB->error());
        }
    }

    /**
     * Uninstall PluginKarastockOrderItem_Order
     *
     * @return boolean
     */
    public static function uninstall() {
        global $DB;
        $table = self::getTable();

        if ($DB->tableExists($table)) {
            $query = "DROP TABLE `$table`";
            $DB->query($query) or die("error deleting $table " . $DB->error());
        }

        // Purge the logs table of the entries about the current class
        $query = "DELETE FROM `glpi_logs`
            WHERE `itemtype` = '" . __CLASS__ . "' 
            OR `itemtype_link` = '" . self::$itemtype_1 . "' 
            OR `itemtype_link` = '" . self::$itemtype_2 . "'";
            
        $DB->query($query) or die ("error purge logs table");

        return true;
    }

    // --------------------------------------------------------------------
    //  GLPI PLUGIN COMMON
    // --------------------------------------------------------------------

    //! @copydoc CommonGLPI::getTypeName($nb)
    static function getTypeName($nb = 0) {
        return __('Link Order Item/Order', 'karastock');
    }

    
    //! @copydoc CommonGLPI::displayTabContentForItem($item, $tabnum, $withtemplate)
    static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0) {

        // Check ACL
        if (!$item->canView()) {
            return false;
        }

        // Check item type
        switch ($item->getType()) {

            case PluginKarastockOrder::class:
                self::showForOrder($item);
                break;
        }

        return true;
    }  
    
    //! @copydoc CommonGLPI::getTabNameForItem($item, $withtemplate)
    function getTabNameForItem(CommonGLPI $item, $withtemplate = 0)
    {
        switch ($item->getType()) {
            case PluginKarastockOrder::class:

                $nb = 0;

                if ($_SESSION['glpishow_count_on_tabs']) {

                    $nb = countElementsInTable(
                        self::getTable(),
                        [
                            self::$items_id_1 => $item->getID()
                        ]
                    );
                }

                return self::createTabEntry(PluginKarastockOrderItem::getTypeName($nb), $nb);
        }

        return '';
    }

    /**
     * Show the tab content for the Order Object
     * 
     * @param   PluginKarastockOrder $order
     * 
     * @return  void
     */
    static function showForOrder($order) {

        global $DB;
        $table = self::getTable();

        $types = Ticket::getAllTypesForHelpdesk();
        $emptylabel = __('General');

        Dropdown::showItemTypes('item_type', array_keys($types),
                                [
                                    'emptylabel' => $emptylabel,
                                    //'value'      => $itemtype,
                                    //'rand'       => $rand, 
                                    'display_emptychoice' => true
                                ]);

        $found_type = isset($types[$itemtype]);

        ComputerType::dropdown("test");
    }
}

// Emulate static constructor
PluginKarastockOrderItem_Order::init();