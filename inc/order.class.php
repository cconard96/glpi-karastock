<?php
/**
 * -------------------------------------------------------------------------
 * Karastock plugin for GLPI
 * Copyright (C) 2020 by the Karastock Development Team.
 *
 * https://github.com/pluginsGLPI/newdporegister
 * -------------------------------------------------------------------------
 *
 * LICENSE
 *
 * This file is part of NewDpoRegister.
 *
 * NewDpoRegister is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 * 
 * NewDpoRegister is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with NewDpoRegister. If not, see <http://www.gnu.org/licenses/>.
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
                `number` varchar(255) collate utf8_unicode_ci default NULL,
                `date` datetime default NULL,
                `suppliers_id` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_suppliers (id)',
                
                PRIMARY KEY  (`id`),
                KEY `number` (`number`)
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";

            $DB->query($query) or die("error creating $table " . $DB->error());
        }

        return true;
    }

    /**
     * Uninstall PluginDporegisterProcessing
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
    
    //! @copydoc CommonGLPI::defineTabs($options)
    public function defineTabs($options = array())
    {
        $ong = array();

        $this->addDefaultFormTab($ong)
            ->addStandardTab(__class__, $ong, $options)
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
            'datatype' => 'number'
        ];

        $tab[] = [
            'id' => '2',
            'table' => $this->getTable(),
            'field' => 'number',
            'name' => __('Order Number', 'karastock'),
            'datatype' => 'itemlink',
            'searchtype' => 'contains',
            'massiveaction' => false
        ];

        $tab[] = [
            'id' => '3',
            'table' => $this->getTable(),
            'field' => 'date',
            'name' => __('Order date', 'karastock'),
            'datatype' => 'datetime',
            'massiveaction' => false
        ];

        $tab[] = [
            'id' => '4',
            'table' => 'glpi_suppliers',
            'field' => 'name',
            'name' => __('Supplier'),
            'datatype' => 'dropdown',
            'searchtype' => 'equals',
            'massiveaction' => false
        ];

        return $tab;
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

        $options['formfooter'] = false;

        $this->initForm($ID, $options);
        $this->showFormHeader($options);

        echo "<tr class='tab_bg_1'>";
        echo "<th width='$colsize1'>" . __('Order number', 'karastock') . "</th>";
        echo "<td width='$colsize2%'>";
        $title = Html::cleanInputText($this->fields["number"]);
        if ($canUpdate) {
            echo sprintf(
                "<input type='text' style='width:98%%' maxlength=250 name='number' required value=\"%1\$s\"/>",
                $title
            );
        } else {
            echo Toolbox::getHtmlToDisplay($title);
        }

        echo "</td><th width='$colsize1'>" . __('Supplier') . "</th>";
        echo "<td width='$colsize2%'>";
        Supplier::dropdown([
            'name' => 'suppliers_id',
            'value' => $this->fields['suppliers_id'],
            'required' => true
        ]);
        echo "</td></tr>";

        echo "<tr class='tab_bg_1'>";
        echo "<th width='$colsize1'>" . __('Order date', 'karastock') . "</th>";
        echo "<td width='$colsize2%'>";
        $date = $this->fields["date"];
        if ($canUpdate) {
            Html::showDateTimeField('date', [
                'value' => $date,
                'required' => true
            ]);
        } else {
            echo Html::convDateTime($date);
        }

        echo "</td><th width='$colsize1'>" . __('Supplier') . "</th>";
        echo "<td width='$colsize2%'>";

        echo "</td></tr>";
        
        $this->showFormButtons($options);
    }
}   