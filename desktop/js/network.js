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
$('.controller_action').on('click',function () {
    if ($(this).data('action') == 'bt_ZigateerasePDM') {
        $action = $(this).data('action');
        bootbox.confirm("{{Etes-vous sûr de vouloir effacer les données de la zigate ?}}", function (result) {
            if (result) {
                callZiGate('erase_persistent');
            }
        });
    }
    if ($(this).data('action') == 'bt_ZigateScan') {
        callZiGate('start_network_scan');
    }
    if ($(this).data('action') == 'bt_ZigateSync') {
        syncEqLogicWithZiGate();
    }
    if ($(this).data('action') == 'bt_ZigateClean') {
        $action = $(this).data('action');
        bootbox.confirm("{{Etes-vous sûr de vouloir effacer les équipements manquants ?}}", function (result) {
            if (result) {
                callZiGate('cleanup_devices');
            }
        });
    }
    if ($(this).data('action') == 'bt_Zigatereset') {
        reset();
    }
});

$("#tab_graph").off("click").on("click", function () {
    load_graph();
});

function load_graph()
{
    $.ajax({
        type: "POST",
        url: "plugins/zigate/core/ajax/zigate.ajax.php",
        data: {
            action: "build_neighbours_table",
        },
        dataType: 'json',
        error: function (request, status, error) {
            handleAjaxError(request, status, error);
        },
        success: function (data) {
            links = data.result.result;
            console.log(links);
            $('#graph_network svg').remove();

            var neighbors = {},
                graph = Viva.Graph.graph();

            for (addr in eqLs) {
                if (eqLs[addr]['enable'] == 1) {
                    graph.addNode(eqLs[addr]['addr'],{
                        'id' : eqLs[addr]['id'],
                        'name' : eqLs[addr]['name'],
                        'addr' : addr,
                        //'lqi' : eqLs[addr]['lqi'],
                        'power' : eqLs[addr]['power']
                    });
                };
            };
            links.forEach(function (element) {
                if (typeof neighbors[element[0]] != 'undefined') {
                    if (neighbors[element[0]].indexOf(eqLs[element[1]]["id"]) == -1) {
                        neighbors[element[0]] += ","+eqLs[element[1]]["id"];
                        graph.addLink(element[0], element[1], {lqi : element[2]});
                    }
                    if (typeof neighbors[element[1]] != 'undefined') {
                        if (neighbors[element[1]].indexOf(eqLs[element[0]]["id"]) == -1) {
                            neighbors[element[1]] += ","+eqLs[element[0]]["id"];
                        }
                    } else {
                        neighbors[element[1]] = eqLs[element[0]]["id"];
                    };
                } else {
                    neighbors[element[0]] = eqLs[element[1]]["id"];
                    neighbors[element[1]] = eqLs[element[0]]["id"];
                    graph.addLink(element[0], element[1], {lqi : element[2]});
                }
            });

            var graphics = Viva.Graph.View.svgGraphics(),
                highlightRelatedNodes = function (nodeId, isOn) {
                    graph.forEachLinkedNode(nodeId, function (node, link) {
                        var linkUI = graphics.getLinkUI(link.id);
                        if (linkUI) {
                            linkUI.attr('stroke', isOn ? '#FF0000' : '#B7B7B7');
                        }
                    });
                };
            var nodeSize = 7;
            graphics.node(function (node) {
                var nodeColor = '#7BCC7B';
                if (node.data.addr == "0000") {
                    nodeColor = '#000000';
                } else if (node.data.power == 1) {
                    nodeColor = '#FF9F33';
                };
                ui = Viva.Graph.svg('g'),
                text = Viva.Graph.svg('text')
                    .attr('text-anchor', 'middle')
                    .attr('stroke', "#000")
                    .attr('stroke-width', '1.5px')
                    .attr('y', '-10px')
                    .text(node.data.id),
                circle = Viva.Graph.svg('circle')
                    .attr('r', nodeSize)
                    .attr('stroke', '#fff')
                    .attr('stroke-width', '1.5px')
                    .attr("fill", nodeColor);
                ui.append(text);
                ui.append(circle);
                $(ui).hover(function () {
                    if (typeof neighbors[node.data.addr] != 'undefined') {
                        $('#graph-node-name').html(node.data.name + " : " + neighbors[node.data.addr].split(',').length + " {{voisin(s)}} [" + neighbors[node.data.addr] + "]");
                    } else {
                        $('#graph-node-name').html(node.data.name + " : {{Aucun voisin}}");
                    };
                    highlightRelatedNodes(node.id, true);
                }, function () {
                    highlightRelatedNodes(node.id, false);
                });
                return ui;
            }).placeNode(function (nodeUI, pos) {
                nodeUI.attr('transform', 'translate('+ pos.x + ',' + pos.y + ')');
            });
            var geom = Viva.Graph.geom();
            graphics.link(function (link) {
                var label = Viva.Graph.svg('text')
                    .attr('id','label_'+link.data.lqi).text(link.data.lqi)
                    .attr('font-size', '8')
                    graphics.getSvgRoot().childNodes[0].append(label);
                    return Viva.Graph.svg('path')
                        .attr('stroke', 'gray')
                        .attr('id', link.data.lqi);
            }).placeLink(function (linkUI, fromPos, toPos) {
                var toNodeSize = nodeSize,
                    fromNodeSize = nodeSize;
                var from = geom.intersectRect(
                fromPos.x - fromNodeSize / 2, // left
                fromPos.y - fromNodeSize / 2, // top
                fromPos.x + fromNodeSize / 2, // right
                fromPos.y + fromNodeSize / 2, // bottom
                fromPos.x, fromPos.y, toPos.x, toPos.y)
                || fromPos;
                var to = geom.intersectRect(
                toPos.x - toNodeSize / 2, // left
                toPos.y - toNodeSize / 2, // top
                toPos.x + toNodeSize / 2, // right
                toPos.y + toNodeSize / 2, // bottom
                toPos.x, toPos.y, fromPos.x, fromPos.y)
                || toPos;
                var data = 'M' + from.x + ',' + from.y + 'L' + to.x + ',' + to.y;
                linkUI.attr("d", data);
                document.getElementById('label_'+linkUI.attr('id'))
                    .attr("x", (from.x + to.x) / 2)
                    .attr("y", (from.y + to.y) / 2);
            });

            var renderer = Viva.Graph.View.renderer(graph, {
                graphics : graphics,
                container: document.getElementById('graph_network')
            });
            renderer.run();
        }
    });
};
