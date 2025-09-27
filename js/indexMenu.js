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