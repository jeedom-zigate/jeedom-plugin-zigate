<?php
/**
 * Copyright (c) 2018 Jeedom-ZiGate contributors
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */
if (!isConnect('admin')) {
    throw new Exception('401 - Accès non autorisé');
}
$plugin = plugin::byId('zigate');
$eqLogics = zigate::byType('zigate');
?>

<table class="table table-condensed tablesorter" id="table_healthZigate">
    <thead>
        <tr>
            <th>{{Image}}</th>
            <th>{{Module}}</th>
            <th>{{ID}}</th>
            <th>{{Modèle}}</th>
            <th>{{LQI}}</th>
            <th>{{Statut}}</th>
            <th>{{Batterie}}</th>
            <th>{{Dernière communication}}</th>
            <th>{{Date création}}</th>
        </tr>
    </thead>
    <tbody>
        <?php
        foreach ($eqLogics as $eqLogic) {
            $opacity = ($eqLogic->getIsEnable()) ? '' : jeedom::getConfiguration('eqLogic:style:noactive');
            echo '<tr><td><img src="' . $eqLogic->getImage() . '" height="55" width="55" /></td><td><a href="' . $eqLogic->getLinkToConfiguration() .
                '" style="' . $opacity . '">' . $eqLogic->getHumanName(true) . '</a></td>';
            echo '<td><span class="label label-info" style="font-size : 1em; cursor : default;' . $opacity . '">' . $eqLogic->getId() . '</span></td>';
            echo '<td><span class="label label-info" style="font-size : 1em; cursor : default;' . $opacity . '">' . $eqLogic->getConfiguration('type') . '</span></td>';
                $lqi_status = '<span class="label label-success" style="font-size : 1em;' . $opacity . '">{{OK}}</span>';
            if ($eqLogic->getConfiguration('lqi') < 50 && $eqLogic->getConfiguration('lqi') != '') {
                $lqi_status = '<span class="label label-danger" style="font-size : 1em;' . $opacity . '">' . $eqLogic->getConfiguration('lqi') . '</span>';
            } elseif ($eqLogic->getConfiguration('lqi') < 100 && $eqLogic->getConfiguration('lqi') != '') {
                $lqi_status = '<span class="label label-warning" style="font-size : 1em;' . $opacity . '">' . $eqLogic->getConfiguration('lqi') . '</span>';
            } elseif ($eqLogic->getConfiguration('lqi') >= 100 && $eqLogic->getConfiguration('lqi') != '') {
                $lqi_status = '<span class="label label-success" style="font-size : 1em;' . $opacity . '">' . $eqLogic->getConfiguration('lqi') . '</span>';
            } else {
                $lqi_status = '<span class="label label-primary" style="font-size : 1em;' . $opacity . '">' . $eqLogic->getConfiguration('lqi') . '</span>';
            }
            echo '<td>' . $lqi_status . '</td>';
            $status = '<span class="label label-success" style="font-size : 1em; cursor : default;' . $opacity . '">{{OK}}</span>';
            if ($eqLogic->getStatus('state') == 'nok') {
                $status = '<span class="label label-danger" style="font-size : 1em; cursor : default;' . $opacity . '">{{NOK}}</span>';
            }
            if (!$eqLogic->getIsEnable()) {
                $status = '<span class="label label-danger" style="font-size : 1em; cursor : default;' . $opacity . '">{{DISABLE}}</span>';
            }
            echo '<td>' . $status . '</td>';
            $battery_status = '<span class="label label-success" style="font-size : 1em;' . $opacity . '">{{OK}}</span>';
            if ($eqLogic->getConfiguration('power_type') == 1) {
                $battery_status = '<span class="label label-primary" style="font-size : 1em;' . $opacity . '" title="{{Secteur}}"><i class="fa fa-plug"></i></span>';
            } elseif ($eqLogic->getStatus('battery') < 20 && $eqLogic->getStatus('battery') != '') {
                $battery_status = '<span class="label label-danger" style="font-size : 1em;' . $opacity . '">' . $eqLogic->getStatus('battery') . '%</span>';
            } elseif ($eqLogic->getStatus('battery') < 60 && $eqLogic->getStatus('battery') != '') {
                $battery_status = '<span class="label label-warning" style="font-size : 1em;' . $opacity . '">' . $eqLogic->getStatus('battery') . '%</span>';
            } elseif ($eqLogic->getStatus('battery') >= 60 && $eqLogic->getStatus('battery') != '') {
                $battery_status = '<span class="label label-success" style="font-size : 1em;' . $opacity . '">' . $eqLogic->getStatus('battery') . '%</span>';
            }
            echo '<td>' . $battery_status . '</td>';
            echo '<td><span class="label label-info" style="font-size : 1em; cursor : default;' . $opacity . '">' . $eqLogic->getStatus('lastCommunication') . '</span></td>';
            echo '<td><span class="label label-info" style="font-size : 1em; cursor : default;' . $opacity . '">' . $eqLogic->getConfiguration('createtime') . '</span></td></tr>';
        }
        ?>
    </tbody>
</table>
