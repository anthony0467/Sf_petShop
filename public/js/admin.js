// Sélectionnez votre élément à modifier
const elementToChange = document.getElementById('menu-js'); // Remplacez 'votreElement' par l'ID de votre élément
let elementOriginalTop; // Variable pour stocker la position d'origine de l'élément

// Fonction pour vérifier la position et changer le style en conséquence
function changerPosition() {
  const scrollY = window.scrollY; // Récupérez la position de défilement en pixels depuis le haut
  if (scrollY >= 106) {
    // Si la position de défilement est supérieure ou égale à 106 pixels, changez la position à 'fixed'
    if (elementToChange.style.position !== 'fixed') {
      elementOriginalTop = elementToChange.getBoundingClientRect().top + scrollY;
      elementToChange.style.position = 'fixed';
      elementToChange.style.top = '0';
    }
  } else {
    // Sinon, gardez la position à 'absolute' (ou la valeur par défaut)
    if (elementToChange.style.position !== 'absolute') {
      elementToChange.style.position = 'absolute';
      elementToChange.style.top = elementOriginalTop + 'px';
    }
  }
}

// Écoutez l'événement de défilement
window.addEventListener('scroll', changerPosition);

// Appelez la fonction au chargement de la page pour vérifier la position initiale
changerPosition();