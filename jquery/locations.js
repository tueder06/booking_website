const $resultsTable = $('#discover-results-table');
const $filterForm = $('#filter-form');
const $errorContainer = $('#locations-error-container');
const $btnPrev = $('#btn-prev');
const $btnNext = $('#btn-next');
const $pageIndicator = $('#page-indicator');

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
    if (!$resultsTable.length) return;
    $resultsTable.empty();

    if (!locations || locations.length === 0) {
        $resultsTable.html('<tr><td colspan="2" style="text-align:center; padding: 20px;">No properties found.</td></tr>');
        return;
    }

    locations.forEach(loc => {
        const ratingText = getRatingText(loc.rating);
        const distanceText = `${loc.distance} km from center`;

        const $tr = $('<tr>').addClass('result-card');
        
        const $tdImage = $('<td>').addClass('result-image-col')
            .append($('<img>').attr('src', loc.image).attr('alt', loc.name));

        const $tdInfo = $('<td>').addClass('result-info-col');
        const $infoTop = $('<div>').addClass('info-top');

        $infoTop.append(
            $('<h3>').addClass('card-title').text(loc.name),
            $('<span>').addClass('rating-badge').text(ratingText),
            $('<p>').addClass('location-text').append(
                $('<strong>').text(loc.city),
                document.createTextNode(` • ${distanceText}`)
            ),
            $('<p>').addClass('room-type').text(loc.roomType)
        );

        const $cardActions = $('<div>').addClass('card-actions');
        $cardActions.append(
            $('<div>').addClass('price-display').append(
                $('<span>').addClass('price-val').text(`${loc.price} RON`),
                $('<span>').addClass('price-currency').text(' / night')
            ),
            $('<button>').addClass('add-to-bag-btn').attr('data-id', loc.id).text('+ Add to Bag'),
            $('<button>').addClass('book-now-btn').attr('data-id', loc.id).text('Book Now')
        );

        $tdInfo.append($infoTop, $cardActions);
        $tr.append($tdImage, $tdInfo);
        
        $resultsTable.append($tr);
    });

    attachBagEvents();
}

function updatePaginationControls() {
    const totalPages = Math.ceil(totalRecords / k_items) || 1;
    $pageIndicator.text(`Page ${currentPage} of ${totalPages}`);
    $btnPrev.prop('disabled', currentPage <= 1);
    $btnNext.prop('disabled', currentPage >= totalPages);
}

let lastValidQueryString = "";
export async function fetchProperties(page, useCurrentForm = false) {
    try {
        $errorContainer.hide();

        const queryParams = useCurrentForm ? new URLSearchParams(new FormData($filterForm[0])) : new URLSearchParams(lastValidQueryString);
        if(useCurrentForm) lastValidQueryString = queryParams.toString();

        queryParams.append('page', page);
        queryParams.append('limit', k_items);

        const url = `includes/filter_properties.php?${queryParams.toString()}`;
        
        await new Promise((resolve, reject) => {
            const xhr = new XMLHttpRequest();
            xhr.open('GET', url, true);
            xhr.responseType = 'json';

            xhr.onreadystatechange = function() {
                if (xhr.readyState === 4) {
                    if (xhr.status === 200 || xhr.status === 400) {
                        let response;
                        try {
                            response = JSON.parse(xhr.responseText);
                        } catch (e) {
                            reject("Invalid JSON format");
                            return;
                        }

                        if (response.success) {
                            totalRecords = response.total || 0;
                            currentPage = page;

                            renderDiscoverLocations(response.data);
                            updatePaginationControls();
                            resolve();
                        } else {
                            if (response.errors) {
                                $.each(response.errors, function(fieldKey, message) {
                                    $(`#${fieldKey}-error`).text(message).show();
                                });
                            }
                            
                            $resultsTable.empty();
                            totalRecords = 0;
                            updatePaginationControls();
                            resolve();
                        }
                    } else {
                        if ($errorContainer.length) {
                            $errorContainer.text("Server error. Status: " + xhr.status).show();
                        }
                        reject("HTTP Status: " + xhr.status);
                    }
                }
            }
            xhr.send();
        });
    } catch (error) {
        if ($errorContainer.length) {
            $errorContainer.text("Connection lost. Could not load the data").show();
        }
        $resultsTable.empty();
    }
}

$btnPrev.on('click', () => { if (currentPage > 1) fetchProperties(currentPage - 1, false); });

$btnNext.on('click', () => { 
    const totalPages = Math.ceil(totalRecords / k_items);
    if (currentPage < totalPages) fetchProperties(currentPage + 1, false); 
});

const $bagToggleBtn = $('#bag-toggle');
const $closeBagBtn = $('#close-bag');
const $bagPanel = $('#bag-panel');
const $bagBadge = $('#bag-badge');
const $bagContent = $('#bag-content');

let savedRooms = JSON.parse(localStorage.getItem('myTravelBag')) || [];

function renderBag() {
    const allLocations = typeof discoverLocations !== 'undefined' ? discoverLocations : [];
    savedRooms = savedRooms.filter(id => allLocations.some(r => r.id === Number(id)));
    localStorage.setItem('myTravelBag', JSON.stringify(savedRooms));

    $bagBadge.text(savedRooms.length);
    $bagContent.empty();

    if (savedRooms.length === 0) {
        $bagContent.append('<p class="empty-msg">Your bag is empty. Start exploring!</p>');
        return;
    }

    savedRooms.forEach(roomId => {
        const locData = allLocations.find(r => r.id === Number(roomId));
        if (!locData) return;

        $bagContent.append(
            $('<div>').addClass('bag-item').append(
                $('<div>').addClass('bag-item-layout').append(
                    $('<img>').attr('src', locData.image).addClass('bag-item-image'),
                    $('<div>').addClass('bag-item-details').append(
                        $('<h4>').addClass('bag-item-title').text(locData.name),
                        $('<p>').append($('<strong>').text(`~${locData.price} RON`), ' / night')
                    )
                ),
                $('<button>').addClass('remove-item-btn').attr('data-id', locData.id).html('&times;')
            )
        );
    });
}

$(document).on('click', '.remove-item-btn', function() {
    const idToRemove = $(this).attr('data-id');
    savedRooms = savedRooms.filter(id => id !== idToRemove);
    localStorage.setItem('myTravelBag', JSON.stringify(savedRooms));
    renderBag();
});

function attachBagEvents() {
    $('.add-to-bag-btn').off('click').on('click', function() {
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

$bagToggleBtn.on('click', () => $bagPanel.addClass('open'));
$closeBagBtn.on('click', () => $bagPanel.removeClass('open'));

renderBag();