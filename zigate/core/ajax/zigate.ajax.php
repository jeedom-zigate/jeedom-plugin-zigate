<?php

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
	}
	elseif ($action == 'refresh_eqlogic') {
	    $id = intval(init('args')[0]);
	    $eqLogic = zigate::byId($id);
	    $addr = $eqLogic->getLogicalId();
	    $result = zigate::callZiGate('refresh_device', [$addr]);
	    if ($result['success']) {
	        ajax::success();
	    }
	    else {
	        ajax::error('Echec');
	    }
	}
	elseif ($action == 'identify_device') {
	    $id = intval(init('args')[0]);
	    $eqLogic = zigate::byId($id);
	    $addr = $eqLogic->getLogicalId();
	    $result = zigate::callZiGate('identify_device', [$addr,10]);
	    if ($result['success']) {
	        ajax::success();
	    }
	    else {
	        ajax::error('Echec');
	    }
	}
    else {
        $result = zigate::callZiGate($action, init('args'));
        if ($result['success']) {    
            ajax::success();
        }
        else {
            ajax::error('Echec');
        }
    }

    throw new Exception(__('Aucune méthode correspondante à : ', __FILE__) . init('action'));
    /*     * *********Catch exeption*************** */
} catch (Exception $e) {
    ajax::error(displayExeption($e), $e->getCode());
}

?>