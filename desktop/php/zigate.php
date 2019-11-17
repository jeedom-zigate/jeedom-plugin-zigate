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
    throw new Exception('{{401 - Accès non autorisé}}');
}

$plugin = plugin::byId('zigate');
sendVarToJS('eqType', $plugin->getId());
$eqLogics = eqLogic::byType($plugin->getId());
// phpcs:disable  Generic.Files.LineLength.TooLong
?>

<div class="row row-overflow">
    <div class="col-xs-12 eqLogicThumbnailDisplay">
        <legend><i class="fas fa-cog"></i>  {{Gestion}}</legend>
        <div class="eqLogicThumbnailContainer">
            <div class="cursor eqLogicAction logoPrimary" id="bt_permitJoin">
                <i class="fas fa-sign-in-alt fa-rotate-90"></i>
                <br/>
                <span>{{Mode inclusion}}</span>
            </div>
            <div class="cursor eqLogicAction" id="bt_syncEqLogic">
                    <i class="fas fa-sync"></i>
                    <br/>
                <span>{{Synchroniser}}</span>
            </div>
            <div class="cursor eqLogicAction" id="bt_cleanup_devices">
                    <i class="fas fa-shower"></i>
                    <br/>
                <span>{{Nettoyer}}</span>
            </div>
            <div class="cursor eqLogicAction" id="bt_reset" >
                    <i class="fas fa-recycle"></i>
                    <br/>
                <span>{{Redémarrage Zigate}}</span>
            </div>
            <div class="cursor eqLogicAction" id="bt_erasepdm">
                    <i class="fas fa-eraser"></i>
                    <br/>
                <span>{{Effacement Zigate}}</span>
            </div>
            <div class="cursor eqLogicAction" data-action="gotoPluginConf">
                    <i class="fas fa-wrench"></i>
                    <br/>
                <span>{{Configuration}}</span>
            </div>
            <div class="cursor" id="bt_healthzigate">
                    <i class="fas fa-medkit"></i>
                    <br/>
                <span>{{Santé}}</span>
            </div>
            <div class="cursor" id="bt_terminalzigate">
                    <i class="fas fa-terminal"></i>
                    <br/>
                <span>{{Terminal}}</span>
            </div>
        </div>

        <legend><i class="fas fa-table"></i> {{Mes équipements ZiGate}}</legend>
        <input class="form-control" placeholder="{{Rechercher}}" id="in_searchEqlogic" />
        <div class="eqLogicThumbnailContainer">
            <?php
            foreach ($eqLogics as $eqLogic) {
                $opacity = ($eqLogic->getIsEnable()) ? '' : 'disableCard';
                echo '<div class="eqLogicDisplayCard cursor '.$opacity.'" data-logical-id="' . $eqLogic->getLogicalId() . '" data-eqLogic_id="' . $eqLogic->getId() . '">';
                echo "<center>";
                echo '<img src="' . $eqLogic->getImage() . '" height="105" width="95" />';
                echo "</center>";
                echo '<span class="name" ' . $eqLogic->getHumanName(true, true) . '</span>';
                echo '</div>';
            }
            ?>
        </div>
    </div>

    <div class="col-xs-12 eqLogic" style="display: none;">
    <div class="input-group pull-right" style="display:inline-flex">
    <span class="input-group-btn">
        <a class="btn btn-success eqLogicAction roundedLeft" data-action="save"><i class="fas fa-check-circle"></i> {{Sauvegarder}}</a>
        <a class="btn btn-danger eqLogicAction" data-action="remove"><i class="fas fa-minus-circle"></i> {{Supprimer}}</a>
        <a class="btn btn-default eqLogicAction" data-action="configure"><i class="fas fa-cogs"></i> {{Configuration avancée}}</a>
        <a class="btn btn-success eqLogicAction" data-action="refresh_device"><i class="fas fa-refresh"></i> {{Rafraichir l'équipement}}</a>
        <a class="btn btn-success eqLogicAction" data-action="discover_device"><i class="fas fa-refresh"></i> {{Découvrir l'équipement}}</a>
        <a class="btn btn-success eqLogicAction roundedRight" data-action="identify_device"><i class="fas fa-refresh"></i> {{Identifier}}</a>
        </span>
        </div>

        <ul class="nav nav-tabs" role="tablist">
            <li role="presentation"><a href="#" class="eqLogicAction" aria-controls="home" role="tab" data-toggle="tab" data-action="returnToThumbnailDisplay">
                <i class="fas fa-arrow-circle-left"></i></a>
            </li>
            <li role="presentation" class="active"><a href="#eqlogictab" aria-controls="home" role="tab" data-toggle="tab"><i class="fas fa-tachometer"></i> {{Equipement}}</a></li>
            <li role="presentation"><a href="#commandtab" aria-controls="profile" role="tab" data-toggle="tab"><i class="fas fa-list-alt"></i> {{Commandes}}</a></li>
        </ul>

        <div class="tab-content" style="height:calc(100% - 50px);overflow:auto;overflow-x: hidden;">
            <div role="tabpanel" class="tab-pane active" id="eqlogictab">
                <br/>
                <form class="form-horizontal">
                    <fieldset>
                        <div class="form-group">
                            <label class="col-sm-3 control-label">{{Nom de l'équipement zigate}}</label>
                            <div class="col-sm-3">
                                <input type="text" class="eqLogicAttr form-control" data-l1key="id" style="display : none;" />
                                <input type="text" class="eqLogicAttr form-control" data-l1key="name" placeholder="{{Nom de l'équipement zigate}}"/>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-3 control-label" >{{Objet parent}}</label>
                            <div class="col-sm-3">
                                <select id="sel_object" class="eqLogicAttr form-control" data-l1key="object_id">
                                    <option value="">{{Aucun}}</option>
                                    <?php
                                    foreach (jeeObject::all() as $object) {
                                        echo '<option value="' . $object->getId() . '">' . $object->getName() . '</option>';
                                    }
                                    ?>
                               </select>
                           </div>
                       </div>
                       <div class="form-group">
                            <label class="col-sm-3 control-label">{{Catégorie}}</label>
                            <div class="col-sm-9">
                                <?php
                                foreach (jeedom::getConfiguration('eqLogic:category') as $key => $value) {
                                    echo '<label class="checkbox-inline">';
                                    echo '<input type="checkbox" class="eqLogicAttr" data-l1key="category" data-l2key="' . $key . '" />{{' . $value['name'] .'}}';
                                    echo '</label>';
                                }
                                ?>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-3 control-label"></label>
                            <div class="col-sm-9">
                                <label class="checkbox-inline"><input type="checkbox" class="eqLogicAttr" data-l1key="isEnable" checked/>{{Activer}}</label>
                                <label class="checkbox-inline"><input type="checkbox" class="eqLogicAttr" data-l1key="isVisible" checked/>{{Visible}}</label>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-3 control-label">{{ADDR}}</label>
                            <div class="col-sm-3">
                                <input type="text" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="addr" placeholder="addr" readonly=true/>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-3 control-label">{{Type}}</label>
                            <div class="col-sm-3">
                                <input type="text" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="type" placeholder="type" readonly=true/>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-3 control-label">{{Manufacturer}}</label>
                            <div class="col-sm-3">
                                <input type="text" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="manufacturer" placeholder="Manufacturer" readonly=true/>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-3 control-label">{{IEEE}}</label>
                            <div class="col-sm-3">
                                <input type="text" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="ieee" placeholder="ieee" readonly=true/>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-3 control-label">{{Qualité du lien LQI (1-255)}}</label>
                            <div class="col-sm-3">
                                <input type="text" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="lqi" placeholder="0" readonly=true/>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-3 control-label">{{Alimentation (0=Batterie, 1=Secteur)}}</label>
                            <div class="col-sm-3">
                                <input type="text" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="power_type" placeholder="0" readonly=true/>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-3 control-label">{{Batterie (%)}}</label>
                            <div class="col-sm-3">
                                <input type="text" class="eqLogicAttr form-control" data-l1key="status" data-l2key="battery" placeholder="" readonly=true/>
                            </div>
                        </div>
                    </fieldset>
                </form>
            </div>

            <div role="tabpanel" class="tab-pane" id="commandtab">
                <table id="table_cmd" class="table table-bordered table-condensed">
                    <thead>
                        <tr>
                            <th style="width: 50px;">#</th>
                            <th>{{Nom}}</th>
                            <th>{{Type}}</th>
                            <th style="width: 100px;">{{Unité}}</th>
                            <th style="width: 200px;">{{Paramètres}}</th>
                            <th>{{Action}}</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include_file('desktop', 'zigate', 'js', 'zigate');?>
<?php include_file('core', 'plugin.template', 'js');?>
