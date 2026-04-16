import { discoverLocations } from './data.js';

document.addEventListener('DOMContentLoaded', function() {
    const resultsTable = document.getElementById('discover-results-table');
    
    function renderDiscoverLocations(locations) {
        if (!resultsTable) return;
        resultsTable.innerHTML = '';

        locations.forEach(loc => {
            const tr = document.createElement('tr');
            tr.className = 'result-card';
            
            tr.innerHTML = `
                <td class="result-image-col">
                    <img src="${loc.image}" alt="${loc.name}">
                </td>
                <td class="result-info-col">
                    <div class="info-top">
                        <h3 class="card-title">${loc.name}</h3>
                        <span class="rating-badge">${loc.rating}</span>
                        <p class="location-text"><strong>${loc.city}</strong> • ${loc.distance}</p>
                        <p class="room-type">${loc.roomType}</p>
                    </div>
                    
                    <div class="card-actions">
                        <div class="price-display">
                            <span class="price-val">${loc.price} RON</span> <span class="price-currency">/ night</span>
                        </div>
                        <button class="add-to-bag-btn" data-id="${loc.id}">+ Add to Bag</button>
                    </div>
                </td>
            `;
            resultsTable.appendChild(tr);
        });

        attachBagEvents();
    }

    if (typeof discoverLocations !== 'undefined') {
        renderDiscoverLocations(discoverLocations);
    }

    const bagToggleBtn = document.getElementById('bag-toggle');
    const closeBagBtn = document.getElementById('close-bag');
    const bagPanel = document.getElementById('bag-panel');
    const bagBadge = document.getElementById('bag-badge');
    const bagContent = document.getElementById('bag-content');
    
    let savedRooms = JSON.parse(localStorage.getItem('myTravelBag')) || [];

    function renderBag() {
        if (!bagBadge || !bagContent) return;
        bagBadge.textContent = savedRooms.length;

        if (savedRooms.length === 0) {
            bagContent.innerHTML = '<p class="empty-msg">Your bag is empty. Start exploring!</p>';
            return;
        }

        bagContent.innerHTML = ''; 

        let totalPrice = 0;

        savedRooms.forEach(roomId => {
            const locData = discoverLocations.find(r => r.id === roomId);
            if (!locData) return;

            totalPrice += locData.price;

            const itemDiv = document.createElement('div');
            itemDiv.className = 'bag-item';
            itemDiv.innerHTML = `
                <div class="bag-item-layout">
                    <img src="${locData.image}" alt="hotel" class="bag-item-image">
                    <div class="bag-item-details">
                        <h4 class="bag-item-title">${locData.name}</h4>
                        <p><strong>~${locData.price} RON</strong> / night</p>
                    </div>
                </div>
                <button class="remove-item-btn" data-id="${locData.id}" aria-label="Remove">&times;</button>
            `;
            bagContent.appendChild(itemDiv);
        });

        document.querySelectorAll('.remove-item-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const idToRemove = this.getAttribute('data-id');
                savedRooms = savedRooms.filter(id => id !== idToRemove);
                localStorage.setItem('myTravelBag', JSON.stringify(savedRooms));
                renderBag();
            });
        });
    }

    function attachBagEvents() {
        const addToBagButtons = document.querySelectorAll('.add-to-bag-btn');
        addToBagButtons.forEach(btn => {
            btn.addEventListener('click', function() {
                const roomId = this.getAttribute('data-id');
                
                if (!savedRooms.includes(roomId)) {
                    savedRooms.push(roomId);
                    localStorage.setItem('myTravelBag', JSON.stringify(savedRooms));
                    renderBag();
                    bagPanel.classList.add('open');
                } else {
                    alert('This location is already in your bag!');
                }
            });
        });
    }

    if (bagToggleBtn) bagToggleBtn.addEventListener('click', () => bagPanel.classList.add('open'));
    if (closeBagBtn) closeBagBtn.addEventListener('click', () => bagPanel.classList.remove('open'));

    renderBag();
});