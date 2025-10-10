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

// Füge den Header in jedes vorhandene <header>-Element ein
document.addEventListener("DOMContentLoaded", () => {
  const header = document.querySelector("header");
  if (header) {
    header.innerHTML = headerHTML;
  }
});