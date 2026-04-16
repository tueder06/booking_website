import { roomsToCompare } from './data.js';

document.addEventListener('DOMContentLoaded', function() {
    const compareTable = document.getElementById('compare-table');

    if (compareTable && typeof roomsToCompare !== 'undefined') {
        const isVertical = compareTable.classList.contains('vertical-mode');
        let currentSort = { key: null, isDesc: false };

        const fields = [
            { key: 'type', label: 'Accom. Type' },
            { key: 'name', label: 'Room Type' },
            { key: 'guests', label: 'Max Guests' },
            { key: 'bed', label: 'Bed Type' },
            { key: 'size', label: 'Room Size (m²)' },
            { key: 'view', label: 'View' },
            { key: 'bath', label: 'Bathroom Type' },
            { key: 'kitchen', label: 'Kitchenette' },
            { key: 'balcony', label: 'Balcony' },
            { key: 'price', label: 'Price / Night (RON)' }
        ];

        function formatCell(room, key) {
            if (key === 'size') return `~${room[key]} m²`;
            if (key === 'price') return `~${room[key]}`;
            if (key === 'type') return `<span class="type-icon ${room.icon}"></span>${room[key]}`;
            return room[key];
        }

        function sortRooms(data, key, isDesc) {
            return [...data].sort((a, b) => {
                let valA = a[key];
                let valB = b[key];
                
                if (typeof valA === 'string') valA = valA.toLowerCase();
                if (typeof valB === 'string') valB = valB.toLowerCase();

                if (valA < valB) return isDesc ? 1 : -1;
                if (valA > valB) return isDesc ? -1 : 1;
                return 0;
            });
        }

        function renderTable(data) {
            compareTable.innerHTML = '';

            if (isVertical) {
                const tbody = document.createElement('tbody');
                
                fields.forEach(field => {
                    const tr = document.createElement('tr');
                    
                    const th = document.createElement('th');
                    th.textContent = field.label;
                    th.classList.add('sortable-row');
                    if (currentSort.key === field.key) th.classList.add(currentSort.isDesc ? 'desc' : 'asc');

                    th.addEventListener('click', () => {
                        currentSort.isDesc = currentSort.key === field.key ? !currentSort.isDesc : false;
                        currentSort.key = field.key;
                        renderTable(sortRooms(roomsToCompare, currentSort.key, currentSort.isDesc));
                    });
                    tr.appendChild(th);

                    data.forEach(room => {
                        const td = document.createElement('td');
                        if (field.key === 'type') td.style.textAlign = 'center'; 
                        td.innerHTML = formatCell(room, field.key);
                        tr.appendChild(td);
                    });
                    tbody.appendChild(tr);
                });
                compareTable.appendChild(tbody);

            } else {
                const thead = document.createElement('thead');
                const trHead = document.createElement('tr');

                fields.forEach(field => {
                    const th = document.createElement('th');
                    th.textContent = field.label;
                    th.classList.add('sortable');
                    if (currentSort.key === field.key) th.classList.add(currentSort.isDesc ? 'desc' : 'asc');

                    th.addEventListener('click', () => {
                        currentSort.isDesc = currentSort.key === field.key ? !currentSort.isDesc : false;
                        currentSort.key = field.key;
                        renderTable(sortRooms(roomsToCompare, currentSort.key, currentSort.isDesc));
                    });
                    trHead.appendChild(th);
                });
                thead.appendChild(trHead);
                compareTable.appendChild(thead);

                const tbody = document.createElement('tbody');
                data.forEach(room => {
                    const tr = document.createElement('tr');
                    fields.forEach(field => {
                        const td = document.createElement('td');
                        td.setAttribute('data-label', field.label);
                        td.innerHTML = formatCell(room, field.key);
                        tr.appendChild(td);
                    });
                    tbody.appendChild(tr);
                });
                compareTable.appendChild(tbody);
            }
        }

        renderTable(roomsToCompare);
    }

    const compareItems = document.querySelectorAll('.compare-tree > li');
    compareItems.forEach(item => {
        item.addEventListener('click', function() {
            this.classList.toggle('open');
        });
    });
});