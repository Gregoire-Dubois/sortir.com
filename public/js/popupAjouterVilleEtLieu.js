// Récupération des éléments du DOM
const addCityButton = document.getElementById('add_city_button');
const addPlaceButton = document.getElementById('add_place_button');

const popupAddCity = document.getElementById('popup_add_city');
const popupFormAddCity = document.getElementById('popup_form_add_city');
const popupTitleAddCity = document.getElementById('popup_add_city_title');

const popupAddPlace = document.getElementById('popup_add_place');
const popupFormAddPlace = document.getElementById('popup_form_add_place');
const popupTitleAddPlace = document.getElementById('popup_add_place_title');

const popupOverlay = document.getElementById('popup_overlay');

const confirmCityButton = document.getElementById('confirm_add_city_button');
const cancelCityButton = document.getElementById('cancel_add_city_button');

const confirmPlaceButton = document.getElementById('confirm_add_place_button');
const cancelPlaceButton = document.getElementById('cancel_add_place_button');

// Affichage du popup
/*
addCityButton.addEventListener('click', function(event) {
    event.preventDefault();
    popupAddCity.style.display = 'block';
    popupFormAddCity.style.display = 'grid';
    popupTitleAddCity.style.display = 'flex';
    popupOverlay.style.display = 'block';
});
*/
addPlaceButton.addEventListener('click', function(event) {
    event.preventDefault();
    popupAddPlace.style.display = 'block';
    popupFormAddPlace.style.display = 'grid';
    popupTitleAddPlace.style.display = 'flex';
    popupOverlay.style.display = 'block';
});

// Disparition du popup "Ajouter une ville" - Fonction
/*
function hideCityPopup() {
    popupAddCity.style.display = 'none';
    popupFormAddCity.style.display = 'none';
    popupTitleAddCity.style.display = 'none';
    popupOverlay.style.display = 'none';
}
*/
// Disparition du popup "Ajouter un lieu" - Fonction
function hidePlacePopup() {
    popupAddPlace.style.display = 'none';
    popupFormAddPlace.style.display = 'none';
    popupTitleAddPlace.style.display = 'none';
    popupOverlay.style.display = 'none';
}
/*
// Disparition du popup "Ajouter une ville" - Boutons
confirmCityButton.addEventListener('submit', function() {
    // Code pour ajouter la ville
    hideCityPopup();
});

cancelCityButton.addEventListener('click', function() {
    hideCityPopup();
});
/*
// Disparition du popup "Ajouter un lieu" - Boutons
confirmPlaceButton.addEventListener('submit', function() {
    // Code pour ajouter le lieu
    hidePlacePopup();
});
*/
cancelPlaceButton.addEventListener('click', function() {
    hidePlacePopup();
});
