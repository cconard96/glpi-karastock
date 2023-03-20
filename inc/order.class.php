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

class PluginKarastockOrder extends CommonDBTM {

    public static $rightname         = 'plugin_karastock_order';
    public $dohistory                = true;
    protected $usenotepadrights      = true;
    protected $usenotepad            = true;

    // --------------------------------------------------------------------
    //  PLUGIN MANAGEMENT - DATABASE INITIALISATION
    // --------------------------------------------------------------------

    /**
     * Install or update PluginKarastockOrder
     *
     * @param Migration $migration Migration instance
     * @param string    $version   Plugin current version
     *
     * @return boolean
     */
    public static function install(Migration $migration, $version)
    {
        global $DB;
        $table = self::getTable();

        if (!$DB->tableExists($table)) {

            $migration->displayMessage(sprintf(__("Installing %s"), $table));
            $query = "CREATE TABLE `$table` (
                `id` int(11) NOT NULL auto_increment,

                `status` int(11) NOT NULL default '1',                
                `name` varchar(255) collate utf8mb4_unicode_ci default NULL,

                `number` varchar(255) collate utf8mb4_unicode_ci default NULL, 

                `other_identifier` varchar(255) collate utf8mb4_unicode_ci default NULL,
                `date` timestamp default NULL,
                `suppliers_id` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_suppliers (id)',

                `is_received` tinyint(1) default 0,
                `received_at` timestamp default NULL,
                `is_bill_received`  tinyint(1) default 0,
                `bill_received_at` timestamp default NULL,

                `comment` varchar(255) collate utf8mb4_unicode_ci default NULL,
                
                PRIMARY KEY  (`id`),
                KEY `status` (`status`),
                KEY `name` (`name`)
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8mb4_unicode_ci";

            $DB->query($query) or die("error creating $table " . $DB->error());
            
            // Insert default display preferences for Processing objects
            $query = "INSERT INTO `glpi_displaypreferences` (`itemtype`, `num`, `rank`, `users_id`) VALUES
            ('" . __class__ . "', 1, 1, 0),
            ('" . __class__ . "', 2, 2, 0),
            ('" . __class__ . "', 3, 3, 0),
            ('" . __class__ . "', 4, 4, 0),
            ('" . __class__ . "', 5, 5, 0),
            ('" . __class__ . "', 6, 6, 0)";

            $DB->query($query) or die("populating display preferences " . $DB->error());
        }

        if(!$DB->fieldExists($table, 'bill_id')) {

            $migration->displayMessage(sprintf(__("Updating %s"), $table));
            $query = "ALTER TABLE `$table`
                ADD `bill_id` varchar(255) collate utf8mb4_unicode_ci default NULL;";
            
            $DB->query($query) or die("error updating $table schema " . $DB->error());
        }

        if(!$DB->fieldExists($table, 'comment')) {

            $migration->displayMessage(sprintf(__("Updating %s"), $table));
            $query = "ALTER TABLE `$table`
                ADD `comment` varchar(255) collate utf8mb4_unicode_ci default NULL;";
            
            $DB->query($query) or die("error updating $table schema " . $DB->error());

            $query = "ALTER TABLE `$table` 
                MODIFY COLUMN `date` timestamp, 
                MODIFY COLUMN `received_at` timestamp, 
                MODIFY COLUMN `bill_received_at` timestamp;";
            
            $DB->query($query) or die("error updating $table field type DATETIME to TIMESTAMP " . $DB->error());          
        }



        return true;
    }

    /**
     * Uninstall PluginKarastockOrder
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

        // Purge display preferences table
        $query = "DELETE FROM `glpi_displaypreferences` WHERE `itemtype` = '" . __class__ . "'";
        $DB->query($query) or die('error purge display preferences table' . $DB->error());

        // Purge logs table
        $query = "DELETE FROM `glpi_logs` WHERE `itemtype` = '" . __class__ . "'";
        $DB->query($query) or die('error purge logs table' . $DB->error());

        // Delete links with documents
        $query = "DELETE FROM `glpi_documents_items` WHERE `itemtype` = '" . __class__ . "'";
        $DB->query($query) or die('error purge documents_items table' . $DB->error());

        // Delete notes associated to processings
        $query = "DELETE FROM `glpi_notepads` WHERE `itemtype` = '" . __class__ . "'";
        $DB->query($query) or die('error purge notepads table' . $DB->error());

        return true;
    }

    // --------------------------------------------------------------------
    //  GLPI PLUGIN COMMON
    // --------------------------------------------------------------------

    //! @copydoc CommonGLPI::getTypeName($nb)
    public static function getTypeName($nb = 0)
    {
        return _n('Order', 'Orders', $nb, 'karastock');
    }

    //! @copydoc CommonDBTM::getIcon()
    static function getIcon()
    {
        return "fas fa-shopping-cart";
    }
    
    //! @copydoc CommonGLPI::defineTabs($options)
    public function defineTabs($options = array())
    {
        $ong = array();

        $this->addDefaultFormTab($ong)
            ->addStandardTab(__class__, $ong, $options)

            ->addStandardTab(PluginKarastockOrderItem::class, $ong, $options)

            ->addStandardTab('Document_Item', $ong, $options)
            ->addStandardTab('Notepad', $ong, $options)
            ->addStandardTab('Log', $ong, $options);

        return $ong;
    }

    //! @copydoc CommonDBTM::rawSearchOptions()
    function rawSearchOptions()
    {
        global $CFG_GLPI;

        $tab = [];

        $tab[] = [
            'id' => 'common',
            'name' => __('Characteristics')
        ];

        $tab[] = [
            'id' => '1',
            'table' => $this->getTable(),
            'field' => 'id',
            'name' => __('ID'),
            'massiveaction' => false,
            'datatype' => 'name'
        ];

        $tab[] = [
            'id' => '2',
            'table' => $this->getTable(),
            'field' => 'name',
            'name' => __('Order Number', 'karastock'),
            'datatype' => 'itemlink',
            'searchtype' => 'contains',
            'massiveaction' => false
        ];

        $tab[] = [
            'id' => '3',
            'table' => 'glpi_suppliers',
            'field' => 'name',
            'name' => __('Supplier'),
            'datatype' => 'dropdown',
            'searchtype' => ['equals', 'notequals'],
            'massiveaction' => true
        ];

        $tab[] = [
            'id' => '4',
            'table' => $this->getTable(),
            'field' => 'date',
            'name' => __('Order date', 'karastock'),
            'datatype' => 'date',
            'massiveaction' => false
        ];

        $tab[] = [
            'id' => '5',
            'table' => $this->getTable(),
            'field' => 'received_at',
            'name' => __('Received at', 'karastock'),
            'datatype' => 'date',
            'massiveaction' => true
        ];

        $tab[] = [
            'id' => '6',
            'table' => $this->getTable(),
            'field' => 'is_received',
            'name' => __('Received', 'karastock'),
            'datatype' => 'bool',
            'massiveaction' => true
        ];

        $tab[] = [
            'id' => '7',
            'table' => $this->getTable(),
            'field' => 'bill_received_at',
            'name' => __('Bill received at', 'karastock'),
            'datatype' => 'date',
            'massiveaction' => true
        ];

        $tab[] = [
            'id' => '8',
            'table' => $this->getTable(),
            'field' => 'is_bill_received',
            'name' => __('Bill received', 'karastock'),
            'datatype' => 'bool',
            'massiveaction' => true
        ];

        $tab[] = [
            'id' => '9',
            'table' => $this->getTable(),
            'field' => 'bill_id',
            'name' => __('Bill ID', 'karastock'),
            'datatype' => 'contains',
            'massiveaction' => false
        ];

        /*
        $tab = array_merge(
            $tab,
            PluginKarastockOrderItem::rawSearchOptionsToAdd()
        );
        */

        return $tab;
    }

    /**
    * Get default values to search engine to override
    **/
    static function getDefaultSearchRequest() {

        $search = ['criteria' =>
                    [
                        0 => [
                            'field'      => 6,
                            'searchtype' => 'equals',
                            'value'      => '0'
                        ],
                        1 => [
                            'field'      => 8,
                            'searchtype' => 'equals',
                            'link'       => 'OR',
                            'value'      => '0'
                        ]
                    ],

                    'sort'     => 4,
                    'order'    => 'DESC'
                ];

        return $search;
    }

    /**
     * Show the current (or new) object formulaire
     * 
     * @param Integer $ID
     * @param Array $options
     */
    public function showForm($ID, $options = array())
    {
        $colsize1 = '13%';
        $colsize2 = '29%';
        $colsize3 = '13%';
        $colsize4 = '45%';

        $canUpdate = self::canUpdate() || (self::canCreate() && !$ID);

        $showUserLink = 0;
        if (Session::haveRight('user', READ)) {
            $showuserlink = 1;
        }

        $options['canedit'] = $canUpdate;

        if ($ID) {

            $options['formtitle'] = sprintf(
                _('%1$s - ID %2$d'),
                $this->getTypeName(1),
                $ID
            );
        }

        $options['formfooter'] = true;

        $this->initForm($ID, $options);
        $this->showFormHeader($options);

        echo "<tr class='tab_bg_1'>";
        echo "<th width='$colsize1'>" . __('Order Number', 'karastock') . "</th>";
        echo "<td width='$colsize2%'>";
        $number = Html::cleanInputText($this->fields["name"]);
        if ($canUpdate) {
            echo sprintf(
                "<input type='text' style='width:95%%' maxlength=250 name='name' required value=\"%1\$s\"/>",
                $number
            );
        } else {
            echo Toolbox::getHtmlToDisplay($number);
        }
        echo "</td><th width='$colsize1'>" . __('Other ID', 'karastock') . "</th>";
        echo "<td width='$colsize2'>";
        $otherid = Html::cleanInputText($this->fields["other_identifier"]);
        if ($canUpdate) {
            echo sprintf(
                "<input type='text' style='width:95%%' maxlength=250 name='other_identifier' value=\"%1\$s\"/>",
                $otherid
            );
        } else {
            echo Toolbox::getHtmlToDisplay($otherid);
        }
        echo "</td></tr>";


        echo "<th width='$colsize1'>" . __('Supplier') . "</th>";
        echo "<td width='$colsize2'>";
        Supplier::dropdown([
            'name' => 'suppliers_id',
            'value' => $this->fields['suppliers_id'],
            'required' => true
        ]);

        echo "</td></tr>";

        echo "<tr class='tab_bg_1'>";
        echo "<th width='$colsize1'>" . __('Order date', 'karastock') . "</th>";
        echo "<td width='$colsize2'>";
        $date = $this->fields["date"];
        if ($canUpdate) {
            Html::showDateField('date', [
                'value' => $date,
                'required' => !$ID
            ]);
        } else {
            echo Html::convDateTime($date);
        }

        echo "</td></tr>";

        if ($ID) {
            
            echo "<tr class='tab_bg_1'>";
            echo "<th width='$colsize1'>" . __('Received', 'karastock') . "</th>";
            echo "<td width='$colsize2'>";
            $rand = Dropdown::showYesNo('is_received', $this->fields['is_received']);
            $params = [
                'is_received' => '__VALUE__',
                'received_at' => $this->fields['received_at']
            ];

            Ajax::updateItemOnSelectEvent(
                "dropdown_is_received$rand",
                "received_div",
                "../ajax/datetime_dropdown.php",
                $params
            );

            $opt = ['value' => $this->fields['received_at']];
            echo "<div id='received_div'>";
            if ($this->fields['is_received']) { 
                Html::showDateField('received_at', $opt);
            }
            echo "</div>";
            echo "</td><th width='$colsize1'>" . __('Bill received', 'karastock') . "</th>";

            echo "<td width='$colsize2'>";
            $rand = Dropdown::showYesNo('is_bill_received', $this->fields['is_bill_received']);
            $params = [
                'is_bill_received' => '__VALUE__',
                'bill_received_at' => $this->fields['bill_received_at'],
                'bill_id'   => $this->fields['bill_id']
            ];

            Ajax::updateItemOnSelectEvent(
                "dropdown_is_bill_received$rand",
                "bill_received_div",
                "../ajax/datetime_dropdown.php",
                $params
            );

            $opt = ['value' => $this->fields['bill_received_at']];
            echo "<div id='bill_received_div'>";
            if ($this->fields['is_bill_received']) { 
                Html::showDateField('bill_received_at', $opt);
                echo sprintf(
                    "<input type='text' placeholder='" . __('Bill ID', 'karastok') . "' style='width:95%%; margin-top: 5px;' maxlength=250 name='bill_id' value=\"%1\$s\"/>",
                    $this->fields['bill_id']
                );
            }
            echo "</div>";
            echo "</td></tr>";
        }
        
        $this->showFormButtons($options);
    }    
}   
