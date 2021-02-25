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

class PluginKarastockOrderItem extends CommonDBChild {

    public static $rightname         = 'plugin_karastock_order';
    public $dohistory                = true;

    static public $itemtype, $items_id;   

    public static function init() {

        self::$itemtype = PluginKarastockOrder::class;
        self::$items_id = PluginKarastockOrder::getForeignKeyField();
    }

    // --------------------------------------------------------------------
    //  PLUGIN MANAGEMENT - DATABASE INITIALISATION
    // --------------------------------------------------------------------

    /**
     * Install or update PluginKarastockOrderItem
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
                `".self::$items_id."` int,
                `type` varchar(255) collate utf8_unicode_ci default NULL,  
                `model` varchar(255) collate utf8_unicode_ci default NULL, 
                `cost` decimal NOT NULL default 0, 
                `tickets_id` int(11) NOT NULL default 0, 
                `is_used` int(1) NOT NULL default 0,                             

                PRIMARY KEY  (`id`)
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";

            $DB->query($query) or die("error creating $table " . $DB->error());
        }
    }

    /**
     * Uninstall PluginKarastockOrderItem
     *
     * @return boolean
     */
    public static function uninstall()
    {
        global $DB;
        $table = self::getTable();

        if ($DB->tableExists($table)) {
            $query = "DROP TABLE `$table`";
            $DB->query($query) or die("error deleting $table " . $DB->error());
        }

        // Purge the logs table of the entries about the current class
        $query = "DELETE FROM `glpi_logs` WHERE `itemtype` = '" . __class__ . "'";
        $DB->query($query) or die('error purge logs table' . $DB->error());

        return true;
    }

    // --------------------------------------------------------------------
    //  GLPI PLUGIN COMMON
    // --------------------------------------------------------------------

    //! @copydoc CommonGLPI::getTypeName($nb)
    public static function getTypeName($nb = 0)
    {
        return _n('Order item', 'Order items', $nb, 'karastock');
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
                            self::$items_id => $item->getID()
                        ]
                    );
                }

                return self::createTabEntry(PluginKarastockOrderItem::getTypeName($nb), $nb);
        }

        return '';
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
        
        $orderId = $order->fields['id'];
        
        $canedit = PluginKarastockOrder::canUpdate();
        $rand = mt_rand(1, mt_getrandmax());

        if ($canedit) {

            echo "<script type='text/javascript' >\n";
            echo "function viewOrderItem" . $orderId . "_$rand() {\n";
            $params = [
                'id' => -1,
                self::$items_id => $orderId
            ];

            Ajax::updateItemJsCode(
                "viewOrderItem" . $orderId . "_$rand",
                "../ajax/order_orderitem_view_subitem.php",
                $params
            );

            echo "$('#viewOrderItem').hide();";
            echo "};";
            echo "</script>\n";
            
            echo "<div class='center firstbloc'>";
            echo "<div id='viewOrderItem" . $orderId . "_$rand'></div>";

            echo "<a class='vsubmit' id='viewOrderItem' href='javascript:viewOrderItem" . $orderId . "_$rand();'>" .
                __('Add new items to this Order', 'karastock') . "</a>";
            echo "</div>";    
        }

        $query = "SELECT * FROM ".self::getTable()." WHERE ".self::$items_id."=".$orderId;
        $result = $DB->query($query);

        if($result) {

            $number = $DB->numrows($result);
            
            echo "<div class='spaced'>";
            if ($canedit && $number) {
                Html::openMassiveActionsForm('mass' . __class__ . $rand);
                $massiveactionparams = ['container' => 'mass' . __class__ . $rand];
                Html::showMassiveActions($massiveactionparams);
            }

            echo "<table class='tab_cadre_fixehov'>";

            $header_begin = "<tr>";
            $header_top = '';
            $header_bottom = '';
            $header_end = '';
            
            if ($canedit && $number) {
                
                $header_top .= "<th width='10'>" . Html::getCheckAllAsCheckbox('mass' . __class__ . $rand);
                $header_top .= "</th>";
                $header_bottom .= "<th width='10'>" . Html::getCheckAllAsCheckbox('mass' . __class__ . $rand);
                $header_bottom .= "</th>";
            }

            $header_end .= "<th class='center'>" . __('Type') . "</th>";
            $header_end .= "<th class='center'>" . __('Model') . "</th>";
            $header_end .= "<th class='center'>" . __('Cost') . "</th>";
            $header_end .= "<th class='center'>" . __('Is used', 'karastock') . "</th>";
            $header_end .= "<th class='center'>" . __('Ticket') . "</th>";
            echo $header_begin . $header_top . $header_end . "</tr>";

            while ($data = $DB->fetch_assoc($result)) {

                echo "<tr class='tab_bg_1'" . ($canedit ?
                "style='cursor:pointer' onClick=\"viewEditOrderItem" . $orderId . "_" . $data['id'] . "_$rand()\""
                : '') . ">";

                if ($canedit) {
                    echo "<td width='10'>";
                    Html::showMassiveActionCheckBox(__class__, $data["id"]);
                    echo "</td>";                    
                }

                echo "<td class='center'>" .  __($data['type']) . "</td>";
                echo "<td class='center'>" . $data['model'] . "</td>";
                echo "<td class='center'>" . $data['cost'] . "</td>";
                echo "<td class='center'>" . ($data['is_used'] == 1 ? __('Yes') : __('No')) . "</td>";
                echo "<td class='center'><a href=''>" . $data['tickets_id'] . "</a></td>";

                if ($canedit) {
                    echo "\n<script type='text/javascript' >\n";
                    echo "function viewEditOrderItem" . $orderId . "_" . $data['id'] . "_$rand() {\n";

                    $params = [
                        self::$items_id => $orderId,
                        'id' => $data["id"]
                    ];

                    Ajax::updateItemJsCode(
                        "viewOrderItem" . $orderId . "_$rand",
                        "../ajax/order_orderitem_view_subitem.php",
                        $params
                    );

                    echo "$('#viewOrderItem').show();";

                    echo "};";
                    echo "</script>\n";
                }

                echo "</tr>";
            }

            echo "</table>";

            if ($canedit && $number) {
                $massiveactionparams['ontop'] = false;
                Html::showMassiveActions($massiveactionparams);
                Html::closeForm();
            }

            echo "</div>";
        }
    }

    public static function showAddForm($orderId) {

        $types = Ticket::getAllTypesForHelpdesk();

        $colsize1 = '13';
        $colsize2 = '29';
        $colsize3 = '13';
        $colsize4 = '45';

        $itemtype = '';
        $rand = mt_rand(1, mt_getrandmax());

        echo "<div class='firstbloc'>";
        echo "<form name='ticketitem_form' id='ticketitem_form' method='post'
            action='" . Toolbox::getItemTypeFormURL(__class__) . "'>";

        echo "<table class='tab_cadre_fixe'>";
        echo "<tr class='headerRow'>";
        echo "<th colspan='4'>Adding new Item(s)</th></tr>";
        echo "<tr class='tab_bg_1'>";
        echo "<td class='left' width='$colsize1%'><label>" . __('Item type', 'karastock') . "</label></td><td width='$colsize2%'>";
        Dropdown::showItemTypes('type', array_keys($types),
        [
            'value'      => $itemtype,
            'rand'       => $rand, 
            'display_emptychoice' => false
        ]);

        echo "</td></tr>";
        echo "<tr class='tab_bg_1'>";
        echo "<td class='left' width='$colsize1%'><label>" . __('Item Model', 'karastock') . "</label></td><td width='$colsize2%'>";
        Html::autocompletionTextField(new PluginKarastockOrderItem(), 'model');
        echo "</td></tr>";

        echo "</td></tr>";
        echo "<tr class='tab_bg_1'>";
        echo "<td class='left' width='$colsize1%'><label>" . __('Item Cost', 'karastock') . "</label></td><td width='$colsize2%'>";
        Html::autocompletionTextField(new PluginKarastockOrderItem(), 'cost');
        echo "</td></tr>";

        echo "<tr class='tab_bg_1'>";
        echo "<td class='left' width='$colsize1%'><label>" . __('Item Count', 'karastock') . "</label></td><td width='$colsize2%'>";
        Dropdown::showNumber('count', ['min'   => 1, 'max'   => 100]);
        echo "</td></tr>";

        echo "<tr class='tab_bg_1'><td class='center' colspan='2'>";        
        echo "<input type='hidden' name='".self::$items_id."' value='$orderId' />";
        echo "<input type='submit' name='add' value=\"" . _sx('button', 'Add') . "\" class='submit'>";
        echo "</td></tr>";

        echo "</table>";
        Html::closeForm();
        echo "</div>";  
    }

    public static function addItemsFromPOST($POST) {

        $count = $_POST['count'];
        for($i = 0; $i < $count; $i++) {   

            $orderitem = new PluginKarastockOrderItem();
            $orderitem->add($_POST);
        }
    }

    public function showEditForm($ID, $POST) {
        
        $this->getFromDB($ID);

        $order = new PluginKarastockOrder();
        $order->getFromDB($this->fields[PluginKarastockOrder::getForeignKeyField()]);
        
        $types = Ticket::getAllTypesForHelpdesk();

        $colsize1 = '13';
        $colsize2 = '29';
        $colsize3 = '13';
        $colsize4 = '45';

        $rand = mt_rand(1, mt_getrandmax());

        echo "<div class='firstbloc'>";
        echo "<form name='ticketitem_form' id='ticketitem_form' method='post'
            action='" . Toolbox::getItemTypeFormURL(__class__) . "'>";

        echo "<table class='tab_cadre_fixe'>";
        echo "<tr class='headerRow'>";
        echo "<th colspan='4'>Adding new Item(s)</th></tr>";
        echo "<tr class='tab_bg_1'>";
        echo "<td class='left' width='$colsize1%'><label>" . __('Item type', 'karastock') . "</label></td><td width='$colsize2%'>";
        Dropdown::showItemTypes('type', array_keys($types),
        [
            'value'      => $this->fields['type'],
            'rand'       => $rand, 
            'display_emptychoice' => false
        ]);

        echo "</td></tr>";
        echo "<tr class='tab_bg_1'>";
        echo "<td class='left' width='$colsize1%'><label>" . __('Item Model', 'karastock') . "</label></td><td width='$colsize2%'>";
        Html::autocompletionTextField($this, 'model');
        echo "</td></tr>";

        echo "</td></tr>";
        echo "<tr class='tab_bg_1'>";
        echo "<td class='left' width='$colsize1%'><label>" . __('Item Cost', 'karastock') . "</label></td><td width='$colsize2%'>";
        Html::autocompletionTextField($this, 'cost');
        echo "</td></tr>";

        echo "</td></tr>";
        echo "<tr class='tab_bg_1'>";
        echo "<td class='left' width='$colsize1%'><label>" . __('Ticket') . "</label></td><td width='$colsize2%'>";
        Ticket::dropdown([
            'displaywith' => ['id'],
            'condition'   => Ticket::getOpenCriteria(),
            'entity'      => $order->getEntityID(),
            'entity_sons' => $order->isRecursive(),
        ]); 

        if($this->fields['tickets_id'] > 0) {
            echo " - " . __('Selected') . " : <span>".$this->fields['tickets_id']."</span>";
        }
        echo "</td></tr>";

        echo "</td></tr>";
        echo "<tr class='tab_bg_1'>";
        echo "<td class='left' width='$colsize1%'><label>" . __('Is Used') . "</label></td><td width='$colsize2%'>";
        Dropdown::showYesNo('is_used', $this->fields['is_used']);
        echo "</td></tr>";

        echo "<tr class='tab_bg_1'><td class='center' colspan='2'>";        
        echo "<input type='hidden' name='id' value='".$this->fields['id']."' />";
        echo "<input type='submit' name='update' value=\"" . _sx('button', 'Edit') . "\" class='submit'>";
        echo "</td></tr>";

        echo "</table>";
        Html::closeForm();
        echo "</div>";  
    }
}

// Emulate static constructor
PluginKarastockOrderItem::init();