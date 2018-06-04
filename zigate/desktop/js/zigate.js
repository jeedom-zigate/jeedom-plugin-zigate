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


$("#table_cmd").sortable({axis: "y", cursor: "move", items: ".cmd", placeholder: "ui-state-highlight", tolerance: "intersect", forcePlaceholderSize: true});
/*
 * Fonction pour l'ajout de commande, appellé automatiquement par plugin.zigate
 */
function addCmdToTable(_cmd) {
    if (!isset(_cmd)) {
        var _cmd = {configuration: {}};
    }
    if (!isset(_cmd.configuration)) {
        _cmd.configuration = {};
    }
    var tr = '<tr class="cmd" data-cmd_id="' + init(_cmd.id) + '">';
    tr += '<td>';
    tr += '<span class="cmdAttr" data-l1key="id"></span>';
    tr += '</td>';
    tr += '<td>';
    tr += '<input class="cmdAttr form-control input-sm" data-l1key="name" style="width : 140px;" placeholder="{{Nom}}">';
    tr += '</td>';
    tr += '<td>';
    tr += '<input class="cmdAttr form-control type input-sm" data-l1key="type" value="info" disabled style="margin-bottom : 5px;" />';
    tr += '<span class="subType" subType="' + init(_cmd.subType) + '"></span>';
    tr += '</td>';
    tr += '<td>';
    tr += '<input class="cmdAttr form-control input-sm" data-l1key="unite" style="width : 90px;" placeholder="{{Unité}}">';
    tr += '<input class="cmdAttr form-control input-sm" data-l1key="configuration" data-l2key="minValue" placeholder="{{Min}}" title="{{Min}}" style="margin-top : 5px;"> ';
    tr += '<input class="cmdAttr form-control input-sm" data-l1key="configuration" data-l2key="maxValue" placeholder="{{Max}}" title="{{Max}}" style="margin-top : 5px;">';
    tr += '</td>';
 	tr += '<td>';
    tr += '<span><label class="checkbox-inline"><input type="checkbox" class="cmdAttr checkbox-inline" data-l1key="isVisible" checked/>{{Afficher}}</label></span> ';
    tr += '<span><label class="checkbox-inline"><input type="checkbox" class="cmdAttr checkbox-inline" data-l1key="isHistorized" checked/>{{Historiser}}</label></span> ';
    tr += '<span><label class="checkbox-inline"><input type="checkbox" class="cmdAttr checkbox-inline" data-l1key="display" data-l2key="invertBinary"/>{{Inverser}}</label></span> '
   	tr += '</td>';
    tr += '<td>';
    if (is_numeric(_cmd.id)) {
        tr += '<a class="btn btn-default btn-xs cmdAction" data-action="configure"><i class="fa fa-cogs"></i></a> ';
        tr += '<a class="btn btn-default btn-xs cmdAction" data-action="test"><i class="fa fa-rss"></i> {{Tester}}</a>';
    }
    tr += '<i class="fa fa-minus-circle pull-right cmdAction cursor" data-action="remove"></i>';
    tr += '</td>';
    tr += '</tr>';
    $('#table_cmd tbody').append(tr);
    $('#table_cmd tbody tr:last').setValues(_cmd, '.cmdAttr');
    if (isset(_cmd.type)) {
        $('#table_cmd tbody tr:last .cmdAttr[data-l1key=type]').value(init(_cmd.type));
    }
    jeedom.cmd.changeType($('#table_cmd tbody tr:last'), init(_cmd.subType));
}

$('#bt_syncEqLogic').on('click', function () {
    syncEqLogicWithZiGate();
});

$('#bt_permitJoin').on('click', function () {
    permitJoin();
});

$('#bt_reset').on('click', function () {
    reset();
});

$('#bt_touchlink').on('click', function () {
    callZiGate('initiate_touchlink');
});

$('#bt_networkscan').on('click', function () {
	callZiGate('start_network_scan');
});

$('.eqLogicAction[data-action=refresh_device]').on('click', function () {
    if ($('.li_eqLogic.active').attr('data-eqLogic_id') != undefined) {
    	id = $('.li_eqLogic.active').attr('data-eqLogic_id');
    	refresh_eqlogic(id);
    } else {
        $('#div_alert').showAlert({message: '{{Veuillez d\'abord sélectionner un}} ' + eqType, level: 'danger'});
    }
});

$('.eqLogicAction[data-action=identify_device]').on('click', function () {
    if ($('.li_eqLogic.active').attr('data-eqLogic_id') != undefined) {
    	id = $('.li_eqLogic.active').attr('data-eqLogic_id');
    	identify_device(id);
    } else {
        $('#div_alert').showAlert({message: '{{Veuillez d\'abord sélectionner un}} ' + eqType, level: 'danger'});
    }
});

function callZiGate(action){
	$.ajax({
        type: "POST", 
        url: "plugins/zigate/core/ajax/zigate.ajax.php", 
        data: {
            action: action,
        },
        dataType: 'json',
        error: function (request, status, error) {
            handleAjaxError(request, status, error);
        },
        success: function (data) { 
            if (data.state != 'ok') {
                $('#div_alert').showAlert({message: data.result, level: 'danger'});
                return;
            }
            $('#div_alert').showAlert({message: 'Commande envoyée', level: 'info'});
        }
    });
}

function syncEqLogicWithZiGate() {
    $.ajax({
        type: "POST", 
        url: "plugins/zigate/core/ajax/zigate.ajax.php", 
        data: {
            action: "syncEqLogicWithZiGate",
        },
        dataType: 'json',
        error: function (request, status, error) {
            handleAjaxError(request, status, error);
        },
        success: function (data) { 
            if (data.state != 'ok') {
                $('#div_alert').showAlert({message: data.result, level: 'danger'});
                return;
            }
            window.location.reload();
        }
    });
}

function permitJoin() {
    $.ajax({
        type: "POST", 
        url: "plugins/zigate/core/ajax/zigate.ajax.php", 
        data: {
            action: "permit_join",
        },
        dataType: 'json',
        error: function (request, status, error) {
            handleAjaxError(request, status, error);
        },
        success: function (data) { 
            if (data.state != 'ok') {
                $('#div_alert').showAlert({message: data.result, level: 'danger'});
                return;
            }
            else {
            	$('#div_alert').showAlert({message: 'Mode inclusion lancé pour 30sec.', level: 'warning'});
            }
        }
    });
}

function reset() {
    $.ajax({
        type: "POST", 
        url: "plugins/zigate/core/ajax/zigate.ajax.php", 
        data: {
            action: "reset",
        },
        dataType: 'json',
        error: function (request, status, error) {
            handleAjaxError(request, status, error);
        },
        success: function (data) { 
            if (data.state != 'ok') {
                $('#div_alert').showAlert({message: data.result, level: 'danger'});
                return;
            }
            else {
            	$('#div_alert').showAlert({message: 'Reset de la clé ZiGate.', level: 'warning'});
            }
        }
    });
}

function refresh_eqlogic(id){
	$.ajax({
        type: "POST", 
        url: "plugins/zigate/core/ajax/zigate.ajax.php", 
        data: {
            action: "refresh_eqlogic",
            args: [id]
        },
        dataType: 'json',
        error: function (request, status, error) {
            handleAjaxError(request, status, error);
        },
        success: function (data) { 
            if (data.state != 'ok') {
                $('#div_alert').showAlert({message: data.result, level: 'danger'});
                return;
            }
            else {
            	$('#div_alert').showAlert({message: 'Rafraichissement de l\'équipement lancé.<br>' +
            			'Les équipements sur pile doivent être activés manuellement pour transmettre les infos' +
            			' (Appui sur le bouton de synchro, manipulation, etc)', level: 'warning'});
            }
        }
    });
}

function identify_device(id){
	$.ajax({
        type: "POST", 
        url: "plugins/zigate/core/ajax/zigate.ajax.php", 
        data: {
            action: "identify_device",
            args: [id]
        },
        dataType: 'json',
        error: function (request, status, error) {
            handleAjaxError(request, status, error);
        },
        success: function (data) { 
            if (data.state != 'ok') {
                $('#div_alert').showAlert({message: data.result, level: 'danger'});
                return;
            }
            else {
            	$('#div_alert').showAlert({message: 'Identification de l\'équipement lancé. (Cette commande peut être sans effet sur certain équipement)', level: 'warning'});
            }
        }
    });
}

$('body').off('zigate::device_changed').on('zigate::device_changed', function (_event, _options) {
	if (_options == '') {
        window.location.reload();
    } else {
        window.location.href = 'index.php?v=d&p=zigate&m=zigate&id=' + _options;
    }
});
        