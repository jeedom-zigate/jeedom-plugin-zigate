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
$('.controller_action').on('click',function(){
    if($(this).data('action') == 'bt_ZigateerasePDM'){
        $action = $(this).data('action');
        bootbox.confirm("Etes-vous sûr de vouloir effacer les données de la zigate ?", function (result) {
            if(result){
                callZiGate('erase_persistent');
            }
        });
    }
    if($(this).data('action') == 'bt_ZigateScan'){
        callZiGate('start_network_scan');
    }
    if($(this).data('action') == 'bt_ZigateSync'){
        syncEqLogicWithZiGate();
    }
    if($(this).data('action') == 'bt_ZigateClean'){
        $action = $(this).data('action');
        bootbox.confirm("Etes-vous sûr de vouloir effacer les équipements manquants ?", function (result) {
            if(result){
                callZiGate('cleanup_devices');
            }
        });
    }
    if($(this).data('action') == 'bt_Zigatereset'){
        reset();
    }
});

$("#tab_graph").off("click").on("click", function () {
    load_graph();
});

function load_graph(){
    $('#graph_network svg').remove();
    //console.log(infonodes);
    var graph = Viva.Graph.graph();
    for (infonode in infonodes) {
        graph.addNode(infonodes[infonode]['id'],{
            'name' : infonodes[infonode]['name'],
            'addr' : infonodes[infonode]['addr'],
            'lqi' : infonodes[infonode]['lqi']
        });
        if (infonodes[infonode]['id'] != 0){
            graph.addLink(0, infonodes[infonode]['id']);
        };
    };

    var graphics = Viva.Graph.View.svgGraphics();
    nodeSize = 24;
    highlightRelatedNodes = function (nodeId, isOn) {
        graph.forEachLinkedNode(nodeId, function (node, link) {
            var linkUI = graphics.getLinkUI(link.id);
            if (linkUI) {
                linkUI.attr('stroke', isOn ? '#FF0000' : '#B7B7B7');
            }
        });
    };

    graphics.node(function(node) {
        nodecolor = '#7BCC7B';
        var nodesize = 10;
        var ui = Viva.Graph.svg('g'),
        svgText = Viva.Graph.svg('text').attr('y', '0px').text(node.id),
        img = Viva.Graph.svg('rect')
        .attr('width', nodesize)
        .attr('height', nodesize)
        .attr('fill', nodecolor);
        ui.append(svgText);
        ui.append(img);
        $(ui).hover(function () {
            $('#graph-node-name').html(node.data.name + ' : LQI ' + node.data.lqi);
            highlightRelatedNodes(node.id, true);
        }, function () {
            highlightRelatedNodes(node.id, false);
        });
        return ui;
    }).placeNode(function(nodeUI, pos){ 
        nodeUI.attr('transform', 
            'translate('+ 
            (pos.x - nodeSize / 3) + ',' + (pos.y - nodeSize / 2.5) + ')'); 
    });

        var renderer = Viva.Graph.View.renderer(graph, {
        graphics : graphics,
        prerender : 10000,
        container: document.getElementById('graph_network')
    });
    renderer.run();
    setTimeout(function () {
        //renderer.pause();
        renderer.reset();
    }, 200);
};

load_graph();
