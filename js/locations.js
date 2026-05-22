const resultsTable = document.getElementById('discover-results-table');
const filterForm = document.getElementById('filter-form');
const errorContainer = document.getElementById('locations-error-container');
const btnPrev = document.getElementById('btn-prev');
const btnNext = document.getElementById('btn-next');
const pageIndicator = document.getElementById('page-indicator');

export let currentPage = 1;
const k_items = 5;
let totalRecords = 0;

function getRatingText(rating) {
    switch (true) {
        case rating >= 9: return `Excellent ${rating}`;
        case rating >= 8: return `Very Good ${rating}`;
        case rating >= 7: return `Good ${rating}`;
        case rating >= 5: return `Pleasant ${rating}`;
        default: return `Unpleasant ${rating}`;
    }
}

function renderDiscoverLocations(locations) {
    if (!resultsTable) return;
    resultsTable.innerHTML = '';

    if (!locations || locations.length === 0) {
        resultsTable.innerHTML = '<tr><td colspan="2" style="text-align:center; padding: 20px;">No properties found.</td></tr>';
        return;
    }

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

        const btnBook = document.createElement('button');
        btnBook.className = 'book-now-btn';
        btnBook.setAttribute('data-id', loc.id);
        btnBook.textContent = 'Book Now';

        cardActions.append(priceDiv, btn, btnBook);
        tdInfo.append(infoTop, cardActions);
        tr.append(tdImage, tdInfo);
        
        resultsTable.appendChild(tr);
    });

    attachBagEvents();
}

function updatePaginationControls() {
    const totalPages = Math.ceil(totalRecords / k_items) || 1;

    if (pageIndicator) pageIndicator.textContent = `Page ${currentPage} of ${totalPages}`;
    if (btnPrev) btnPrev.disabled = (currentPage <= 1);
    if (btnNext) btnNext.disabled = (currentPage >= totalPages);
}

let lastValidQueryString = "";
export async function fetchProperties(page, useCurrentForm = false) {
    try {
        if(errorContainer) errorContainer.style.display = 'none';

        let queryParams;
        
        if (useCurrentForm) {
            const formData = new FormData(filterForm);
            queryParams = new URLSearchParams(formData);
            lastValidQueryString = queryParams.toString(); 
        } else {
            queryParams = new URLSearchParams(lastValidQueryString);
        }

        queryParams.append('page', page);
        queryParams.append('limit', k_items);
        
        const url = `includes/filter_properties.php?${queryParams.toString()}`;

        const response = await fetch(url);
        const jsonResponse = await response.json();

        if (jsonResponse.success) {
            totalRecords = jsonResponse.total;
            currentPage = page;
            renderDiscoverLocations(jsonResponse.data);
            updatePaginationControls();
        } else {
            if (jsonResponse.errors && Object.keys(jsonResponse.errors).length > 0) {
                for (const [fieldKey, errorMessage] of Object.entries(jsonResponse.errors)) {
                    const errorDiv = document.getElementById(`${fieldKey}-error`);
                    if (errorDiv) {
                        errorDiv.textContent = errorMessage;
                        errorDiv.style.display = 'block';
                    }
                }
            } else if(errorContainer) {
                errorContainer.textContent = jsonResponse.message;
                errorContainer.style.display = 'block';
            }
            resultsTable.innerHTML = '';
            totalRecords = 0;
            updatePaginationControls();
        }
        // await new Promise((resolve, reject) => {
        //     const xhr = new XMLHttpRequest();
        //     xhr.open('GET', url, true);
        //     xhr.responseType = 'document';

        //     xhr.onreadystatechange = function() {
        //         if (xhr.readyState === 4) {
        //             if (xhr.status === 200 || xhr.status === 400) {
        //                 const xmlDoc = xhr.responseXML;
        //                 const successNode = xmlDoc.getElementsByTagName('success')[0];
        //                 const isSuccess = successNode ? successNode.textContent === 'true' : false;

        //                 if (isSuccess) {
        //                     const properties = xmlDoc.getElementsByTagName('property');
        //                     const totalNode = xmlDoc.getElementsByTagName('total')[0];
                            
        //                     totalRecords = totalNode ? parseInt(totalNode.textContent) : 0;
        //                     currentPage = page;

        //                     const data = Array.from(properties).map(prop => ({
        //                         id: prop.getElementsByTagName('id')[0]?.textContent || '',
        //                         name: prop.getElementsByTagName('name')[0]?.textContent || '',
        //                         city: prop.getElementsByTagName('city')[0]?.textContent || '',
        //                         distance: prop.getElementsByTagName('distance')[0]?.textContent || '',
        //                         rating: parseFloat(prop.getElementsByTagName('rating')[0]?.textContent || 0),
        //                         roomType: prop.getElementsByTagName('roomType')[0]?.textContent || '',
        //                         price: prop.getElementsByTagName('price')[0]?.textContent || '0',
        //                         image: prop.getElementsByTagName('image')[0]?.textContent || ''
        //                     }));

        //                     renderDiscoverLocations(data);
        //                     updatePaginationControls();
        //                 } else {
        //                     const errorNodes = xmlDoc.getElementsByTagName('error');
        //                     for (let i = 0; i < errorNodes.length; i++) {
        //                         const err = errorNodes[i];
        //                         const fieldKey = err.getAttribute('field');
        //                         const errorDiv = document.getElementById(`${fieldKey}-error`);
        //                         if (errorDiv) {
        //                             errorDiv.textContent = err.textContent;
        //                             errorDiv.style.display = 'block';
        //                         }
        //                     }
        //                     resultsTable.innerHTML = '';
        //                     totalRecords = 0;
        //                     updatePaginationControls();
        //                 }
        //                 resolve();
        //             } else {
        //                 if (errorContainer) {
        //                     errorContainer.textContent = "Server error. Could not load XML.";
        //                     errorContainer.style.display = 'block';
        //                 }
        //                 reject("HTTP Status: " + xhr.status);
        //             }
        //         }
        //     };
        //     xhr.send();
        // });
    } catch (error) {
        if(errorContainer) {
            errorContainer.textContent = "Connection lost. Could not load the data.";
            errorContainer.style.display = 'block';
        }
        resultsTable.innerHTML = ''; 
    }
}

if (btnPrev) {
    btnPrev.addEventListener('click', () => {
        if (currentPage > 1) fetchProperties(currentPage - 1, false);
    });
}

if (btnNext) {
    btnNext.addEventListener('click', () => {
        const totalPages = Math.ceil(totalRecords / k_items);
        if (currentPage < totalPages) fetchProperties(currentPage + 1, false);
    });
}

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

renderBag();