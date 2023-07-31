// Récupération des éléments du DOM
const popupUpdateEntity = document.getElementById('popup_update_entity');
const popupFormUpdateEntity = popupUpdateEntity.querySelector('.update_form_ville, .update_form_campus');
const popupTitleUpdateEntity = document.getElementById('popup_update_entity_title');
const popupOverlay = document.getElementById('popup_overlay');

// Fonction pour ouvrir le popup de modification de la ville ou du campus
function ouvrirModalModifier(type, entityId) {
  // Récupération des informations spécifiques de la ville/campus via une requête AJAX
  fetch('/admin/' + type + '/' + entityId + '/details')
    .then(response => response.json())
    .then(data => {
      const nomInput = popupFormUpdateEntity.querySelector('.nom_input');

      nomInput.value = data.nom;
      // Ajoutez ici d'autres champs du formulaire pour le campus
    })
    .catch(error => {
      console.error('Une erreur s\'est produite lors de la récupération des informations du campus :', error);
    });

  // Affichage du popup
  popupUpdateEntity.style.display = 'block';
  popupFormUpdateEntity.classList.add(type);
  popupTitleUpdateEntity.textContent = 'Modifier ' + (type === 'ville' ? 'une ville' : 'un campus');
  popupOverlay.style.display = 'block';
}

// Fonction pour masquer le popup
function masquerPopup() {
  popupUpdateEntity.style.display = 'none';
  popupFormUpdateEntity.classList.remove('ville', 'campus');
  popupOverlay.style.display = 'none';
}

// Ajouter les écouteurs d'événements pour les boutons "Modifier" des villes et des campus
const updateButtons = document.querySelectorAll('.modifier_ville_button, .modifier_campus_button');
updateButtons.forEach(button => {
  const entityType = button.classList.contains('modifier_ville_button') ? 'ville' : 'campus';
  const entityId = button.getAttribute('data-' + entityType + '-id');
  button.addEventListener('click', function(event) {
    event.preventDefault();
    ouvrirModalModifier(entityType, entityId);
  });
});

// Ajouter les écouteurs d'événements pour les boutons de confirmation de modification
const confirmButtons = popupFormUpdateEntity.querySelectorAll('.confirm_update_button');
confirmButtons.forEach(button => {
  button.addEventListener('click', function() {
    // Code pour enregistrer les modifications dans la base de données
    masquerPopup();
  });
});

// Ajouter les écouteurs d'événements pour les boutons d'annulation de modification
const cancelButtons = popupFormUpdateEntity.querySelectorAll('.cancel_update_button');
cancelButtons.forEach(button => {
  button.addEventListener('click', function() {
    masquerPopup();
  });
});
