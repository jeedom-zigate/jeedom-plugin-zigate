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

require_once dirname(__FILE__) . "/../../../../core/php/core.inc.php";

if (!jeedom::apiAccess(init('apikey'), 'zigate')) {
    echo __('Vous n\'êtes pas autorisé à effectuer cette action', __FILE__);
    die();
}

$results = json_decode(file_get_contents("php://input"), true);
$response = array('success' => true);
log::add('zigate', 'debug', print_r($results, true));
$action = $results['action'];

if ($action == 'syncEqLogicWithZiGate') {
    zigate::syncEqLogicWithZiGate();
} elseif ($action == 'ZIGATE_DEVICE_ADDED') {
    $ieee = zigate::syncDevice($results['device']);
    event::add('jeedom::alert', array(
        'level' => 'warning',
        'page' => 'zigate',
        'message' => 'Nouvel équipement ZiGate ajouté',
    ));
    $eqLogic = zigate::byLogicalId($ieee, 'zigate');
    event::add('zigate::device_changed', $eqLogic->getId());
} elseif ($action == 'ZIGATE_DEVICE_UPDATED') {
    zigate::syncDevice($results['device']);
} elseif ($action == 'ZIGATE_DEVICE_REMOVED') {
    $device = $results['device'];
    zigate::removeDevice($device['ieee']);
    event::add('zigate::device_changed', '');
} elseif ($action == 'ZIGATE_DEVICE_NEED_REFRESH') {
    $device = $results['device'];
    $eqLogic = zigate::byLogicalId($device['ieee'], 'zigate');
    $humanName = $device['ieee'];
    if (is_object($eqLogic)) {
        $humanName = $eqLogic->getHumanName();
    }
    message::add('zigate', 'L\'équipement ZiGate '.$humanName.' requiert un rafraichissement.');
} elseif ($action == 'ZIGATE_ATTRIBUTE_ADDED') {
    zigate::syncDevice($results['device']);
} elseif ($action == 'ZIGATE_ATTRIBUTE_UPDATED') {
    if (isset($results['device'])) {
        $device = $results['device'];
        $eqLogic = zigate::byLogicalId($device['ieee'], 'zigate');
        if (is_object($eqLogic)) {
            $attribute = $results['attribute'];
            $eqLogic->update_command($attribute['endpoint'], $attribute['cluster'], $attribute);
            $eqLogic->setStatus('lastCommunication', $device['info']['last_seen']);
            $eqLogic->setStatus('rssi', $device['info']['rssi']);
        }
    }
} elseif ($action == 'ZIGATE_FAILED_TO_CONNECT') {
    message::add('zigate', $results['message']);
} elseif ($action == 'message') {
    message::add('zigate', $results['message']);
}

echo json_encode($response);
