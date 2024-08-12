// import './bootstrap.js';
/*
 * Welcome to your app's main JavaScript file!
 *
 * This file will be included onto the page via the importmap() Twig function,
 * which should already be in your base.html.twig.
 */
import './styles/app.css';




const burgerMenu = document.querySelector(".burgerMenu");
const headerDivSearch = document.querySelector(".headerDivSearch");
const headerSearchOverlay = document.querySelector(".headerSearchOverlay");
const headerTop_right_profil = document.querySelector(".headerTop_right_profil");
// const headerTop_right_cart = document.querySelector(".headerTop_right_cart");
const divCloseHeaderSearch = document.querySelector(".divCloseHeaderSearch");
const inputHeaderSearch = document.querySelector(".inputHeaderSearch");
const headerMenuOverlay = document.querySelector(".headerMenuOverlay");
const profilMenuMobile = document.querySelector(".profilMenuMobile");
const divCloseprofilMenu = document.querySelector(".divCloseprofilMenu");
const headerSearchDesktop = document.querySelector(".headerSearchDesktop");
const headerCroixSearchDesktop = document.querySelector(".headerCroixSearchDesktop");
const deleteSearchDesktop = document.querySelector(".deleteSearchDesktop");
const profilMenuDesktop = document.querySelector(".profilMenuDesktop");
const searchDeleteInputMobile = document.querySelector(".searchDeleteInputMobile");

if (window.matchMedia("(max-width: 1080px)").matches){

    [headerTop_right_profil, divCloseprofilMenu].forEach(element =>{

        element.addEventListener('click', () => {
            burgerMenu.classList.remove("activeSpan");
            headerSearchOverlay.classList.remove('headerSearchOverlayActive');
            headerMenuOverlay.classList.remove("headerMenuOverlayActive");
            profilMenuMobile.classList.toggle('profilMenuMobileActive');
        });
    });
}else{
    headerTop_right_profil.addEventListener('click', () => {
        profilMenuDesktop.classList.toggle('profilMenuDesktopActive');
    });
    document.addEventListener('click', (event) => {
        const isClickInside1 = headerTop_right_profil.contains(event.target);
        const isClickInside2 = profilMenuDesktop.contains(event.target);

        if (!isClickInside1 && !isClickInside2) {
            profilMenuDesktop.classList.remove('profilMenuDesktopActive');
        }
    });

}


burgerMenu.addEventListener("click", function () {
    this.classList.toggle("activeSpan");
    headerMenuOverlay.classList.toggle("headerMenuOverlayActive");
    headerSearchOverlay.classList.remove('headerSearchOverlayActive');
    profilMenuMobile.classList.remove('profilMenuMobileActive');
});

//ICONE LOUPE MOBILE
headerDivSearch.addEventListener('click', () => {
    burgerMenu.classList.remove("activeSpan");
    headerMenuOverlay.classList.remove("headerMenuOverlayActive");
    profilMenuMobile.classList.remove('profilMenuMobileActive');
    headerSearchOverlay.classList.toggle('headerSearchOverlayActive');

});

//ICONE CROIX SEARCH MOBILE
divCloseHeaderSearch.addEventListener('click', () => {
    headerSearchOverlay.classList.toggle('headerSearchOverlayActive');
});

//INPUT SEARCH MOBILE
inputHeaderSearch.addEventListener('keyup', () => {
    searchDeleteInputMobile.style.display = inputHeaderSearch.value ? "block" : "none";
    if (this.value === ""){
        searchDeleteInputMobile.style.display="none";
    }
});
searchDeleteInputMobile.addEventListener('click', () => {
    inputHeaderSearch.value = "";
    this.style.display="none";
})


//Mobile accordion

const accordionItemHeaders = document.querySelectorAll(
    ".accordion-item-header"
);

accordionItemHeaders.forEach((accordionItemHeader) => {
    accordionItemHeader.addEventListener("click", (event) => {

        //Parmis tous les éléments, on selectionne celui qui à la classe active (Il peut ne pas existé à ce moment là)
        const currentlyActiveAccordionItemHeader = document.querySelector(
            ".accordion-item-header.active"
        );

        if (
            //S'il en existe bien un, et que cet élément est différent de celui que l'on clique
            currentlyActiveAccordionItemHeader &&
            currentlyActiveAccordionItemHeader !== accordionItemHeader
        ) {
            console.log(currentlyActiveAccordionItemHeader)
            currentlyActiveAccordionItemHeader.classList.remove("active");
            currentlyActiveAccordionItemHeader.nextElementSibling.style.maxHeight = 0;
        }


        //on ajoute ou on supprime la classe "active" de l'élément cliqué
        accordionItemHeader.classList.toggle("active");

        const accordionItemBody = accordionItemHeader.nextElementSibling;

        //si l'élément cliqué a obtenu un ajout de classe
        if (accordionItemHeader.classList.contains("active")) {
            //alors on augmente la hauteur de l'élément suivant
            accordionItemBody.style.maxHeight = accordionItemBody.scrollHeight + "px";

        } else {
            // si au contraire on lui a supprimé, on réduit sa hauteur
            accordionItemBody.style.maxHeight = 0;
        }
    });
});

headerSearchDesktop.addEventListener('click', () => {
    headerSearchDesktop.classList.add('headerSearchDesktopActive');
    headerSearchDesktop.setAttribute('placeholder', 'Votre recherche...');
    headerCroixSearchDesktop.style.display='block';

    headerSearchDesktop.addEventListener('keyup', () => {
        if (headerSearchDesktop.value !== ""){
            deleteSearchDesktop.style.display="block"
            deleteSearchDesktop.addEventListener('click', () =>{
                headerSearchDesktop.value = "";
                deleteSearchDesktop.style.display="none"
            })
        }

    })

    headerCroixSearchDesktop.addEventListener('click', () =>{
        headerSearchDesktop.classList.remove('headerSearchDesktopActive');
        headerSearchDesktop.removeAttribute('placeholder');
        deleteSearchDesktop.style.display="none"
        headerSearchDesktop.value ="";
        headerCroixSearchDesktop.style.display='none';
    })

})


// MESSAGES FLASH

document.querySelectorAll('.hideFlashMessage').forEach(icon => {
    icon.addEventListener('click', () => icon.parentNode.style.display = "none");
});
