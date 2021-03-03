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


include("../../../inc/includes.php");

Html::header(
    __('Karastock', 'karastock'),
    $_SERVER['PHP_SELF'],
    'management',
    'PluginKarastockMenu'
);

(new PluginKarastockOrder)->checkGlobal(READ);

echo "<div class='center'>";
echo "<table class='tab_cadre'>";
echo "<tr><th colspan='2'>" . __("Stock management", "karastock") . "</th></tr>";


if (PluginKarastockOrder::canView()) {

    echo "<tr class='tab_bg_1 center'>";
    echo "<td><i class='fas fa-shopping-cart' style='font-size:24px; margin: 5px 0'></i></td>";
    echo "<td><a href='".Toolbox::getItemTypeSearchURL('PluginKarastockOrder')."'>" .
    __("Orders", "karastock") . "</a></td></tr>";

}

if (PluginKarastockStock::canView()) {

    echo "<tr class='tab_bg_1 center'>";
    echo "<td><i class='fas fa-cubes' style='font-size:24px; margin: 5px 0'></i></td>";
    echo "<td><a href='".Toolbox::getItemTypeSearchURL('PluginKarastockStock')."'>" .
    __("Stock", "karastock") . "</a></td></tr>";

}

if (PluginKarastockHistory::canView()) {

    echo "<tr class='tab_bg_1 center'>";
    echo "<td><i class='fas fa-history' style='font-size:24px; margin: 5px 0'></i></td>";
    echo "<td><a href='".Toolbox::getItemTypeSearchURL('PluginKarastockHistory')."?history'>" .
    __("History", "karastock") . "</a></td></tr>";

}

echo "</table></div>";
Html::footer();