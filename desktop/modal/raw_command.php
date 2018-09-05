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
    throw new Exception('401 Unauthorized');
}
?>
<span class="pull-left alert" id="span_state" style="background-color : #dff0d8;color : #3c763d;height:35px;border-color:#d6e9c6;display:none;margin-bottom:0px;">
    <span style="position:relative; top : -7px;">{{Demande envoyée}}</span>
</span>
<br/><br/>

<div id='div_backupAlert' style="display: none;"></div>
<form class="form-horizontal">
    <fieldset>
        <div class="form-group">
            <label class="col-sm-4 col-xs-6 control-label">{{Créer une sauvegarde}}</label>
            <div class="col-sm-4 col-xs-6">
                <a class="btn btn-success" id="bt_createBackup"><i class="fa fa-floppy-o"></i> {{Lancer}}</a>
            </div>
        </div>
        <div class="form-group">
            <label class="col-sm-4 col-xs-6 control-label">{{Sauvegardes disponibles}}</label>
            <div class="col-sm-4 col-xs-6">
                <select class="form-control" id="sel_restoreBackup"> </select>
            </div>
        </div>
        <div class="form-group">
            <label class="col-sm-4 col-xs-6 control-label">{{Restaurer/Supprimer la sauvegarde}}</label>
            <div class="col-sm-4 col-xs-6">
                <a class="btn btn-warning" id="bt_restoreBackup"><i class="fa fa-refresh fa-spin"
                    style="display : none;"></i> <i
                    class="fa fa-file"></i> {{Restaurer}}</a>
                    <a class="btn btn-danger" id="bt_removeBackup"><i class="fa fa-trash-o"></i> {{Supprimer}}</a>
                </div>
            </div>
        </fieldset>
    </form>
</div>
<?php include_file('core', 'openzwave', 'class.js', 'openzwave');?>
<?php include_file('desktop', 'backup', 'js', 'openzwave');?>
