<?php
/* This file is part of Jeedom.
 *
 * Jeedom is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Jeedom is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Jeedom. If not, see <http://www.gnu.org/licenses/>.
 */

if (!isConnect('admin')) {
	throw new Exception('401 - Accès non autorisé');
}
$plugin = plugin::byId('zigate');
$eqLogics = zigate::byType('zigate');
?>

<table class="table table-condensed tablesorter" id="table_healthikealight">
	<thead>
		<tr>
			<th>{{Image}}</th>
			<th>{{Module}}</th>
			<th>{{ID}}</th>
			<th>{{Modèle}}</th>
			<th>{{Rssi}}</th>
			<th>{{Statut}}</th>
			<th>{{Batterie}}</th>
			<th>{{Dernière communication}}</th>
			<th>{{Date création}}</th>
		</tr>
	</thead>
	<tbody>
	 <?php
foreach ($eqLogics as $eqLogic) {
	if (file_exists(dirname(__FILE__) . '/../../images/' . $eqLogic->getConfiguration('type') . '.jpg')) {
		$image = '<img src="plugins/zigate/images/' . $eqLogic->getConfiguration('type') . '.jpg' . '" height="55" width="55" />';
	} else {
		$image = '<img src="' . $plugin->getPathImgIcon() . '" height="55" width="55" />';
	}
	echo '<tr><td>' . $image . '</td><td><a href="' . $eqLogic->getLinkToConfiguration() . '" style="text-decoration: none;">' . $eqLogic->getHumanName(true) . '</a></td>';
	echo '<td><span class="label label-info" style="font-size : 1em; cursor : default;">' . $eqLogic->getId() . '</span></td>';
	echo '<td><span class="label label-info" style="font-size : 1em; cursor : default;">' . $eqLogic->getConfiguration('type') . '</span></td>';
		$rssi_status = '<span class="label label-success" style="font-size : 1em;">{{OK}}</span>';
	if ($eqLogic->getConfiguration('rssi') < 50 && $eqLogic->getConfiguration('rssi') != '') {
		$rssi_status = '<span class="label label-danger" style="font-size : 1em;">' . $eqLogic->getConfiguration('rssi') . '</span>';
	} elseif ($eqLogic->getConfiguration('rssi') < 100 && $eqLogic->getConfiguration('rssi') != '') {
		$rssi_status = '<span class="label label-warning" style="font-size : 1em;">' . $eqLogic->getConfiguration('rssi') . '</span>';
	} elseif ($eqLogic->getConfiguration('rssi') >= 100 && $eqLogic->getConfiguration('rssi') != '') {
		$rssi_status = '<span class="label label-success" style="font-size : 1em;">' . $eqLogic->getConfiguration('rssi') . '</span>';
	} else {
		$rssi_status = '<span class="label label-primary" style="font-size : 1em;">' . $eqLogic->getConfiguration('rssi') . '</span>';
	}
	echo '<td>' . $rssi_status . '</td>';
	$status = '<span class="label label-success" style="font-size : 1em; cursor : default;">{{OK}}</span>';
	if ($eqLogic->getStatus('state') == 'nok') {
		$status = '<span class="label label-danger" style="font-size : 1em; cursor : default;">{{NOK}}</span>';
	}
	echo '<td>' . $status . '</td>';
	$battery_status = '<span class="label label-success" style="font-size : 1em;">{{OK}}</span>';
	if ($eqLogic->getStatus('battery') < 20 && $eqLogic->getStatus('battery') != '') {
		$battery_status = '<span class="label label-danger" style="font-size : 1em;">' . $eqLogic->getStatus('battery') . '%</span>';
	} elseif ($eqLogic->getStatus('battery') < 60 && $eqLogic->getStatus('battery') != '') {
		$battery_status = '<span class="label label-warning" style="font-size : 1em;">' . $eqLogic->getStatus('battery') . '%</span>';
	} elseif ($eqLogic->getStatus('battery') > 60 && $eqLogic->getStatus('battery') != '') {
		$battery_status = '<span class="label label-success" style="font-size : 1em;">' . $eqLogic->getStatus('battery') . '%</span>';
	} else {
		$battery_status = '<span class="label label-primary" style="font-size : 1em;" title="{{Secteur}}"><i class="fa fa-plug"></i></span>';
	}
	echo '<td>' . $battery_status . '</td>';
	echo '<td><span class="label label-info" style="font-size : 1em; cursor : default;">' . $eqLogic->getStatus('lastCommunication') . '</span></td>';
	echo '<td><span class="label label-info" style="font-size : 1em; cursor : default;">' . $eqLogic->getConfiguration('createtime') . '</span></td></tr>';
}
?>
	</tbody>
</table>
