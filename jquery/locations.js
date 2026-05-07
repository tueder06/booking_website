import { discoverLocations } from '../scripts/data.js';

$(document).ready(function() {
    const $resultsTable = $('#discover-results-table');

    function renderDiscoverLocations(locations) {
        if (!$resultsTable.length) return;
        $resultsTable.empty();

        locations.forEach(loc => {
            const $tr = $('<tr></tr>')
                .addClass('result-card')
                .html(`
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
                `);
            $resultsTable.append($tr);
        });

        attachBagEvents();
    }

    if (typeof discoverLocations !== 'undefined') {
        renderDiscoverLocations(discoverLocations);
    }

    const $bagToggleBtn = $('#bag-toggle');
    const $closeBagBtn = $('#close-bag');
    const $bagPanel = $('#bag-panel');
    const $bagBadge = $('#bag-badge');
    const $bagContent = $('#bag-content');

    let savedRooms = JSON.parse(localStorage.getItem('myTravelBag')) || [];

    function renderBag() {
        if (!$bagBadge.length || !$bagContent.length) return;
        $bagBadge.text(savedRooms.length);

        if (savedRooms.length === 0) {
            $bagContent.html('<p class="empty-msg">Your bag is empty. Start exploring!</p>');
            return;
        }

        $bagContent.empty();
        savedRooms.forEach(roomId => {
            const locData = discoverLocations.find(r => r.id === roomId);
            if (!locData) return;

            const $itemDiv = $('<div></div>')
                .addClass('bag-item')
                .html(`
                    <div class="bag-item-layout">
                        <img src="${locData.image}" alt="hotel" class="bag-item-image">
                        <div class="bag-item-details">
                            <h4 class="bag-item-title">${locData.name}</h4>
                            <p><strong>~${locData.price} RON</strong> / night</p>
                        </div>
                    </div>
                    <button class="remove-item-btn" data-id="${locData.id}" aria-label="Remove">&times;</button>
                `);
            $bagContent.append($itemDiv);
        });

        $('.remove-item-btn').on('click', function() {
            const idToRemove = $(this).attr('data-id');
            savedRooms = savedRooms.filter(id => id !== idToRemove);
            localStorage.setItem('myTravelBag', JSON.stringify(savedRooms));
            renderBag();
        });
    }

    function attachBagEvents() {
        $('.add-to-bag-btn').on('click', function() {
            const roomId = $(this).attr('data-id');
            if (!savedRooms.includes(roomId)) {
                savedRooms.push(roomId);
                localStorage.setItem('myTravelBag', JSON.stringify(savedRooms));
                renderBag();
                $bagPanel.addClass('open');
            } else {
                alert('This location is already in your bag!');
            }
        });
    }

    $bagToggleBtn.on('click', function() {
        $bagPanel.addClass('open');
    });
    $closeBagBtn.on('click', function() {
        $bagPanel.removeClass('open');
    });

    renderBag();
});