import { roomsToCompare, tableHeaders } from '../scripts/data.js';

$(document).ready(function() {
    const $compareTable = $('#compare-table');
    let isTableCreated = false;

    if ($compareTable.length && roomsToCompare !== undefined) {
        const isVertical = $compareTable.hasClass('vertical-mode');
        let currentSort = { key: null, isDesc: false };

        function formatCell(room, key) {
            if (key === 'size') return `~${room[key]} m²`;
            if (key === 'price') return `~${room[key]}`;
            if (key === 'type') return `<span class="type-icon ${room.icon}"></span>${room[key]}`;
            return room[key];
        }

        function sortRooms(data, key, isDesc) {
            return [...data].sort((a, b) => {
                let val1 = a[key];
                let val2 = b[key];

                if (typeof val1 === 'string') val1 = val1.toLowerCase();
                if (typeof val2 === 'string') val2 = val2.toLowerCase();

                if (val1 < val2) return isDesc ? 1 : -1;
                if (val1 > val2) return isDesc ? -1 : 1;
                return 0;
            });
        }

        function renderTable(data) {
            if (!isTableCreated) {
                $compareTable.empty();

                if (isVertical) {
                    const $tbody = $('<tbody></tbody>');

                    tableHeaders.forEach(field => {
                        const $tr = $('<tr></tr>');
                        const $th = $('<th></th>')
                            .text(field.label)
                            .addClass('sortable-row')
                            .attr('data-key', field.key);
                        if (currentSort.key === field.key) {
                            $th.addClass(currentSort.isDesc ? 'desc' : 'asc');
                        }

                        $th.on('click', function() {
                            currentSort.isDesc = currentSort.key === field.key ? !currentSort.isDesc : false;     
                            currentSort.key = field.key;
                            renderTable(sortRooms(roomsToCompare, currentSort.key, currentSort.isDesc));
                        });
                        $tr.append($th);

                        data.forEach(room => {
                            const $td = $('<td></td>');
                            if (field.key === 'type') $td.css('text-align', 'center');
                            $tr.append($td);
                        });
                        $tbody.append($tr);
                    });
                    $compareTable.append($tbody);
                } else {
                    const $thead = $('<thead></thead>');
                    const $trHead = $('<tr></tr>');

                    tableHeaders.forEach(field => {
                        const $th = $('<th></th>')
                            .text(field.label)
                            .addClass('sortable-row')
                            .attr('data-key', field.key);
                        if (currentSort.key === field.key) {
                            $th.addClass(currentSort.isDesc ? 'desc' : 'asc');
                        }
                        $th.on('click', function() {
                            currentSort.isDesc = currentSort.key === field.key ? !currentSort.isDesc : false;     
                            currentSort.key = field.key;
                            renderTable(sortRooms(roomsToCompare, currentSort.key, currentSort.isDesc));
                        });
                        $trHead.append($th);
                    });
                    $thead.append($trHead);
                    $compareTable.append($thead);

                    const $tbody = $('<tbody></tbody>');
                    data.forEach(room => {
                        const $tr = $('<tr></tr>');
                        tableHeaders.forEach(field => {
                            const $td = $('<td></td>')
                                .attr('data-label', field.label);
                            $tr.append($td);
                        });
                        $tbody.append($tr);
                    });
                    $compareTable.append($tbody);
                }

                isTableCreated = true;
            }

            $compareTable.find('th.sortable-row')
                .removeClass('asc desc')
                .filter(`[data-key="${currentSort.key}"]`)
                .addClass(currentSort.isDesc ? 'desc' : 'asc');

            const $rows = $compareTable.find('tbody tr');
            if (isVertical) {
                tableHeaders.forEach((field, rowIndex) => {
                    const $tds = $rows.eq(rowIndex).find('td');
                    data.forEach((room, colIndex) => {
                        $tds.eq(colIndex)
                            .html(formatCell(room, field.key));
                    });
                });
            } else {
                data.forEach((room, rowIndex) => {
                    const $tds = $rows.eq(rowIndex).find('td');
                    tableHeaders.forEach((field, colIndex) => {
                        $tds.eq(colIndex)
                            .html(formatCell(room, field.key));
                    });
                });
            }
        }

        renderTable(roomsToCompare);
    }

    $('.compare-tree').children('li').on('click', function(e) {
        $(this).toggleClass('open');
        $(this).children('.nested-sublist').slideToggle(200);
    });
});