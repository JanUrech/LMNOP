// Container holen
const main = document.querySelector("main");

// Für jedes Topic eine Section bauen
topics.forEach(topic => {
  const section = document.createElement("section");
  section.classList.add("topicSection");

  // Titel
  const h1 = document.createElement("h1");
  h1.classList.add("topicTitle");
  h1.textContent = topic.title;
  section.appendChild(h1);

  // Hero-Artikel
  const hero = document.createElement("article");
  hero.classList.add("heroArticle");


  // Erzeuge einen URL-freundlichen Titel
  const urlTitle = topic.title.toLowerCase().replace(/[^a-z0-9]+/g, "-").replace(/^-+|-+$/g, "");
  const overviewUrl = `../overviews/overview-${urlTitle}.html`;

  // Hero-Artikel klickbar machen
  hero.style.cursor = "pointer";
  hero.addEventListener("click", function(e) {
    // Nur weiterleiten, wenn nicht auf einen Link im Hero geklickt wurde
    if (e.target.tagName !== "A") {
      window.location.href = overviewUrl;
    }
  });

  // Optional: Link weiterhin anzeigen
  const overviewLink = document.createElement("a");
  overviewLink.href = overviewUrl;
  overviewLink.textContent = "Zur Übersicht";
  overviewLink.style.display = "block";
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
