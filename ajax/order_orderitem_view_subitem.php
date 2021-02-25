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

if (strpos($_SERVER['PHP_SELF'], "order_orderitem_view_subitem.php")) {
    $AJAX_INCLUDE = 1;

    include("../../../inc/includes.php");
    Plugin::load('karastock', true);

    header("Content-Type: text/html; charset=UTF-8");
    Html::header_nocache();
}

if (array_key_exists(
    PluginKarastockOrder::getForeignKeyField(), 
    $_POST
)) {
    if($_POST['id'] > 0)  {
        
        // Show the inputs form with the specified ID
        $item = new PluginKarastockOrderItem();
        $item->showEditForm($_POST['id'], $_POST);
    }
    else {

        PluginKarastockOrderItem::showAddForm(
            $_POST[PluginKarastockOrder::getForeignKeyField()]
        );
    }
}
else { echo ''; }

Html::ajaxFooter();