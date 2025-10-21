// Container holen
const main = document.querySelector("main");

// Daten von transform.php holen und Sections bauen
async function loadTopics() {
  try {
    const res = await fetch('/PHP/transformIndex.php');
    if (!res.ok) throw new Error(`HTTP ${res.status}`);
    const topics = await res.json();

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

      const overviewUrl = `../${urlTitle}.html`;

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

    // Menü bauen (nach dem Laden der Topics)
    buildMenu(topics);

  } catch (err) {
    console.error('Fehler beim Laden der Topics:', err);
    main.innerHTML = '<p>Fehler beim Laden der Inhalte.</p>';
  }
}

// Menü bauen
function buildMenu(topics) {
  const menu = document.querySelector(".menuIndex");
  if (!menu) return;

  topics.forEach(topic => {
    const urlTitle = topic.title.toLowerCase().replace(/[^a-z0-9]+/g, "-").replace(/^-+|-+$/g, "");
    const li = document.createElement("li");
    li.classList.add("menuIndexItem");
    const a = document.createElement("a");
    a.textContent = topic.title;
    a.href = `#${urlTitle}`;
    li.appendChild(a);
    menu.appendChild(li);
  });

  // Smooth Scroll für Menü-Links per Event Delegation
  menu.addEventListener("click", function(e) {
    const link = e.target.closest("a");
    if (!link) return;

    const href = link.getAttribute("href");
    if (href && href.startsWith("#")) {
      const sectionId = href.slice(1);
      const section = document.getElementById(sectionId);
      if (section) {
        e.preventDefault();
        section.scrollIntoView({ behavior: "smooth" });
      }
    }
  });
}

// Initiales Laden der Topics
loadTopics();