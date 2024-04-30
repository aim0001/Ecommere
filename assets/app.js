import './bootstrap.js';
/*
 * Welcome to your app's main JavaScript file!
 *
 * This file will be included onto the page via the importmap() Twig function,
 * which should already be in your base.html.twig.
 */
import './styles/app.css';

    function showContent(tabId) {
    const tabs = document.querySelectorAll('.tab');
    const tabContents = document.querySelectorAll('.tab-content');

    tabs.forEach(tab => tab.classList.remove('active-tab'));
    tabContents.forEach(content => content.classList.remove('active-content'));

    const selectedTab = document.getElementById(tabId);
    selectedTab.classList.add('active-content');

    const correspondingTab = Array.from(tabs).find(tab => tab.getAttribute('onclick').includes(tabId));
    if (correspondingTab) {
        correspondingTab.classList.add('active-tab');
    }
}

//image defilement

// script.js
let slideIndex = 0;

function showSlides() {
    let slides = document.getElementsByClassName("slide");
    for (let i = 0; i < slides.length; i++) {
        slides[i].style.display = "none";
    }
    slideIndex++;
    if (slideIndex > slides.length) {
        slideIndex = 1;
    }
    slides[slideIndex - 1].style.display = "block";
    setTimeout(showSlides, 5000); // Change d'image toutes les 2 secondes (2000 ms)
}

// Démarrez le diaporama lorsque la page est chargée
window.onload = function () {
    showSlides();
};
