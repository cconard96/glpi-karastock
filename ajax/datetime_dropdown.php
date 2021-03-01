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

if (strpos($_SERVER['PHP_SELF'], "datetime_dropdown.php")) {
    $AJAX_INCLUDE = 1;

    include("../../../inc/includes.php");
    Plugin::load('karastock', true);

    header("Content-Type: text/html; charset=UTF-8");
    Html::header_nocache();
}

if (array_key_exists('is_received', $_POST) && $_POST['is_received']) {
    
    Html::showDateField('received_at', 
        ['value' => $_POST['received_at']]
    );
}
else if (array_key_exists('is_bill_received', $_POST) && $_POST['is_bill_received']) {
    
    Html::showDateField('bill_received_at', 
        ['value' => $_POST['bill_received_at']]
    );
}
else if (array_key_exists('is_out_of_stock', $_POST) && $_POST['is_out_of_stock']) {
    
    Html::showDateField('out_of_stock_at', 
        [
            'value' => $_POST['out_of_stock_at'],
            'required' => true
        ]
    );
}
else { echo ''; }
