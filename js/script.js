// Header-HTML als Template-String
const headerHTML = `
<div class="logoBar">
  <img src="media/header/ABCDE.png" alt="" class="logoBarText">
  <img src="media/header/logo_rosa_grau.png" alt="" id="headerLogo">
  <img src="media/header/QRSTU.png" alt="" class="logoBarText">
</div>
`;

// Füge den Header in jedes vorhandene <header>-Element ein
document.addEventListener("DOMContentLoaded", () => {
  const header = document.querySelector("header");
  if (header) {
    header.innerHTML = headerHTML;
  }
});