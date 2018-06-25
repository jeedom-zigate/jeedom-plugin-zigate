<?php

/* * ***************************Includes********************************* */
require_once dirname(__FILE__) . '/../../../../core/php/core.inc.php';

class zigate extends eqLogic {
    
    public static $_excludeOnSendPlugin = array('.zigate.json', '.key', '_config.yml');

    public static function callZiGate($action, $args='') {
        log::add('zigate', 'debug', 'callZiGate ' . print_r($action,true) . ' ' .print_r($args,true));
        $apikey = jeedom::getApiKey('zigate');
        $sock = 'unix://' . jeedom::getTmpFolder('zigate') . '/daemon.sock';
        $fp = stream_socket_client($sock, $errno, $errstr);
        $result = '';
        if ($fp){
            $query = array();
            $query['action'] = $action;
            $query['args'] = $args;
            $query['apikey'] = $apikey;
        fwrite($fp, json_encode($query));
        while (!feof($fp)) {
            $result .= fgets($fp, 1024);
        }
        fclose($fp);
        }
        $result = (is_json($result)) ? json_decode($result, true) : $result;
        log::add('zigate', 'debug', 'result callZiGate '.print_r($result,true));
		return $result;
	}

    public static function syncEqLogicWithZiGate() {
        $results = zigate::callZiGate('devices');
		$findDevice = array();
		foreach ($results['result'] as $result) {
		    $addr = zigate::syncDevice($result);
		    array_push($findDevice, $addr);
		}
        foreach (self::byType('zigate') as $eqLogic) {
            if (!in_array($eqLogic->getLogicalId(), $findDevice)) {
					$eqLogic->remove();
				}
			}
    }
    public static function syncDevice($device) {
        $addr = $device['info']['addr'];
        $eqLogic = self::byLogicalId($addr, 'zigate');
        if (!is_object($eqLogic)) {
            $eqLogic = new eqLogic();
            $eqLogic->setEqType_name('zigate');
            $eqLogic->setIsEnable(1);
            $eqLogic->setLogicalId($addr);
            foreach ($device['info'] as $info => $value) {
                $eqLogic->setConfiguration($info, $value);
            }
            $eqLogic->setName('Device ' . $addr);
            $eqLogic->setIsVisible(1);
            $eqLogic->setStatus('lastCommunication', $device['info']['last_seen']);
            $eqLogic->setStatus('rssi', $device['info']['rssi']);
            $eqLogic->save();
            $eqLogic = self::byId($eqLogic->getId());
            $eqLogic->createCommands($device);
        } else {
            foreach ($device['info'] as $info => $value) {
                $eqLogic->setConfiguration($info, $value);
            }
            $eqLogic->setStatus('lastCommunication', $device['info']['last_seen']);
            $eqLogic->setStatus('rssi', $device['info']['rssi']);
            $eqLogic->save();
            $eqLogic->createCommands($device);
        }
        return $addr;
    }
    public static function removeDevice($addr) {
        $eqLogic = self::byLogicalId($addr, 'zigate');
        $eqLogic->remove();
    }
    
    public function getProperty($name){
        $cmds = $this->searchCmdByConfiguration('property');
        foreach ($cmds as $cmd) {
            if ($cmd->getConfiguration('property') == $name){
                return $cmd->execCmd();
            }
        }
    }
    
    public function evaluateBattery($value){
        $percent = $this->getProperty('battery_percent');
        if (!$percent){
            $power_source = $this->getProperty('power_source');
            if (!$power_source){
                $power_source = 3;
            }
            $power_source = floatval($power_source);
            $power_end = 0.75*$power_source;
            $percent = ($value-$power_end)*100/($power_source-$power_end);
            $percent = intval($percent);
        }
        $this->batteryStatus($percent);
    }
    
    public function createCommands($device) {
      	log::add('zigate', 'debug', 'createcommands for '.$this->getLogicalId());
      	$created_commands = [];
      	foreach ($device['endpoints'] as $endpoint) {
      	    $endpoint_id = $endpoint['endpoint'];
      	    $endpoint_device = $endpoint['device'];
      	    $endpoint_profile = $endpoint['profile'];
      	    foreach ($endpoint['clusters'] as $cluster){
      	        $cluster_id = $cluster['cluster'];
      	        foreach ($cluster['attributes'] as $attribute){
      	            $value = $attribute['data'];
      	            if (isset($attribute['value'])){
      	                $value = $attribute['value'];
      	            }
      	            // hmm, it's a array, we supposed a dict
      	            // so create a command per value
      	            if (gettype($value) == 'array'){
      	                foreach($value as $name => $val){
      	                    $fake_attribute = $attribute;
      	                    $fake_attribute['name'] = $name;
      	                    $fake_attribute['value'] = $val;
      	                    $fake_attribute['attribute'] = $attribute['attribute'].'.'.$name;
      	                    $key = $this->_create_command($endpoint_id, $cluster_id, $fake_attribute);
      	                    array_push($created_commands, $key);
      	                }
      	            }
      	            else {
      	             $key = $this->_create_command($endpoint_id, $cluster_id, $attribute);
      	             array_push($created_commands, $key);
      	            }
                  
                }
      	    }
        
        }
        
        $available_actions = zigate::callZiGate('available_actions', [$this->getLogicalId()]);
        foreach($available_actions['result'] as $endpoint_id => $actions){
            foreach($actions as $action){
                switch ($action){
                    case 'onoff':
                        $key = $this->_create_action($endpoint_id, $action, 'off', 'other', 0);
                        array_push($created_commands, $key);
                        
                        $key = $this->_create_action($endpoint_id, $action, 'on', 'other', 1);
                        array_push($created_commands, $key);
                        
                        $key = $this->_create_action($endpoint_id, $action, 'toggle', 'other', 2);
                        array_push($created_commands, $key);
                        break;
                    case 'level':
                        $key = $this->_create_action($endpoint_id, $action, 'level', 'slider');
                        array_push($created_commands, $key);
                        break;
                    case 'color':
                        $key = $this->_create_action($endpoint_id, $action, 'color', 'color');
                        array_push($created_commands, $key);
                        break;
                    case 'temperature':
                        $key = $this->_create_action($endpoint_id, $action, 'temperature', 'slider');
                        array_push($created_commands, $key);
                        break;
                    case 'hue':
                        $key = $this->_create_action($endpoint_id, $action, 'hue', 'color');
                        array_push($created_commands, $key);
                        break;
                }
            }
        }
        $key = $this->_create_action(0, 'refresh', 'refresh', 'other');
        array_push($created_commands, $key);
        
        /*
        foreach (self::getCmd() as $cmd) {
            if (!in_array($cmd->getLogicalId(), $created_commands)) {
                $cmd->remove();
            }
        }
        */
    }
    public function _create_command($endpoint_id, $cluster_id, $attribute){
        $attribute_id = $attribute['attribute'];
        log::add('zigate', 'debug', 'create command '.$endpoint_id.'.'.$cluster_id.'.'.$attribute_id);
        $key = $this->getLogicalId().'.'.$endpoint_id.'.'.$cluster_id.'.'.$attribute_id;
        $name = $endpoint_id.'_'.$cluster_id.'_'.$attribute['attribute'];
        if (isset($attribute['name'])){
            $name = $attribute['name'];
        }
        $value = $attribute['data'];
        if (isset($attribute['value'])){
            $value = $attribute['value'];
        }
        
        $cmd_info = $this->getCmd(null, $key);
        if (!is_object($cmd_info)) {
            $cmd_info = new zigateCmd();
            $cmd_info->setLogicalId($key);
            $cmd_info->setType('info');
            $cmd_info->setEqLogic_id($this->getId());
            $cmd_info->setIsHistorized(1);
            if (!isset($attribute['name'])){
                $cmd_info->setIsVisible(0);
                $cmd_info->setIsHistorized(0);
            }
            if ($cluster_id == 0){
                $cmd_info->setIsVisible(0);
            }
            $cmd_info->setName($name);
        }
        if (isset($attribute['unit'])){
            $cmd_info->setUnite($attribute['unit']);
        }
        if (isset($attribute['name'])){
            $cmd_info->setConfiguration('property', $name);
            if ($cluster_id == 0){
                $this->setConfiguration($name,$attribute['value']);
                $this->save();
            }
        }
        $cmd_info->setConfiguration('addr', $this->getLogicalId());
        $cmd_info->setConfiguration('endpoint', $endpoint_id);
        $cmd_info->setConfiguration('cluster', $cluster_id);
        $cmd_info->setConfiguration('attribute', $attribute_id);
        
        if ($name == 'battery'){
            $this->evaluateBattery($value);
        }
        switch (gettype($value)) {
            case 'boolean':
                $cmd_info->setSubType('binary');
                break;
            case 'integer':
                $cmd_info->setSubType('numeric');
                if ($name == 'current_level'){
                    $cmd_info->setConfiguration('minValue', 0);
                    $cmd_info->setConfiguration('maxValue', 100);
                }
                if ($name == 'current_hue'){
                    $cmd_info->setConfiguration('minValue', 0);
                    $cmd_info->setConfiguration('maxValue', 360);
                }
                if ($name == 'current_saturation'){
                    $cmd_info->setConfiguration('minValue', 0);
                    $cmd_info->setConfiguration('maxValue', 100);
                }
                if ($name == 'colour_temperature'){
                    $cmd_info->setConfiguration('minValue', 1700);
                    $cmd_info->setConfiguration('maxValue', 6500);
                }
                break;
            case 'double':
                $cmd_info->setSubType('numeric');
                break;
            default:
                $cmd_info->setSubType('string');
                break;
        }
        //$cmd_info->setValue($value);
        $cmd_info->save();
        $cmd_info->event($value);
        return $key;
    }
    public function _create_action($endpoint_id, $action, $name, $subtype, $value=null){
        log::add('zigate', 'debug', 'create action '.$action.' for endpoint '.$endpoint_id);
        $key = $this->getLogicalId().'.'.$endpoint_id.'.'.$action;
        if (!is_null($value)){
            $key = $key.'.'.$value;
        }
        $cmd_action = $this->getCmd(null, $key);
        if (!is_object($cmd_action)) {
            $cmd_action = new zigateCmd();
            $cmd_action->setLogicalId($key);
            $cmd_action->setEqLogic_id($this->getId());
            $cmd_action->setName($name);
        }
        $cmd_action->setType('action');
        $cmd_action->setSubType($subtype);
        $cmd_action->setConfiguration('addr', $this->getLogicalId());
        $cmd_action->setConfiguration('endpoint', $endpoint_id);
        $cmd_action->setConfiguration('action', $action);
        if (!is_null($value)){
            $cmd_action->setConfiguration('value', $value);
        }
        $cmd_action->save();
        return $key;
    }

    public static function permitJoin() {
        return zigate::callZiGate('permit_join');
    }
    
    public static function reset() {
        return zigate::callZiGate('reset');
    }

	public static function dependancy_info() {
		$return = array();
		$return['state'] = 'nok';
		$return['log'] = 'zigate_update';
		$return['progress_file'] = jeedom::getTmpFolder('zigate') . '/dependance';
		$zigate_version = trim(file_get_contents(dirname(__FILE__) . '/../../resources/zigate_version.txt'));
		$cmd = "/usr/bin/python3 -c 'from distutils.version import LooseVersion;import zigate,sys;sys.exit(LooseVersion(zigate.__version__)<LooseVersion(\"".$zigate_version."\"))' 2>&1";
		exec($cmd,$output,$return_var);
		if ($return_var == 0) {
			$return['state'] = 'ok';
		} 
		return $return;
	}

	public static function dependancy_install() {
		log::remove(__CLASS__ . '_update');
		return array('script' => dirname(__FILE__) . '/../../resources/install_#stype#.sh ' . jeedom::getTmpFolder('zigate') . '/dependance', 'log' => log::getPathToLog(__CLASS__ . '_update'));
	}

	public static function deamon_info() {
		$return = array();
		$return['state'] = 'nok';
		$pid_file = jeedom::getTmpFolder('zigate') . '/daemon.pid';
		if (file_exists($pid_file)) {
			if (posix_getsid(trim(file_get_contents($pid_file)))) {
				$return['state'] = 'ok';
			} else {
				shell_exec(system::getCmdSudo() . 'rm -rf ' . $pid_file . ' 2>&1 > /dev/null');
			}
		}
        
		$return['launchable'] = 'ok';
		$port = config::byKey('port', 'zigate');
		$host = config::byKey('host', 'zigate');
		if (zigate::dependancy_info()['state'] == 'nok'){
		    $cache = cache::byKey('dependancy' . 'zigate');
		    $cache->remove();
		    $return['launchable'] = 'nok';
		    $return['launchable_message'] = __('Veuillez (ré-)installer les dépendances', __FILE__);
		}
		else {
		    if (!$host){
        		if (@!file_exists($port) && $port != 'auto') {
        		  $return['launchable'] = 'nok';
        		  $return['launchable_message'] = __('Le port n\'est pas configuré ou la zigate n\'est pas connecté.', __FILE__);
        		}
        		elseif ($port != 'auto') {
        		  exec(system::getCmdSudo() . 'chmod 777 ' . $port . ' > /dev/null 2>&1');
                }
		    }
		}
		return $return;
	}

	public static function deamon_start($_debug = false) {
		self::deamon_stop();
		$deamon_info = self::deamon_info();
		if ($deamon_info['launchable'] != 'ok') {
			throw new Exception(__('Veuillez vérifier la configuration', __FILE__));
		}
		$port = config::byKey('port', 'zigate');
		$host = config::byKey('host', 'zigate');
		$sharedata = config::byKey('sharedata', 'zigate');
		$zigate_path = dirname(__FILE__) . '/../../resources';
        $callback = network::getNetworkAccess('internal', 'proto:127.0.0.1:port:comp') . '/plugins/zigate/core/php/jeeZiGate.php';

		$cmd = '/usr/bin/python3 ' . $zigate_path . '/zigated/zigated.py ';
		if ($host){
		    $cmd .= ' --device ' . $host;
		}
		else {
		  $cmd .= ' --device ' . $port;
		}
		$cmd .= ' --loglevel ' . log::convertLogLevel(log::getLogLevel('zigate'));
		$cmd .= ' --apikey ' . jeedom::getApiKey('zigate');
		$cmd .= ' --pid ' . jeedom::getTmpFolder('zigate') . '/daemon.pid';
        $cmd .= ' --socket ' . jeedom::getTmpFolder('zigate') . '/daemon.sock';
        $cmd .= ' --callback ' . $callback;
        $cmd .= ' --sharedata ' . $sharedata;

		log::add('zigate', 'info', 'Lancement démon zigate : ' . $cmd);
		exec($cmd . ' >> ' . log::getPathToLog('zigate') . ' 2>&1 &');
		$i = 0;
		while ($i < 5) {
			$deamon_info = self::deamon_info();
			if ($deamon_info['state'] == 'ok') {
				break;
			}
			sleep(1);
			$i++;
		}
		if ($i >= 5) {
			log::add('zigate', 'error', 'Impossible de lancer le démon zigate, relancer le démon en debug et vérifiez la log', 'unableStartDeamon');
			return false;
		}
		message::removeAll('zigate', 'unableStartDeamon');
		log::add('zigate', 'info', 'Démon zigate lancé');
	}

	public static function deamon_stop() {
		$deamon_info = self::deamon_info();
		$pid_file = jeedom::getTmpFolder('zigate') . '/daemon.pid';
		if (file_exists($pid_file)) {
			$pid = intval(trim(file_get_contents($pid_file)));
			system::kill($pid);
		}
		$i = 0;
		while ($i < 5) {
		    $deamon_info = self::deamon_info();
		    if ($deamon_info['state'] == 'nok') {
		        break;
		    }
		    sleep(1);
		    $i++;
		}
		if ($i >= 5) {
		    log::add('zigate', 'error', 'Impossible d\'arrêter le démon zigate, tuons-le');
		    system::kill('zigated.py');
		}
      	
	}
	
	public function getImage() {
	    $filename = $this->getConfiguration('type') . '.jpg';
	    $filename = str_replace(' ','_',$filename);
	    $path = dirname(__FILE__). '/../../images/'. $filename;
	    if (file_exists($path)){
	        return 'plugins/zigate/images/' . $filename;
	    }
	    $plugin = plugin::byId($this->getEqType_name());
	    return $plugin->getPathImgIcon();
	}
    /*     * *************************Attributs****************************** */



    /*     * ***********************Methode static*************************** */

    /*
     * Fonction exécutée automatiquement toutes les minutes par Jeedom
      public static function cron() {

      }
     */


    /*
     * Fonction exécutée automatiquement toutes les heures par Jeedom
      public static function cronHourly() {

      }
     */

    /*
     * Fonction exécutée automatiquement tous les jours par Jeedom
      public static function cronDayly() {

      }
     */



    /*     * *********************Méthodes d'instance************************* */

    public function preInsert() {
        
    }

    public function postInsert() {
        
    }

    public function preSave() {
        
    }

    public function postSave() {
        
    }

    public function preUpdate() {
        
    }

    public function postUpdate() {
        
    }

    public function preRemove() {
        zigate::callZiGate('remove_device', [$this->getLogicalId()]);
    }

    public function postRemove() {
        
    }

    /*
     * Non obligatoire mais permet de modifier l'affichage du widget si vous en avez besoin
      public function toHtml($_version = 'dashboard') {

      }
     */

    /*
     * Non obligatoire mais ca permet de déclencher une action après modification de variable de configuration
    public static function postConfig_<Variable>() {
    }
     */

    /*
     * Non obligatoire mais ca permet de déclencher une action avant modification de variable de configuration
    public static function preConfig_<Variable>() {
    }
     */

    /*     * **********************Getteur Setteur*************************** */
}

class zigateCmd extends cmd {
    /*     * *************************Attributs****************************** */


    /*     * ***********************Methode static*************************** */
    

    /*     * *********************Methode d'instance************************* */
    public function preSave() {
        # on verifie si le nom n'est pas déjà utilisé, si oui, on supprime l'ancien
        $result = zigateCmd::byEqLogicIdCmdName($this->getEqLogic_id(), $this->getName());
        if (is_object($result)){
            if ($this->getLogicalId() != $result->getLogicalId()){
                $result->remove();
            }
        }
    }

    public function execute($_options = array()) {
        if ($this->getType() != 'action') {
            return;
        }
        $addr = $this->getConfiguration('addr');
        $endpoint = $this->getConfiguration('endpoint');
        $action = $this->getConfiguration('action');
        $value = $this->getConfiguration('value');
        switch ($this->getSubType()) {
            case 'slider':
                $value = intval($_options['slider']);
                break;
            case 'color':
                $value = $_options['color'];
                break;
        }
        switch ($action){
            case 'onoff':
                zigate::CallZiGate('action_onoff',[$addr, $endpoint, $value]);
                break;
            case 'level':
                $onoff = 1;
                if ($value == 0){
                    $onoff = 0;
                }
                zigate::CallZiGate('action_move_level_onoff',[$addr, $endpoint, $onoff, $value]);
                break;
            case 'lock':
                zigate::CallZiGate('action_lock',[$addr, $endpoint, $value]);
                break;
            case 'color':
                zigate::CallZiGate('actions_move_colour_hex',[$addr, $endpoint, $value]);
                break;
            case 'temperature':
                // min 1700 max 6500
                $value = intval(($value*(6500-1700)/100)+1700);
                zigate::CallZiGate('actions_move_temperature',[$addr, $endpoint, $value]);
                break;
            case 'hue':
                zigate::CallZiGate('actions_move_hue_hex',[$addr, $endpoint, $value]);
                break;
            case 'refresh':
                zigate::callZiGate('refresh_device', [$addr]);
                break;
        }
    }

    /*     * **********************Getteur Setteur*************************** */
}

?>
