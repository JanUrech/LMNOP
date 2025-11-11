// Bubble-Bilder Array
const bubbleImages = [
    'media/Background/bubble_vektor_05.svg',
    'media/Background/bubble_vektor_06.svg',
    'media/Background/bubble_vektor_08.svg',
    'media/Background/bubble_vektor_09.svg',
    'media/Background/bubbles_vektor_02.svg'
];

// Zufällige Zahl zwischen min und max
function random(min, max) {
    return Math.random() * (max - min) + min;
}

// Generiere zufällige Animation Keyframes
function generateRandomAnimation(index) {
    const animationName = `flyAround-random-${index}`;
    const keyframes = `
        @keyframes ${animationName} {
            0% {
                transform: translate(0%, 0%) rotate(0deg) scale(1);
            }
            25% {
                transform: translate(${random(-80, 80)}%, ${random(-20, 30)}%) rotate(${random(-120, 120)}deg) scale(${random(0.9, 1.2)});
            }
            50% {
                transform: translate(${random(-100, 100)}%, ${random(-30, 40)}%) rotate(${random(-240, 240)}deg) scale(${random(0.85, 1.15)});
            }
            75% {
                transform: translate(${random(-60, 60)}%, ${random(-15, 25)}%) rotate(${random(-300, 300)}deg) scale(${random(0.9, 1.1)});
            }
            100% {
                transform: translate(0%, 0%) rotate(${random(-360, 360)}deg) scale(1);
            }
        }
    `;
    
    // Füge Keyframes zum Stylesheet hinzu
    const styleSheet = document.styleSheets[0];
    styleSheet.insertRule(keyframes, styleSheet.cssRules.length);
    
    return animationName;
}

// Erstelle eine zufällige Bubble
function createRandomBubble(index, offsetTop = 0) {
    const bubble = document.createElement('img');
    
    // Zufälliges Bubble-Bild
    const randomImage = bubbleImages[Math.floor(Math.random() * bubbleImages.length)];
    bubble.src = randomImage;
    bubble.alt = '';
    bubble.classList.add('bubble', `bubble-random-${index}`);
    
    // Zufällige Größe (Mobile)
    const size = random(280, 500);
    bubble.style.width = `${size}px`;
    bubble.style.height = `${size}px`;
    
    // Zufällige Position
    const top = random(0, 120) + offsetTop;
    const left = random(-10, 90);
    bubble.style.top = `${top}%`;
    bubble.style.left = `${left}%`;
    
    // Zufällige Opacity
    bubble.style.opacity = random(0.1, 0.25);
    
    // Zufällige Animation
    const animationName = generateRandomAnimation(index);
    const duration = random(40, 150);
    const delay = random(0, 10);
    bubble.style.animation = `${animationName} ${duration}s infinite ease-in-out ${delay}s`;
    
    return bubble;
}

// Funktion zum Erstellen unendlicher zufälliger Bubbles
function createInfiniteBubbles() {
    const bubbleContainer = document.querySelector('.bubble-container');
    if (!bubbleContainer) return;
    
    // Lösche existierende Bubbles
    bubbleContainer.innerHTML = '';
    
    const documentHeight = document.body.scrollHeight;
    const viewportHeight = window.innerHeight;
    
    // Anzahl Bubbles pro Viewport-Höhe
    const bubblesPerViewport = 7;
    const sections = Math.ceil(documentHeight / viewportHeight);
    const totalBubbles = bubblesPerViewport * sections;
    
    // Erstelle Bubbles
    for (let i = 0; i < totalBubbles; i++) {
        const section = Math.floor(i / bubblesPerViewport);
        const offsetTop = section * 100; // Offset in %
        const bubble = createRandomBubble(i, offsetTop);
        bubbleContainer.appendChild(bubble);
    }
    
    // Desktop: Größere Bubbles
    if (window.innerWidth >= 900) {
        document.querySelectorAll('.bubble').forEach(bubble => {
            const currentSize = parseFloat(bubble.style.width);
            const newSize = currentSize * 1.4; // 40% größer
            bubble.style.width = `${newSize}px`;
            bubble.style.height = `${newSize}px`;
        });
    }
}

// Initial erstellen
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', createInfiniteBubbles);
} else {
    createInfiniteBubbles();
}

// Bei Resize aktualisieren
let resizeTimeout;
window.addEventListener('resize', () => {
    clearTimeout(resizeTimeout);
    resizeTimeout = setTimeout(createInfiniteBubbles, 250);
});