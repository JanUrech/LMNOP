// Header-HTML als Template-String
const headerHTML = `
<div class="logoBar">
  <img src="media/header/ABCDE.png" alt="" class="logoBarText">
  <a href="index.html"><img src="media/header/logo_rosa_grau.png" alt="Startseite" id="headerLogo"></a>
  <img src="media/header/QRSTU.png" alt="" class="logoBarText">
</div>
  <menu id="headerMenu">
    <li><a href="aboutus.html" class="headerMenuItem">About us</a></li>
    <li><a href="impressum.html" class="headerMenuItem">Impressum</a></li>
  </menu>
`;

const footerHTML = `
<div>
  <h6>Impressum</h6>
  <p>Herausgeber: LMNOP Verlag</p>
  <p>Adresse: Musterstrasse 1, 7000 Chur</p>
  <p>Kontakt:
    <a href="mailto:  janic.urech@stud.fhgr.ch"> Mail</a> |
    <a href="tel:+41791234567"> Tel</a>
  </p>
</div>

<img src="media/header/logo_rosa_grau.png" alt="" id="footerLogo">
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
});

