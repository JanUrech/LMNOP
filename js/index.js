// Container holen
const main = document.querySelector("main");

// Für jedes Topic eine Section bauen
topics.forEach(topic => {
  const section = document.createElement("section");
  section.classList.add("topicSection");

  // Erzeuge einen URL-freundlichen Titel für ID und Links
  const urlTitle = topic.title.toLowerCase().replace(/[^a-z0-9]+/g, "-").replace(/^-+|-+$/g, "");
  section.id = urlTitle; // <--- Section bekommt ID

  // Titel
  const h1 = document.createElement("h1");
  h1.classList.add("topicTitle");
  h1.textContent = topic.title;
  section.appendChild(h1);

  // Hero-Artikel
  const hero = document.createElement("article");
  hero.classList.add("heroArticle");

  const overviewUrl = `../overviews/overview-${urlTitle}.html`;

  // Hero-Artikel klickbar machen
  hero.style.cursor = "pointer";
  hero.addEventListener("click", function(e) {
    if (e.target.tagName !== "A") {
      window.location.href = overviewUrl;
    }
  });

  // Optional: Link weiterhin anzeigen
  const overviewLink = document.createElement("a");
  overviewLink.href = overviewUrl;
  overviewLink.textContent = "Zur Übersicht";
  overviewLink.style.display = "none";
  overviewLink.style.marginBottom = "1rem";
  hero.appendChild(overviewLink);

  const p = document.createElement("p");
  p.textContent = topic.description;
  hero.appendChild(p);

  // Nur die ersten zwei Artikel anzeigen
  topic.articles.slice(0, 2).forEach((article, idx) => {
    const side = document.createElement("article");
    side.classList.add("sideArticle");
    const h2 = document.createElement("h2");
    h2.classList.add("sideArticleText");
    h2.textContent = article.title;
    side.appendChild(h2);

    // Zufällige Rotation zwischen -8deg und 8deg (immer, auch mobil)
    const randRot = (Math.random() * 16 - 8).toFixed(1);
    side.style.setProperty("--rot", randRot + "deg");

    // Für Desktop: Position und vertikale Verschiebung setzen
    side.style.setProperty("--posX", idx === 0 ? "0%" : "85%");
    const randY = Math.floor(Math.random() * 121) - 60;
    side.style.setProperty("--y", randY + "px");

    hero.appendChild(side);
  });

  section.appendChild(hero);
  main.appendChild(section);
});


// Menü bauen
const menu = document.querySelector(".menuIndex");

topics.forEach(topic => {
    const urlTitle = topic.title.toLowerCase().replace(/[^a-z0-9]+/g, "-").replace(/^-+|-+$/g, "");
    const li = document.createElement("li");
    li.classList.add("menuItem"); // Klasse hinzufügen
    const a = document.createElement("a");
    a.textContent = topic.title;
    a.href = `#${urlTitle}`; // Link zur Section-ID
    li.appendChild(a);
    menu.appendChild(li);
});

// Smooth Scroll für Menü-Links per Event Delegation
document.querySelector("menu.menuIndex").addEventListener("click", function(e) {
  const link = e.target.closest("a");
  if (!link) return;

  const href = link.getAttribute("href");
  // Prüfen, ob Link zu einer Section auf der Seite führt
  if (href && href.startsWith("#")) {
    const sectionId = href.slice(1);
    const section = document.getElementById(sectionId);
    if (section) {
      e.preventDefault();
      section.scrollIntoView({ behavior: "smooth" });
    }
  }
});