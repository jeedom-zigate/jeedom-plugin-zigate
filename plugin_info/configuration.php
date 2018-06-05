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

require_once dirname(__FILE__) . '/../../../core/php/core.inc.php';
include_file('core', 'authentification', 'php');
if (!isConnect()) {
    include_file('desktop', '404', 'php');
    die();
}
?>
<form class="form-horizontal">
    <fieldset>
        	<div class="form-group">
			<label class="col-sm-4 control-label">{{Port clé ZiGate}}</label>
			<div class="col-sm-2">
				<select class="configKey form-control" data-l1key="port">
					<option value="auto">{{Auto}}</option>
					<?php
foreach (jeedom::getUsbMapping('', true) as $name => $value) {
	echo '<option value="' . $value . '">' . $name . ' (' . $value . ')</option>';
}
?>
				</select>
			</div>
		</div>
		<div class="form-group">
            <label class="col-sm-3 control-label">{{ou Adresse ZiGate Wifi}}</label>
            <div class="col-sm-3">
                <input type="text" class="configKey form-control" data-l1key="host" placeholder="ip:port"/>
            </div>
        </div>
        <div class="form-group">
            <label class="col-sm-3 control-label">{{J'autorise l'envoi de données anonymes pour aider le développement}}</label>
            <div class="col-sm-3">
                <input type="checkbox" class="configKey form-control" data-l1key="sharedata" checked/>
            </div>
        </div>
    </fieldset>
</form>

