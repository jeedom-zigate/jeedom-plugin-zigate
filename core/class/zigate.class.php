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

 /*
  * Because extends a Jeedom class...
  * phpcs:disable PSR1.Classes.ClassDeclaration.MissingNamespace
  * phpcs:disable PSR1.Classes.ClassDeclaration.MultipleClasses
  * phpcs:disable PSR1.Files.SideEffects
  * phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
  * phpcs:disable PSR2.Classes.PropertyDeclaration.Underscore
  * phpcs:disable PSR2.Methods.MethodDeclaration.Underscore
  * phpcs:disable Squiz.Classes.ValidClassName.NotCamelCaps
  */
require_once dirname(__FILE__) . '/../../../../core/php/core.inc.php';

/**
 * Define a Zigate eqLogic.
 */
class zigate extends eqLogic
{

    public static $_excludeOnSendPlugin = ['.zigate.json', '.key', '_config.yml'];

    /**
     * Call the zigate Python daemon.
     *
     * @param  string $action Action calling.
     * @param  string $args   Other arguments.
     * @return array  Result of the callZiGate.
     */
    public static function callZiGate($action, $args = '')
    {
        log::add('zigate', 'debug', 'callZiGate ' . print_r($action, true) . ' ' .print_r($args, true));
        $apikey = jeedom::getApiKey('zigate');
        $sock = 'unix://' . jeedom::getTmpFolder('zigate') . '/daemon.sock';
        $fp = stream_socket_client($sock, $errno, $errstr);
        $result = '';

        if ($fp) {
            $query = [
                'action' => $action,
                'args' => $args,
                'apikey' => $apikey
            ];
            fwrite($fp, json_encode($query));
            while (!feof($fp)) {
                $result .= fgets($fp, 1024);
            }
            fclose($fp);
        }
        $result = (is_json($result)) ? json_decode($result, true) : $result;
        log::add('zigate', 'debug', 'result callZiGate '.print_r($result, true));

        return $result;
    }


    /**
     * Create/Update ZiGate object
     */
    public static function createZiGate()
    {
        $eqLogic = self::byLogicalId('zigate', 'zigate');
        if (!is_object($eqLogic)) {
            log::add('zigate', 'debug', 'eqLogic ZiGate absent, création');
            $eqLogic = new eqLogic();
            $eqLogic->setEqType_name('zigate');
            $eqLogic->setIsEnable(1);
            $eqLogic->setLogicalId('zigate');
            $eqLogic->setName('ZiGate');
            $eqLogic->setIsVisible(1);
            $eqLogic->save();
            $eqLogic = self::byId($eqLogic->getId());
            $eqLogic->setConfiguration('type', 'ZiGate');
            $eqLogic->setConfiguration('manufacturer', 'zigate.fr');
        }
        $addr = zigate::callZiGate('addr')['result'];
        $ieee = zigate::callZiGate('ieee')['result'];
        $eqLogic->setConfiguration('addr', $addr);
        $eqLogic->setConfiguration('ieee', $ieee);
        $eqLogic->save();
    }


    /**
     * Sync all eqLogic.
     */
    public static function syncEqLogicWithZiGate()
    {
        log::add('zigate', 'debug', 'Création de l\'objet ZiGate');
        zigate::createZiGate();
        
        log::add('zigate', 'debug', 'Synchronisation des équipements entre le démon et jeedom');
        $results = zigate::callZiGate('devices');
        $findDevice = ['zigate'];
        foreach ($results['result'] as $result) {
            $ieee = zigate::syncDevice($result);
            array_push($findDevice, $ieee);
        }

        foreach (self::byType('zigate') as $eqLogic) {
            if (!in_array($eqLogic->getLogicalId(), $findDevice)) {
//                 $eqLogic->remove();
                $eqLogic->setIsEnable(0);
                $eqLogic->save();
            }
        }
        
        log::add('zigate', 'debug', 'Désactivation des équipements manquants');
        $results = zigate::callZiGate('get_missing');
        foreach ($results['result'] as $device) {
            $ieee = $device['info']['ieee'];
            $eqLogic = self::byLogicalId($ieee, 'zigate');
            if (is_object($eqLogic)) {
//                 $eqLogic->setIsEnable(0);
//                 $eqLogic->save();
                $humanName = $eqLogic->getHumanName();
//                 message::add('zigate', 'L\'équipement '.$humanName.' semble manquant, il a été désactivé.');
                log::add('zigate', 'info', 'L\'équipement '.$humanName.' semble manquant.');
            }
        }
    }


    /**
     * Sync a specific eqLogic.
     *
     * @param  array  $device Device array definition.
     * @return string Device address (Id).
     */
    public static function syncDevice($device)
    {
        $addr = $device['info']['addr'];
        $ieee = $device['info']['ieee'];
        if (!$ieee) {
            log::add('zigate', 'error', 'L\'IEEE de l\'équipement '.$addr.' est absent, vous devriez refaire l\'association');
            message::add('zigate', 'L\'IEEE de l\'équipement '.$addr.' est absent, vous devriez refaire l\'association');
            return;
        }
        log::add('zigate', 'debug', 'Recherche eqLogic '.$ieee);
        $eqLogic = self::byLogicalId($ieee, 'zigate');
        if (!is_object($eqLogic)) {
            /* Migration addr => IEEE */
            log::add('zigate', 'debug', 'eqLogic absent recherche eqLogic '.$addr.' pour migration');
            $eqLogic = self::byLogicalId($addr, 'zigate');
            if (is_object($eqLogic)) {
                log::add('zigate', 'debug', 'Migration - Equipement '.$eqLogic->getId().' : '.$addr.' => '.$ieee);
                $eqLogic->setLogicalId($ieee);
                $eqLogic->save();
                $eqLogic = self::byId($eqLogic->getId());
                if ($ieee == $eqLogic->getLogicalId()) {
                    log::add('zigate', 'debug', 'Migration ok '.$addr.' => '.$ieee);
                } else {
                    log::add('zigate', 'error', 'Migration échec pour '.$addr.' '.$eqLogic->getLogicalId().' != '.$ieee);
                }
                
                $cmds = cmd::byEqLogicId($eqLogic->getId());
                log::add('zigate', 'debug', 'Migration des commandes : '.count($cmds));
                foreach ($cmds as $cmd) {
                    $key = $cmd->getLogicalId();
                    $new_key = str_replace($addr, $ieee, $key);
                    log::add('zigate', 'debug', 'Migration - Commande '.$cmd->getId().' : '.$key.' => '.$new_key);
                    $cmd->setLogicalId($new_key);
                    $cmd->save();
                    $cmd = zigateCmd::byId($cmd->getId());
                    if ($new_key == $cmd->getLogicalId()) {
                        log::add('zigate', 'debug', 'Migration ok Commande '.$cmd->getId().' : '.$key.' => '.$new_key);
                    } else {
                        log::add('zigate', 'error', 'Migration échec Commande '.$cmd->getId().' '.$cmd->getLogicalId().' != '.$new_key);
                    }
                }
            }
        }
        if (!is_object($eqLogic)) {
            log::add('zigate', 'debug', 'eqLogic absent, création');
            $eqLogic = new eqLogic();
            $eqLogic->setEqType_name('zigate');
            $eqLogic->setIsEnable(1);
            $eqLogic->setLogicalId($ieee);
            $eqLogic->setName('Device ' . $ieee . ' (' . $addr . ')');
            $eqLogic->setIsVisible(1);
            $eqLogic->save();
            $eqLogic = self::byId($eqLogic->getId());
        }

        foreach ($device['info'] as $info => $value) {
            $eqLogic->setConfiguration($info, $value);
        }

        $eqLogic->setStatus('lastCommunication', $device['info']['last_seen']);
        $eqLogic->setStatus('lqi', $device['info']['lqi']);
        $eqLogic->save();
        $eqLogic->createCommands($device);

        return $ieee;
    }


    /**
     * Remove a device.
     *
     * @param  string $ieee Device address (Id).
     * @see https://github.com/Jeedom-Zigate/jeedom-plugin-zigate/issues/30
     */
    public static function removeDevice($ieee)
    {
        $eqLogic = self::byLogicalId($ieee, 'zigate');
        $eqLogic->remove();
    }


    /**
     * Get property by name.
     *
     * @param  string $name The propertiy's name.
     * @return array  The cmd return.
     */
    public function getProperty($name)
    {
        $cmds = $this->searchCmdByConfiguration('property');
        foreach ($cmds as $cmd) {
            if ($cmd->getConfiguration('property') == $name) {
                return $cmd->execCmd();
            }
        }
    }


    /**
     * Calculate bettery percent from voltage.
     *
     * @param  float $value Voltage.
     * @return int   Battery percentage.
     */
    public function evaluateBattery($value)
    {
        $percent = $this->getProperty('battery_percent');
        if (!$percent) {
            $power_source = $this->getProperty('power_source');
            if (!$power_source) {
                $power_source = 3;
            }
            if ($power_source == 3) {
                $power_source = 3.1;
            }
            $power_source = floatval($power_source);
            $power_end = 0.9*$power_source;
            $percent = ($value-$power_end)*100/($power_source-$power_end);
            $percent = intval($percent);
        }

        $this->batteryStatus($percent);
        $fake_attribute = array('name' => 'battery_level',
            'value' => $percent,
            'attribute' => 'battery_level',
            'unit' => '%'
        );
        $this->_create_command(0, 0, $fake_attribute);
    }


    /**
     * Create command.
     *
     * @param  array $device Device definition.
     */
    public function createCommands($device)
    {
        log::add('zigate', 'debug', 'createcommands for '.$this->getLogicalId());
        $created_commands = [];
        foreach ($device['endpoints'] as $endpoint) {
            $endpoint_id = $endpoint['endpoint'];
            $endpoint_device = $endpoint['device'];
            $endpoint_profile = $endpoint['profile'];
            foreach ($endpoint['clusters'] as $cluster) {
                $cluster_id = $cluster['cluster'];
                foreach ($cluster['attributes'] as $attribute) {
                    $keys = $this->update_command($endpoint_id, $cluster_id, $attribute);
                    $created_commands = array_merge($created_commands, $keys);
                }
            }
        }

        $addr = $this->getConfiguration('addr');
        $available_actions = zigate::callZiGate('available_actions', [$addr]);
        foreach ($available_actions['result'] as $endpoint_id => $actions) {
            foreach ($actions as $action) {
                switch ($action) {
                    case 'onoff':
                        $key = $this->_create_action($endpoint_id, $action, 'off', 'other', 0);
                        array_push($created_commands, $key);

                        $key = $this->_create_action($endpoint_id, $action, 'on', 'other', 1);
                        array_push($created_commands, $key);

                        $key = $this->_create_action($endpoint_id, $action, 'toggle', 'other', 2);
                        array_push($created_commands, $key);
                        break;
                    case 'level':
                        $key = $this->_create_action($endpoint_id, $action, 'level', 'slider', [0, 100]);
                        array_push($created_commands, $key);
                        
                        $key = $this->_create_action($endpoint_id, 'stop', 'stop', 'other');
                        array_push($created_commands, $key);
                        break;
                    case 'color':
                        $key = $this->_create_action($endpoint_id, $action, 'color', 'color');
                        array_push($created_commands, $key);
                        break;
                    case 'temperature':
                        $key = $this->_create_action($endpoint_id, $action, 'temperature', 'slider', [153, 500]);
                        array_push($created_commands, $key);
                        break;
                    case 'hue':
                        $key = $this->_create_action($endpoint_id, $action, 'hue', 'color');
                        array_push($created_commands, $key);
                        break;
                    case 'cover':
                        $key = $this->_create_action($endpoint_id, $action, 'open', 'other', 0);
                        array_push($created_commands, $key);
                        
                        $key = $this->_create_action($endpoint_id, $action, 'close', 'other', 1);
                        array_push($created_commands, $key);
                        
                        $key = $this->_create_action($endpoint_id, $action, 'stop', 'other', 2);
                        array_push($created_commands, $key);
                        break;
                    case 'thermostat':
                        $key = $this->_create_action($endpoint_id, 'Occupied_temperature_setpoint', 'Occupied temperature setpoint', 'slider', [6, 28]);
                        array_push($created_commands, $key);
                        
                        $key = $this->_create_action($endpoint_id, 'System_mode', 'off', 'other', 0);
                        array_push($created_commands, $key);
                        $key = $this->_create_action($endpoint_id, 'System_mode', 'cooling', 'other', 3);
                        array_push($created_commands, $key);
                        $key = $this->_create_action($endpoint_id, 'System_mode', 'heating', 'other', 4);
                        array_push($created_commands, $key);
                        break;
                    case 'ias':
                        $key = $this->_create_action($endpoint_id, 'ias_off', 'off', 'other', 0);
                        array_push($created_commands, $key);
                        
                        $key = $this->_create_action($endpoint_id, 'ias_warning', 'buzzer_2sec', 'other', 2);
                        array_push($created_commands, $key);
                        
                        $key = $this->_create_action($endpoint_id, 'ias_warning', 'buzzer_10sec', 'other', 10);
                        array_push($created_commands, $key);
                        
                        $key = $this->_create_action($endpoint_id, 'ias_warning', 'buzzer_60sec', 'other', 60);
                        array_push($created_commands, $key);
                        
                        $key = $this->_create_action($endpoint_id, 'ias_squawk', 'Disarmed', 'other', 'disarmed');
                        array_push($created_commands, $key);
                        
                        $key = $this->_create_action($endpoint_id, 'ias_squawk', 'Armed', 'other', 'armed');
                        array_push($created_commands, $key);

                        break;
                    case 'lock':
                        $key = $this->_create_action($endpoint_id, $action, 'lock', 'other', 0);
                        array_push($created_commands, $key);

                        $key = $this->_create_action($endpoint_id, $action, 'unlock', 'other', 1);
                        array_push($created_commands, $key);
                        break;
                }
            }
        }

        $key = $this->_create_action(0, 'refresh', 'refresh', 'other');
        array_push($created_commands, $key);
    }


    /**
     * Update command
     *
     * @param  string $endpoint_id End point Id.
     * @param  string $cluster_id  Cluster Id.
     * @param  string $attribute   Attribute name.
     * @return array  Udated command informations.
     */
    public function update_command($endpoint_id, $cluster_id, $attribute)
    {
        $created_commands = [];
        $value = '';
        if (isset($attribute['data'])) {
            $value = $attribute['data'];
        }
        if (isset($attribute['value'])) {
            $value = $attribute['value'];
        }

        /*
         * hmm, it's a array, we supposed a dict
         * so create a command per value
         */
        if (gettype($value) == 'array') {
            foreach ($value as $name => $val) {
                $fake_attribute = $attribute;
                $fake_attribute['name'] = $name;
                $fake_attribute['value'] = $val;
                $fake_attribute['attribute'] = $attribute['attribute'].'.'.$name;
                $key = $this->_create_command($endpoint_id, $cluster_id, $fake_attribute);
                array_push($created_commands, $key);
            }
        } else {
            $key = $this->_create_command($endpoint_id, $cluster_id, $attribute);
            array_push($created_commands, $key);
        }
        return $created_commands;
    }


    /**
     * Create a command.
     *
     * @param  string $endpoint_id End point Id.
     * @param  string $cluster_id  Cluster Id.
     * @param  string $attribute   Attribute name.
     * @return string Command key.
     */
    public function _create_command($endpoint_id, $cluster_id, $attribute)
    {
        $attribute_id = $attribute['attribute'];
        log::add('zigate', 'debug', 'create command '.$endpoint_id.'.'.$cluster_id.'.'.$attribute_id);
        $key = $this->getLogicalId().'.'.$endpoint_id.'.'.$cluster_id.'.'.$attribute_id;
        $name = $endpoint_id.'_'.$cluster_id.'_'.$attribute['attribute'];
        if (isset($attribute['name'])) {
            $name = $attribute['name'];
        }

        $value = '';
        if (isset($attribute['data'])) {
            $value = $attribute['data'];
        }
        if (isset($attribute['value'])) {
            $value = $attribute['value'];
        }

        $cmd_info = $this->getCmd(null, $key);
        if (!is_object($cmd_info)) {
            log::add('zigate', 'debug', 'Command doesn\'t exist, create it');
            $cmd_info = new zigateCmd();
            $cmd_info->setLogicalId($key);
            $cmd_info->setConfiguration('addr', $this->getConfiguration('addr'));
            $cmd_info->setConfiguration('ieee', $this->getLogicalId());
            $cmd_info->setConfiguration('endpoint', $endpoint_id);
            $cmd_info->setConfiguration('cluster', $cluster_id);
            $cmd_info->setConfiguration('attribute', $attribute_id);
            $cmd_info->setType('info');
            $cmd_info->setEqLogic_id($this->getId());
            $cmd_info->setIsHistorized(0);
            if (!isset($attribute['name'])) {
                $cmd_info->setIsVisible(0);
                $cmd_info->setIsHistorized(0);
            }
            // by default hide "basic" clusters
            if ($cluster_id < 5) {
                $cmd_info->setIsVisible(0);
            } else {
                $cmd_info->setIsHistorized(1);
            }
            $cmd_info->setName($name);
            
            if (isset($attribute['unit'])) {
                $cmd_info->setUnite($attribute['unit']);
            }

            if (isset($attribute['name'])) {
                $cmd_info->setConfiguration('property', $name);
                if ($cluster_id < 5) {
                    $this->setConfiguration($name, $attribute['value']);
                }
            }

            switch (gettype($value)) {
                case 'boolean':
                    $cmd_info->setSubType('binary');
                    break;
                case 'integer':
                    $cmd_info->setSubType('numeric');
                    if ($name == 'current_level') {
                        $cmd_info->setConfiguration('minValue', 0);
                        $cmd_info->setConfiguration('maxValue', 100);
                    }
                    if ($name == 'current_hue') {
                        $cmd_info->setConfiguration('minValue', 0);
                        $cmd_info->setConfiguration('maxValue', 360);
                    }
                    if ($name == 'current_saturation') {
                        $cmd_info->setConfiguration('minValue', 0);
                        $cmd_info->setConfiguration('maxValue', 100);
                    }
                    if ($name == 'colour_temperature') {
                        $cmd_info->setConfiguration('minValue', 153);
                        $cmd_info->setConfiguration('maxValue', 500);
                    }
                    break;
                case 'double':
                    $cmd_info->setSubType('numeric');
                    break;
                default:
                    $cmd_info->setSubType('string');
                    break;
            }
            $cmd_info->save();
        }
        
        // rename device to add type in name if not renamed
        if ($name == 'type' && $attribute['value'] && $this->getName() == 'Device ' . $this->ieee() . ' (' . $this->addr() . ')') {
            $this->setName($this->getConfiguration('manufacturer').' '.$attribute['value'].' ('.$this->addr().') '.$this->ieee());
        }
        $this->save();
        
        if ($name == 'battery' or $name == 'battery_voltage') {
            $this->evaluateBattery($value);
        } elseif ($name == 'battery_percent') {
            $this->batteryStatus($value);
            $fake_attribute = array('name' => 'battery_level',
                'value' => $value,
                'attribute' => 'battery_level',
                'unit' => '%'
            );
            $this->_create_command(0, 0, $fake_attribute);
        }
        
        if ($value != $cmd_info->getCache('value', '')) {
            $cmd_info->event($value);
        }

        return $key;
    }
    
    public function ieee()
    {
        return $this->getConfiguration('ieee');
    }
    
    public function addr()
    {
        return $this->getConfiguration('addr');
    }

    /**
     * Create an action.
     *
     * @param  string $endpoint_id End point Id.
     * @param  string $action      Action name.
     * @param  string $name        Command name.
     * @param  string $subtype     Subtype name.
     * @param  string $value       Value.
     * @return string Action key.
     */
    public function _create_action($endpoint_id, $action, $name, $subtype, $value = null)
    {
        log::add('zigate', 'debug', 'create action '.$action.' for endpoint '.$endpoint_id);
        $key = $this->getLogicalId().'.'.$endpoint_id.'.'.$action;
        if (!is_null($value) and $subtype != 'slider') {
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
        $cmd_action->setConfiguration('addr', $this->getConfiguration('addr'));
        $cmd_action->setConfiguration('ieee', $this->getLogicalId());
        $cmd_action->setConfiguration('endpoint', $endpoint_id);
        $cmd_action->setConfiguration('action', $action);

        if (!is_null($value)) {
            if ($subtype == 'slider') {
                $cmd_action->setConfiguration('minValue', $value[0]);
                $cmd_action->setConfiguration('maxValue', $value[1]);
            } else {
                $cmd_action->setConfiguration('value', $value);
            }
        }
        $cmd_action->save();

        return $key;
    }


    /**
     * Send "permit_join" to ZiGate.
     *
     * @return array ZiGate called commmand return.
     */
    public static function permitJoin()
    {
        return zigate::callZiGate('permit_join');
    }


    /**
     * Send "reset" to ZiGate.
     *
     * @return array ZiGate called commmand return.
     */
    public static function reset()
    {
        return zigate::callZiGate('reset');
    }


    /**
     * Get Zigate lib dependancy information.
     *
     * @return array Python3 command return.
     */
    public static function dependancy_info()
    {
        $return = [
            'state' => 'nok',
            'log' => 'zigate_update',
            'progress_file' => jeedom::getTmpFolder('zigate') . '/dependance'
        ];

        $zigate_version = trim(file_get_contents(dirname(__FILE__) . '/../../resources/zigate_version.txt'));
        $cmd = "/usr/bin/python3 -c 'from distutils.version import LooseVersion;import zigate,sys;" .
              "sys.exit(LooseVersion(zigate.__version__)<LooseVersion(\"".$zigate_version."\"))' 2>&1";
        exec($cmd, $output, $return_var);

        if ($return_var == 0) {
            $return['state'] = 'ok';
        }

        return $return;
    }


    /**
     * Install dependancies.
     *
     * @return array Shell script command return.
     */
    public static function dependancy_install()
    {
        log::remove(__CLASS__ . '_update');
        return [
            'script' => dirname(__FILE__) . '/../../resources/install_#stype#.sh ' . jeedom::getTmpFolder('zigate') . '/dependance',
            'log' => log::getPathToLog(__CLASS__ . '_update')
        ];
    }


    /**
     * Return information (status) about daemon.
     *
     * @return array Shell command return.
     */
    public static function deamon_info()
    {
        $return = ['state' => 'nok'];
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
        if (zigate::dependancy_info()['state'] == 'nok') {
            $cache = cache::byKey('dependancy' . 'zigate');
            $cache->remove();
            $return['launchable'] = 'nok';
            $return['launchable_message'] = __('Veuillez (ré-)installer les dépendances', __FILE__);
        } else {
            if (!$host) {
                if (@!file_exists($port) && $port != 'auto') {
                    $return['launchable'] = 'nok';
                    $return['launchable_message'] = __('Le port n\'est pas configuré ou la zigate n\'est pas connecté.', __FILE__);
                } elseif ($port != 'auto') {
                    exec(system::getCmdSudo() . 'chmod 777 ' . $port . ' > /dev/null 2>&1');
                }
            }
        }
        return $return;
    }


    /**
     * Start python daemon.
     *
     * @return array Shell command return.
     */
    public static function deamon_start($_debug = false)
    {
        self::deamon_stop();
        $deamon_info = self::deamon_info();
        if ($deamon_info['launchable'] != 'ok') {
            throw new Exception(__('Veuillez vérifier la configuration', __FILE__));
        }
        $port = config::byKey('port', 'zigate');
        $host = config::byKey('host', 'zigate');
        $gpio = config::byKey('gpio', 'zigate');
        $enable_led = config::byKey('enable_led', 'zigate');
        $channel = config::byKey('channel', 'zigate');
        $sharedata = config::byKey('sharedata', 'zigate');
        $zigate_path = dirname(__FILE__) . '/../../resources';
        $callback = network::getNetworkAccess('internal', 'proto:127.0.0.1:port:comp') . '/plugins/zigate/core/php/jeeZiGate.php';

        $cmd = '/usr/bin/python3 ' . $zigate_path . '/zigated/zigated.py ';
        if ($host) {
            $cmd .= ' --device ' . $host;
        } else {
            $cmd .= ' --device ' . $port;
        }
        $cmd .= ' --gpio ' . $gpio;
        $cmd .= ' --loglevel ' . log::convertLogLevel(log::getLogLevel('zigate'));
        $cmd .= ' --apikey ' . jeedom::getApiKey('zigate');
        $cmd .= ' --pid ' . jeedom::getTmpFolder('zigate') . '/daemon.pid';
        $cmd .= ' --socket ' . jeedom::getTmpFolder('zigate') . '/daemon.sock';
        $cmd .= ' --callback ' . $callback;
        $cmd .= ' --sharedata ' . $sharedata;
        $cmd .= ' --enable_led ' . $enable_led;
        if ($channel) {
            $cmd .= ' --channel ' . $channel;
        }

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


    /**
     * Stop python daemon.
     *
     * @return array Shell command return.
     */
    public static function deamon_stop()
    {
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


    /**
     * Get eqLogic images path.
     *
     * @return string Image path.
     */
    public function getImage()
    {
        $filename = $this->getConfiguration('type') . '.jpg';
        $filename = str_replace(' ', '_', $filename);
        $filename = str_replace('/', '_', $filename);
        $path = dirname(__FILE__). '/../../images/'. $filename;
        if (file_exists($path)) {
            return 'plugins/zigate/images/' . $filename;
        }

        $plugin = plugin::byId($this->getEqType_name());

        return $plugin->getPathImgIcon();
    }


    /**
     * Return plugin version.
     *
     * @return string Version of the plugin.
     */
    public static function getVersion()
    {
        $pluginVersion = 'Error';
        if (!file_exists(dirname(__FILE__) . '/../../plugin_info/info.json')) {
            log::add('zigate', 'warning', 'Pas de fichier info.json');
        }
        $data = json_decode(file_get_contents(dirname(__FILE__) . '/../../plugin_info/info.json'), true);
        if (!is_array($data)) {
            log::add('zigate', 'warning', 'Impossible de décoder le fichier info.json');
        }
        try {
            $pluginVersion = $data['pluginVersion'];
        } catch (\Exception $e) {
            log::add('zigate', 'warning', 'Impossible de récupérer la version.');
        }

        return $pluginVersion;
    }


    /*
     * Fonction exécutée automatiquement toutes les minutes par Jeedom
    public static function cron()
    {
    } */


    /*
     * Fonction exécutée automatiquement toutes les heures par Jeedom
    public static function cronHourly()
    {
    } */

    /*
     * Fonction exécutée automatiquement tous les jours par Jeedom
    public static function cronDayly()
    {
    } */


    /**
     * Before insert.
     */
    public function preInsert()
    {
    }

    /**
     * After insert.
     */
    public function postInsert()
    {
    }

    /**
     * Before saving.
     */
    public function preSave()
    {
    }

    /**
     * After saving.
     */
    public function postSave()
    {
    }

    /**
     * Before update.
     */
    public function preUpdate()
    {
    }

    /**
     * After update.
     */
    public function postUpdate()
    {
    }

    /**
     * After remove.
     */
    public function preRemove()
    {
        zigate::callZiGate('remove_device', [$this->getConfiguration('addr')]);
    }

    /**
     * After remove.
     */
    public function postRemove()
    {
    }

    /*
     * Non obligatoire mais permet de modifier l'affichage du widget si vous en avez besoin
    public function toHtml($_version = 'dashboard')
    {
    } */

    /*
     * Non obligatoire mais ca permet de déclencher une action après modification de variable de configuration
    public static function postConfig_<Variable>()
    {
    } */

    /*
     * Non obligatoire mais ca permet de déclencher une action avant modification de variable de configuration
    public static function preConfig_<Variable>() {
    } */
}


/**
 * Define zigate command extending cmd.
 */
class zigateCmd extends cmd
{
    /**
     * Action before call execute.
     */
    public function preSave()
    {
        // We check if the name is not already used
        // if yes : remove the old one if it's the same endpoint
        // else we rename the new one
        $result = zigateCmd::byEqLogicIdCmdName($this->getEqLogic_id(), $this->getName());
        while (is_object($result) && $this->getId() != $result->getId()) {
            if ($this->getConfiguration('endpoint') != $result->getConfiguration('endpoint')) {
                $this->setName($this->getName() . $this->getConfiguration('endpoint'));
                $result = zigateCmd::byEqLogicIdCmdName($this->getEqLogic_id(), $this->getName());
            } else {
                $result->remove();
            }
        }
    }


    /**
     * Zigate commmand execution.
     *
     * @param  array  $_options Option of the call cmd.
     */
    public function execute($_options = [])
    {
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

        switch ($action) {
            case 'onoff':
                zigate::CallZiGate('action_onoff', [$addr, $endpoint, $value]);
                break;

            case 'level':
                $onoff = 1;
                if ($value == 0) {
                    $onoff = 0;
                }
                zigate::CallZiGate('action_move_level_onoff', [$addr, $endpoint, $onoff, $value]);
                break;
                
            case 'stop':
                zigate::CallZiGate('action_move_stop_onoff', [$addr, $endpoint]);
                break;

            case 'lock':
                zigate::CallZiGate('action_lock', [$addr, $endpoint, $value]);
                break;

            case 'color':
                zigate::CallZiGate('action_move_colour_hex', [$addr, $endpoint, $value]);
                break;

            case 'temperature':
                // mired : min 153 max 500
                zigate::CallZiGate('action_move_temperature', [$addr, $endpoint, $value]);
                break;

            case 'hue':
                zigate::CallZiGate('action_move_hue_hex', [$addr, $endpoint, $value]);
                break;
                
            case 'cover':
                zigate::CallZiGate('action_cover', [$addr, $endpoint, $value]);
                break;

            case 'refresh':
                zigate::callZiGate('refresh_device', [$addr]);
                break;

            case 'ias_warning':
                    zigate::CallZiGate('action_ias_warning', [$addr, $endpoint, 'burglar', true, 'low', $value]);
                break;
            case 'ias_off':
                    zigate::CallZiGate('action_ias_warning', [$addr, $endpoint, 'stop']);
                break;
            case 'ias_squawk':
                    zigate::CallZiGate('action_ias_squawk', [$addr, $endpoint, $value, true]);
                break;
            case 'Occupied_temperature_setpoint':
                    zigate::CallZiGate('action_thermostat_occupied_heating_setpoint', [$addr, $endpoint, $value]);
                break;
            case 'System_mode':
                    zigate::CallZiGate('action_thermostat_system_mode', [$addr, $endpoint, $value]);
                break;
        }
    }
}
