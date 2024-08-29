gsap.registerPlugin(ScrollTrigger);

const projectHeader = document.getElementById("h1_page_title");
const projectGallery = document.querySelector(".project_gallery");
const imagesBottom = projectGallery.querySelectorAll("img");
// const imagesTop = document.querySelectorAll(".project_header");

// Pin le h1 à la page
ScrollTrigger.create({
    trigger: "#h1_page_title",
    start: "top top", 
    endTrigger: ".project_header",
    end: "top 60px",
    pin: true,
    pinSpacing: false
});

// Configuration initiale des images (position hors écran)
imagesBottom.forEach((imgBottom, index) => {
    if (index === 0) {
        gsap.set(imgBottom, { xPercent: -100, opacity: 0 });
    } else {
        gsap.set(imgBottom, { xPercent: 100, opacity: 0 });
    }
});

// Fonction pour animer les images
function animateImage(imgBottom, delay) {
    gsap.to(imgBottom, {
        xPercent: 0,  // Ramène l'image à sa position d'origine
        opacity: 1,   // Rend l'image visible
        duration: 2,
        ease: "bounce.out", // Easing pour une animation fluide
        delay: delay,
        scrollTrigger: {
            trigger: "#h1_page_title", // Déclenche l'animation 
            start: "top top",
            toggleActions: "play none none none", // Joue l'animation à l'arrivée
        }
    });
}

// Lancer l'animation pour chaque image avec un délai différent
imagesBottom.forEach((imgBottom, index) => {
    animateImage(imgBottom, 0.2 + index * 0.2);
});
