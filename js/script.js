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

  const p = document.createElement("p");
  p.textContent = topic.description;
  hero.appendChild(p);


    // Nebenartikel dynamisch erzeugen
    // Flexbox-Container für Bottom-Artikel
    let bottomBox = null;

    topic.articles.forEach((article, idx) => {
      const side = document.createElement("article");
      side.classList.add("sideArticle");
      const h2 = document.createElement("h2");
      h2.textContent = article.title;
      side.appendChild(h2);

      if (idx === 0) {
        side.style.setProperty("--posX", "0%");
    // Zufällige vertikale Position zwischen -60px und 60px
    const randY = Math.floor(Math.random() * 121) - 60;
        side.style.setProperty("--y", randY + "px");
        // Zufällige Rotation zwischen -8deg und 8deg
        const randRot = (Math.random() * 16 - 8).toFixed(1);
        side.style.setProperty("--rot", randRot + "deg");
        hero.appendChild(side);
      } else if (idx === 1) {
        side.style.setProperty("--posX", "100%");
    // Zufällige vertikale Position zwischen -60px und 60px
    const randY = Math.floor(Math.random() * 121) - 60;
        side.style.setProperty("--y", randY + "px");
        // Zufällige Rotation zwischen -8deg und 8deg
        const randRot = (Math.random() * 16 - 8).toFixed(1);
        side.style.setProperty("--rot", randRot + "deg");
        hero.appendChild(side);
      } else {
        // Bottom-Artikel in Flexbox-Container
        side.classList.add("sideArticleBottom");
        if (!bottomBox) {
          bottomBox = document.createElement("div");
          bottomBox.classList.add("sideArticlesBottomBox");
          hero.appendChild(bottomBox);
        }
        bottomBox.appendChild(side);
      }
    });

  section.appendChild(hero);
  main.appendChild(section);
});
