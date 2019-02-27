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

require_once dirname(__FILE__) . '/../../../core/php/core.inc.php';
include_file('core', 'authentification', 'php');
if (!isConnect()) {
    include_file('desktop', '404', 'php');
    die();
}

$versionFirmware = zigate::callZiGate('get_version_text');
$versionLib = zigate::callZiGate('get_libversion');

?>
<form class="form-horizontal">
    <fieldset>
        <div class="form-group">
            <label class="col-lg-4 control-label">
                {{ Version plugin }} <sup><i class="fa fa-question-circle tooltips" title="{{C'est la version du plugin ZiGate}}" style="font-size : 1em;color:grey;"></i></sup>
            </label>
            <span style="top:6px;" class="col-lg-4"><?php echo zigate::getVersion(); ?></span>
        </div>
        <div class="form-group">
            <label class="col-lg-4 control-label">
                {{ Version lib }}
                <sup><i class="fa fa-question-circle tooltips" title="{{C'est la version de la librairie Python ZiGate}}" style="font-size : 1em;color:grey;"></i></sup>
            </label>
            <span style="top:6px;" class="col-lg-4"><?php echo $versionLib['result']; ?></span>
        </div>
        <div class="form-group">
            <label class="col-lg-4 control-label">
                {{ Version firmware }}
                <sup><i class="fa fa-question-circle tooltips" title="{{C'est la version du firmware de la ZiGate}}" style="font-size : 1em;color:grey;"></i></sup>
            </label>
            <span style="top:6px;" class="col-lg-4"><?php echo $versionFirmware['result']; ?></span>
        </div>
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
            <label class="col-sm-3 control-label">{{Canal}}</label>
            <div class="col-sm-3">
                <input type="text" class="configKey form-control" data-l1key="channel" placeholder="11"/>
            </div>
        </div>
        <div class="form-group">
            <label class="col-sm-3 control-label">{{J'autorise l'envoi de données anonymes pour aider le développement}}</label>
            <div class="col-sm-3">
                <input type="checkbox" class="configKey form-control" data-l1key="sharedata"/>
            </div>
        </div>
    </fieldset>
</form>
