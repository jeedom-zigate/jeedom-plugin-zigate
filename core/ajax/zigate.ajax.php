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

try {
    require_once dirname(__FILE__) . '/../../../../core/php/core.inc.php';
    include_file('core', 'authentification', 'php');

    if (!isConnect('admin')) {
        throw new Exception(__('401 - Accès non autorisé', __FILE__));
    }

    ajax::init();

    $action = init('action');

    if ($action == 'syncEqLogicWithZiGate') {
        zigate::syncEqLogicWithZiGate();
        ajax::success();
    } elseif ($action == 'refresh_eqlogic') {
        $id = intval(init('args')[0]);
        $eqLogic = zigate::byId($id);
        $addr = $eqLogic->getLogicalId();
        $result = zigate::callZiGate('refresh_device', [$addr]);
        if ($result['success']) {
            ajax::success();
        } else {
            ajax::error('Echec');
        }
    } elseif ($action == 'identify_device') {
        $id = intval(init('args')[0]);
        $eqLogic = zigate::byId($id);
        $addr = $eqLogic->getLogicalId();
        $result = zigate::callZiGate('identify_device', [$addr,10]);
        if ($result['success']) {
            ajax::success();
        } else {
            ajax::error('Echec');
        }
    } else {
        $result = zigate::callZiGate($action, init('args'));
        if ($result['success']) {
            ajax::success();
        } else {
            ajax::error('Echec');
        }
    }

    throw new Exception(__('Aucune méthode correspondante à : ', __FILE__) . init('action'));
    /*     * *********Catch exeption*************** */
} catch (Exception $e) {
    ajax::error(displayExeption($e), $e->getCode());
}
