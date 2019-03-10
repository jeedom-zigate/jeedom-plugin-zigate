<?php
/*
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
$eqLogics = eqLogic::byType('Zigate');

$startTime = config::byKey('lastDeamonLaunchTime', 'Zigate', '{{Jamais lancé}}');
$awakedDelay = '';
$versionFirmware = zigate::callZiGate('get_version_text');
$versionLib = zigate::callZiGate('get_libversion');
$usbPort = config::byKey('Port', 'Zigate');
$JeedomNodesCount = count($eqLogics);
$ZigateNodes = zigate::callZiGate('devices');
$ZigateNodesCount = count($ZigateNodes["result"]);

$DeamonInfo = zigate::deamon_info();
if ($DeamonInfo['launchable'] == 'ok') {
    $DeamonState = "<i class=\"fa fa-circle fa-lg greeniconcolor\"></i>{{ Demon actif}}";
} else {
    $DeamonState = "<i class=\"fa fa-circle fa-lg rediconcolor\"></i>{{ Demon non actif : }}".$DeamonInfo['launchable_message'];
}
$zigatetree_content = file_get_contents('./plugins/zigate/resources/zigated/zigate.json', true);

$eqLs=array();
$infoeqL=array();
$infoeqL['id'] = '0';
$infoeqL['addr'] = '0000';
$infoeqL['name'] = '<span class="label" style="text-shadow : none;background-color:#9b59b6;color:#ffffff">Coordinateur</span> Zigate';
$infoeqL['lqi'] = '0';
$infoeqL['enable'] = '1';
$infoeqL['power'] = '0';
$eqLs['0000']=$infoeqL;
foreach ($eqLogics as $eqLogic) {
    $addr = $eqLogic->getConfiguration('addr');
    $infoeqL['id'] = $eqLogic->getId();
    $infoeqL['addr'] = $addr;
    $infoeqL['name'] = $eqLogic->getHumanName(true);
    $infoeqL['lqi'] = $eqLogic->getConfiguration('rssi');
    $infoeqL['enable'] = $eqLogic->getIsEnable();
    $infoeqL['power'] = $eqLogic->getConfiguration('power_type');
    $eqLs[$addr]=$infoeqL;
}
sendVarToJS('eqLs', $eqLs);

?>
<script type="text/javascript" src="./plugins/zigate/3rdparty/vivagraph/vivagraph.min.js"></script>
<style>
    .greeniconcolor {
        color: green;
    }
    .yellowiconcolor {
        color: #FFD700;
    }
    .rediconcolor {
        color: red;
    }
    #graph_network {
        height: 100%;
        width: 100%;
        position: absolute;
    }
    #graph_network > svg {
        height: 100%;
        width: 100%;
    }
    .node-coordinator-color {
        color: #000;
    }
    .node-router-color {
        color: #FF9F33;
    }
    .node-endterminal-color {
        color: #7BCC7B;
    }
</style>
<div id='div_networkZigateAlert' style="display: none;"></div>
<div class='network' nid='' id="div_templateNetwork">
    <div class="container-fluid">
        <div id="content">
            <ul id="tabs_network" class="nav nav-tabs" data-tabs="tabs">
                <li class="active"><a href="#summary_network" data-toggle="tab"><i class="fa fa-tachometer"></i> {{Résumé}}</a></li>
                <li><a href="#actions_network" data-toggle="tab"><i class="fa fa-sliders"></i> {{Actions}}</a></li>
                <li id="tab_graph"><a href="#graph_network" data-toggle="tab"><i class="fa fa-picture-o"></i> {{Graphique du réseau}}</a></li>
                <li><a href="#tree_network" data-toggle="tab"><i class="fa fa-tree"></i> {{Arbre Zigate}}</a></li>
            </ul>
            <div id="network-tab-content" class="tab-content">
                <div class="tab-pane active" id="summary_network">
                    <br>
                    <div class="panel panel-primary">
                        <div class="panel-heading"><h4 class="panel-title">{{Informations}}</h4></div>
                        <div class="panel-body">
                            <p>{{Réseau démarré le}} <span class="zigateNetworkAttr label label-default" style="font-size : 1em;" data-l1key="startTime">
                                <?php echo $startTime ?></span></p>
                            <p>{{Le réseau Jeedom contient}} <b><span class="zigateNetworkAttr" data-l1key="JeedomNodesCount">
                                <?php echo $JeedomNodesCount ?></span></b> {{noeuds, actuellement}} </p>
                            <p>{{Le réseau Zigate contient}} <b><span class="zigateNetworkAttr" data-l1key="ZigateNodesCount">
                                <?php echo $ZigateNodesCount ?></span></b> {{noeuds, actuellement}} </p>
                        </div>
                    </div>
                    <div class="panel panel-primary">
                        <div class="panel-heading"><h4 class="panel-title">{{Etat}}</h4></div>
                        <div class="panel-body">
                            <p><span class="zigateNetworkAttr" data-l1key="state"></span> {{Etat actuel :}} 
                               <span class="zigateNetworkAttr label label-default" data-l1key="stateDescription" style="font-size : 1em;">
                                   <?php echo $DeamonState ?>
                               </span>
                            </p>
                       </div>
                    </div>
                    <div class="panel panel-primary">
                        <div class="panel-heading"><h4 class="panel-title">{{Système}}</h4></div>
                        <div class="panel-body">
                            <p>{{Chemin du contrôleur Zigate :}} <span class="zigateNetworkAttr label label-default" data-l1key="devicePath" style="font-size : 1em;">
                                <?php echo $usbPort ?></span></p>
                            <p>{{Version du firmware Zigate :}} <span class="zigateNetworkAttr label label-default" data-l1key="ZigateFirmwareVersion" style="font-size : 1em;">
                                <?php echo $versionFirmware['result']; ?></span></p>
                            <p>{{Version de la librairie Zigate :}} <span class="zigateNetworkAttr label label-default" data-l1key="ZigateLibraryVersion" style="font-size : 1em;">
                                <?php echo $versionLib['result']; ?></span></p>
                        </div>
                    </div>
                </div>
                <div class="tab-pane" id="actions_network">
                    <table class="table">
                        <tr>
                            <td><a data-action="bt_ZigateScan" class="btn btn-success controller_action"><i class="fa fa-refresh"></i> {{Scan réseau}}</a></td>
                            <td>{{Lancement d’un scan du réseau ZigBee. Peut résoudre des problèmes d’association, mais pour l’instant l’impact est inconnu.}}</td>
                        </tr>
                        <tr>
                            <td><a data-action="bt_ZigateSync" class="btn btn-success controller_action"><i class="fa fa-refresh"></i> {{Synchroniser}}</a></td>
                            <td>{{Synchronise les informations du plugin avec les informations réelles du réseau ZigBee.}}</td>
                        </tr>
                        <tr>
                            <td><a data-action="bt_ZigateClean" class="btn btn-success controller_action"><i class="fa fa-shower"></i> {{Nettoyer}}</a></td>
                            <td>{{Supprime les équipements fantômes.}}</td>
                        </tr>
                        <tr>
                            <td><a data-action="bt_Zigatereset" class="btn btn-warning controller_action"><i class="fa fa-recycle"></i> {{Redémarrage Zigate}}</a></td>
                            <td>{{équivalent à un débranchement/rebranchement de la ZiGate. Cette commande ne supprime aucune donnée.}}</td>
                        </tr>
                        <tr>
                            <td><a data-action="bt_ZigateerasePDM" class="btn btn-danger controller_action"><i class="fa fa-eraser"></i> {{Effacement Zigate}}</a></td>
                            <td>{{Remise à zéro du contrôleur.}}</td>
                        </tr>
                    </table>
                </div>
                <div class="tab-pane" id="graph_network" >
                    <table class="table table-bordered table-condensed" style="width: 350px;position:fixed;margin-top : 25px;">
                        <thead><tr><th colspan="2">{{Légende}}</th></tr></thead>
                        <tbody>
                            <tr>
                                <td class="node-coordinator-color" style="width: 35px"><i class="fa fa-square fa-2x"></i></td>
                                <td>{{Coordinateur Zigbee}}</td>
                            </tr>
                            <tr>
                                <td class="node-router-color" style="width: 35px"><i class="fa fa-square fa-2x"></i></td>
                                <td>{{Routeur Zigbee (FFD)}}</td>
                            </tr>
                            <tr>
                                <td class="node-endterminal-color"><i class="fa fa-square fa-2x"></i></td>
                                <td>{{Terminal Zigbee (RFD)}}</td>
                            </tr>
                        </tbody>
                    </table>
                    <div id="graph-node-name"></div>
                </div>
                <div class="tab-pane" id="tree_network">
                    <div style="overflow: scroll; height: 100%;">
                        <pre id="pre_treeZigate" style="height: 100%;"><?php echo $zigatetree_content ?></pre>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include_file('desktop', 'network', 'js', 'zigate');?>
