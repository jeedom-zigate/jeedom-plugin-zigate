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
$nodesCount = count($eqLogics);
?>
<div id='div_networkZigateAlert' style="display: none;"></div>
<div class='network' nid='' id="div_templateNetwork">
    <div class="container-fluid">
        <div id="content">
            <ul id="tabs_network" class="nav nav-tabs" data-tabs="tabs">
                <li class="active"><a href="#summary_network" data-toggle="tab"><i class="fa fa-tachometer"></i> {{Résumé}}</a></li>
                <li><a href="#actions_network" data-toggle="tab"><i class="fa fa-sliders"></i> {{Actions}}</a></li>
            </ul>
            <div id="network-tab-content" class="tab-content">
                <div class="tab-pane active" id="summary_network">
                    <br>
                    <div class="panel panel-primary">
                        <div class="panel-heading"><h4 class="panel-title">{{Informations}}</h4></div>
                        <div class="panel-body">
                            <p>{{Réseau démarré le}} <span class="zigateNetworkAttr label label-default" style="font-size : 1em;" data-l1key="startTime"><?php echo $startTime ?></span></p>
                            <p>{{Le réseau contient}} <b><span class="zigateNetworkAttr" data-l1key="nodesCount"><?php echo $nodesCount ?></span></b> {{noeuds, actuellement}} </p>
                        </div>
                    </div>
                    <div class="panel panel-primary">
                        <div class="panel-heading"><h4 class="panel-title">{{Système}}</h4></div>
                        <div class="panel-body">
                            <p>{{Chemin du contrôleur Zigate :}} <span class="zwaveNetworkAttr label label-default" data-l1key="devicePath" style="font-size : 1em;"><?php echo $usbPort ?></span></p>
                            <p>{{Version du firmware Zigate :}} <span class="zwaveNetworkAttr label label-default" data-l1key="ZigateFirmwareVersion" style="font-size : 1em;"><?php echo $versionFirmware['result']; ?></span></p>
                            <p>{{Version de la librairie Zigate :}} <span class="zwaveNetworkAttr label label-default" data-l1key="ZigateLibraryVersion" style="font-size : 1em;"><?php echo $versionLib['result']; ?></span></p>
                        </div>
                    </div>
                </div>
                <div class="tab-pane" id="actions_network">
                    <table class="table">
                        <tr>
                            <td><a data-action="bt_ZigateSync" class="btn btn-success controller_action"><i class="fa fa-refresh"></i> {{Synchroniser}}</a></td>
                            <td>{{Synchronise les informations du plugin avec les informations réelles du réseau ZigBee.}}</td>
                        </tr>
                        <tr>
                            <td><a data-action="bt_ZigateClean" class="btn btn-success controller_action"><i class="fa fa-shower"></i> {{Nettoyer}}</a></td>
                            <td>{{Vide pour le moment}}</td>
                        </tr>
                        <tr>
                            <td><a data-action="bt_Zigatereset" class="btn btn-warning controller_action"><i class="fa fa-recycle"></i> {{Redémarrage}}</a></td>
                            <td>{{équivalent à un débranchement/rebranchement de la ZiGate. Cette commande ne supprime aucune donnée.}}</td>
                        </tr>
                        <tr>
                            <td><a data-action="bt_ZigateerasePDM" class="btn btn-danger controller_action"><i class="fa fa-eraser"></i> {{Remise à zéro}}</a></td>
                            <td>{{Remise à zéro du contrôleur}}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    $('.controller_action').on('click',function(){
        if($(this).data('action') == 'bt_ZigateerasePDM'){
            $action = $(this).data('action');
            bootbox.confirm("Etes-vous sûr de vouloir effacer les données de la zigate ?", function (result) {
                if (result) {
                    callZiGate('erase_persistent');
                }
            });
        }
        if($(this).data('action') == 'bt_ZigateSync'){
            callZiGate('start_network_scan');
        }
        if($(this).data('action') == 'bt_ZigateClean'){
            $action = $(this).data('action');
            bootbox.confirm("Etes-vous sûr de vouloir effacer les équipements manquants ?", function (result) {
                if (result) {
                    callZiGate('cleanup_devices');
                }
            });
        }
        if($(this).data('action') == 'bt_Zigatereset'){
            reset();
        }
    });
</script>
