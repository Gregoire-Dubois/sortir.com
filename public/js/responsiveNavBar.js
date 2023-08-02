// Récupération des éléments du DOM
const hbgUserMenuIcon = document.querySelector('.hbg_user_menu_icon');
const headerMenuNav = document.querySelector('.header_menu_nav');

// Affichage de la navbar lors du click sur le hamburger
hbgUserMenuIcon.addEventListener('click', () => {
  headerMenuNav.classList.toggle('show');
});