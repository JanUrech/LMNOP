// Header-HTML als Template-String
const headerHTML = `
<div class="logoBar">
  <div class="logoBarText"><p>A</p><p>B</p><p>C</p><p>D</p><p>E</p><p>F</p><p>G</p><p>H</p><p>I</p><p>J</p><p>K</p></div>
  <a href="index.php"><img src="media/header/logo_rosa_grau.png" alt="Startseite" id="headerLogo"></a>
  <div class="logoBarText"><p>Q</p><p>R</p><p>S</p><p>T</p><p>U</p><p>V</p><p>W</p><p>X</p><p>Y</p><p>Z</p></div>
</div>
  <menu id="headerMenu">
   <li><a href="index.php" class="headerMenuItem">Home</a></li>
    <li><a href="aboutus.html" class="headerMenuItem">About us</a></li>
    <li><a href="impressum.html" class="headerMenuItem">Impressum</a></li>
  </menu>
`;

const footerHTML = `
    <ul id=footerMenu>
      <li> <a href="aboutus.html" class="footerMenuItem">Über Uns</a></li>
      <li><a href="datenschutz.html" class="footerMenuItem">Datenschutzerklärung </a></li>
      <li><a href="impressum.html" class="footerMenuItem">Impressum </a></li>
      <li><a href="https://www.instagram.com/lmnop_mag/" class="footerMenuItem">Instagram </a></li>

    </ul>

    <a href="index.php"><img src="media/header/logo_rosa_grau.png" alt="" id="footerLogo"></a>
`;

// Füge den Header und Footer in jedes vorhandene <header> / <footer>-Element ein
document.addEventListener("DOMContentLoaded", () => {
  const header = document.querySelector("header");
  if (header) {
    header.innerHTML = headerHTML;
  }

  const footer = document.querySelector("footer");
  if (footer) {
    footer.innerHTML = footerHTML;
  }

  // Smooth Scroll für Menü-Links
  const menuIndex = document.querySelector('.menuIndex');
  if (menuIndex) {
    menuIndex.addEventListener('click', function (e) {
      const link = e.target.closest('a');
      if (!link) return;
      const href = link.getAttribute('href');
      if (href?.startsWith('#')) {
        e.preventDefault();
        document.getElementById(href.slice(1))?.scrollIntoView({ behavior: 'smooth' });
      }
    });
  }
});

