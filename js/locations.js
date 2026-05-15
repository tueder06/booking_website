document.addEventListener('DOMContentLoaded', function() {
    const resultsTable = document.getElementById('discover-results-table');
    
    function getRatingText(rating) {
        switch (true) {
            case rating >= 9: return `Excellent ${rating}`;
            case rating >= 8: return `Very Good ${rating}`;
            case rating >= 7: return `Good ${rating}`;
            case rating >= 5: return `Pleasant ${rating}`;
            default: return `Unpleasant ${rating}`;
        }
    }

    // function renderDiscoverLocations(locations) {
    //     if (!resultsTable) return;
    //     resultsTable.innerHTML = '';

    //     locations.forEach(loc => {
    //         const tr = document.createElement('tr');
    //         tr.className = 'result-card';
            
    //         let ratingText = getRatingText(loc.rating);
    //         let distanceText = `${loc.distance} km from center`;

    //         tr.innerHTML = `
    //             <td class="result-image-col">
    //                 <img src="${loc.image}" alt="${loc.name}">
    //             </td>
    //             <td class="result-info-col">
    //                 <div class="info-top">
    //                     <h3 class="card-title">${loc.name}</h3>
    //                     <span class="rating-badge">${ratingText}</span>
    //                     <p class="location-text"><strong>${loc.city}</strong> • ${distanceText}</p>
    //                     <p class="room-type">${loc.roomType}</p>
    //                 </div>
                    
    //                 <div class="card-actions">
    //                     <div class="price-display">
    //                         <span class="price-val">${loc.price} RON</span> <span class="price-currency">/ night</span>
    //                     </div>
    //                     <button class="add-to-bag-btn" data-id="${loc.id}">+ Add to Bag</button>
    //                 </div>
    //             </td>
    //         `;
    //         resultsTable.appendChild(tr);
    //     });

    //     attachBagEvents();
    // }
    function renderDiscoverLocations(locations) {
        if (!resultsTable) return;
        resultsTable.innerHTML = '';

        locations.forEach(loc => {
            const tr = document.createElement('tr');
            tr.className = 'result-card';
            
            let ratingText = getRatingText(loc.rating);
            let distanceText = `${loc.distance} km from center`;

            const tdImage = document.createElement('td');
            tdImage.className = 'result-image-col';
            const img = document.createElement('img');
            img.src = loc.image;
            img.alt = loc.name;
            tdImage.appendChild(img);

            const tdInfo = document.createElement('td');
            tdInfo.className = 'result-info-col';

            const infoTop = document.createElement('div');
            infoTop.className = 'info-top';
            
            const title = document.createElement('h3');
            title.className = 'card-title';
            title.textContent = loc.name;

            const badge = document.createElement('span');
            badge.className = 'rating-badge';
            badge.textContent = ratingText;

            const locationP = document.createElement('p');
            locationP.className = 'location-text';
            const strongCity = document.createElement('strong');
            strongCity.textContent = loc.city;
            locationP.appendChild(strongCity);
            locationP.appendChild(document.createTextNode(` • ${distanceText}`));

            const roomP = document.createElement('p');
            roomP.className = 'room-type';
            roomP.textContent = loc.roomType;

            infoTop.append(title, badge, locationP, roomP);

            const cardActions = document.createElement('div');
            cardActions.className = 'card-actions';

            const priceDiv = document.createElement('div');
            priceDiv.className = 'price-display';
            
            const priceVal = document.createElement('span');
            priceVal.className = 'price-val';
            priceVal.textContent = `${loc.price} RON`;
            
            const priceCurrency = document.createElement('span');
            priceCurrency.className = 'price-currency';
            priceCurrency.textContent = ' / night';
            
            priceDiv.append(priceVal, priceCurrency);

            const btn = document.createElement('button');
            btn.className = 'add-to-bag-btn';
            btn.setAttribute('data-id', loc.id);
            btn.textContent = '+ Add to Bag';

            cardActions.append(priceDiv, btn);

            tdInfo.append(infoTop, cardActions);
            tr.append(tdImage, tdInfo);
            
            resultsTable.appendChild(tr);
        });

        attachBagEvents();
    }

    if (typeof discoverLocationsFiltered !== 'undefined') {
        renderDiscoverLocations(discoverLocationsFiltered);
    }

    const bagToggleBtn = document.getElementById('bag-toggle');
    const closeBagBtn = document.getElementById('close-bag');
    const bagPanel = document.getElementById('bag-panel');
    const bagBadge = document.getElementById('bag-badge');
    const bagContent = document.getElementById('bag-content');
    
    let savedRooms = JSON.parse(localStorage.getItem('myTravelBag')) || [];

    function renderBag() {
        if (!bagBadge || !bagContent) return;

        const validRooms = savedRooms.filter(roomId => {
            return discoverLocations.some(r => r.id === Number(roomId));
        });

        if (validRooms.length !== savedRooms.length) {
            localStorage.setItem('myTravelBag', JSON.stringify(validRooms));
            savedRooms = validRooms;
        }

        bagBadge.textContent = savedRooms.length;

        if (savedRooms.length === 0) {
            bagContent.innerHTML = '<p class="empty-msg">Your bag is empty. Start exploring!</p>';
            return;
        }

        bagContent.innerHTML = '';

        savedRooms.forEach(roomId => {
            const locData = discoverLocations.find(r => r.id === Number(roomId));
            if (!locData) return;

            const itemDiv = document.createElement('div');
            itemDiv.className = 'bag-item';
            
            const layoutDiv = document.createElement('div');
            layoutDiv.className = 'bag-item-layout';

            const img = document.createElement('img');
            img.src = locData.image;
            img.alt = "hotel";
            img.className = 'bag-item-image';

            const detailsDiv = document.createElement('div');
            detailsDiv.className = 'bag-item-details';

            const title = document.createElement('h4');
            title.className = 'bag-item-title';
            title.textContent = locData.name;

            const priceP = document.createElement('p');
            const strongPrice = document.createElement('strong');
            strongPrice.textContent = `~${locData.price} RON`;
            priceP.appendChild(strongPrice);
            priceP.appendChild(document.createTextNode(' / night'));

            detailsDiv.append(title, priceP);
            layoutDiv.append(img, detailsDiv);

            const removeBtn = document.createElement('button');
            removeBtn.className = 'remove-item-btn';
            removeBtn.setAttribute('data-id', locData.id);
            removeBtn.setAttribute('aria-label', 'Remove');
            removeBtn.innerHTML = '&times;';

            itemDiv.append(layoutDiv, removeBtn);
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