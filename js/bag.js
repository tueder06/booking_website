document.addEventListener('DOMContentLoaded', () => {
    const bagToggleBtn = document.getElementById('bag-toggle');
    const closeBagBtn = document.getElementById('close-bag');
    const bagPanel = document.getElementById('bag-panel');
    const bagBadge = document.getElementById('bag-badge');
    const bagContent = document.getElementById('bag-content');

    let savedRooms = JSON.parse(localStorage.getItem('myTravelBag')) || [];

    function renderBag() {
        if (!bagBadge || !bagContent) return;
        
        const allLocations = typeof discoverLocations !== 'undefined' ? discoverLocations : [];

        const validRooms = savedRooms.filter(roomId => {
            return allLocations.some(r => r.id === Number(roomId));
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
            const locData = allLocations.find(r => r.id === Number(roomId));
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

    attachBagEvents();
    renderBag();
});

