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
<div class="row row-overflow">
    <div id="zigateterminal">
		<div class="col-sm-6">
			<form class="form-horizontal">
				<fieldset>
					<legend>{{Commande}}</legend>
					<div class="form-group">
						<label class="col-sm-4 control-label">{{Commande}}</label>
						<div class="col-sm-6">
							<input type="text" class="form-control tooltips pluginAttr" data-l1key="zigateterminal" data-l2key="zigate_command"/>
						</div>
					</div>
					<div class="form-group">
						<label class="col-sm-4 control-label">{{Data}}</label>
						<div class="col-sm-6">
							<input type="text" class="form-control tooltips pluginAttr" data-l1key="zigateterminal" data-l2key="zigate_data"/>
						</div>
					</div>
					<div class="col-sm-4">
						<a class="btn btn-success" id="btn_sendcommand"><i class="fa fa-check-circle"></i> {{Envoyer}}</a>
					</div>
				</fieldset>
			</form>
		</div>
		<div class="col-sm-6">
			<form class="form-horizontal">
				<fieldset>
					<legend>{{Résultat}}</legend>
					<div style="overflow: scroll; height: 250px;">
                        <pre id="pre_logZigateCommand"></pre>
					</div>
				</fieldset>
			</form>
		</div>      
    </div>
</div>

<script>
	$('#btn_sendcommand').on('click', function () {
		$.ajax({
			type: "POST",
			url: "plugins/zigate/core/ajax/zigate.ajax.php",
			data: {
				action: "send_data",
				command: $('#zigate_command').value(),
				value: $('#zigate_data').value(),
			},
			dataType: 'json',
			error: function (request, status, error) {
				handleAjaxError(request, status, error);
			},
			success: function (data) {
				console.log(data)
				if (data.state != 'ok') {
					$('#pre_logZigateCommand').empty();
					$('#pre_logZigateCommand').prepend(data.state);
					return;
				}
				$('#pre_logZigateCommand').empty();
				$('#pre_logZigateCommand').prepend(data.result.result);
			}
		});
	});
</script>
