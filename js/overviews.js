/**
 * overviews.js
 * Erstellt für jeden Beitrag mit Kategorie "overview" eine eigene HTML-Seite,
 * benannt nach der zweiten Kategorie (z.B. "musik.html").
 * 
 * Annahme: Die Kategorie-IDs und Namen werden über die WP-API separat geladen.
 */

async function fetchPosts() {
    const response = await fetch('fetch.php');
    return await response.json();
}

async function fetchCategories() {
    // Holt alle Kategorien (ID <-> Name)
    const response = await fetch('https://wp-lmnop.janicure.ch/wp-json/wp/v2/categories?per_page=100');
    const categories = await response.json();
    // Map: { id: name }
    return Object.fromEntries(categories.map(cat => [cat.id, cat.name]));
}

function hasOverviewCategory(post, categoriesMap) {
    // Prüft, ob eine Kategorie "overview" heißt
    return post.categories.some(catId => categoriesMap[catId]?.toLowerCase() === 'overview');
}

function getSecondCategoryName(post, categoriesMap) {
    // Gibt den Namen der zweiten Kategorie zurück (außer "overview")
    const filtered = post.categories
        .map(catId => categoriesMap[catId])
        .filter(name => name && name.toLowerCase() !== 'overview');
    return filtered[1] || filtered[0] || null;
}

function createHtmlContent(post) {
    return `
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>${post.title.rendered}</title>
</head>
<body>
    <h1>${post.title.rendered}</h1>
    ${post.content.rendered}
</body>
</html>
    `.trim();
}

async function main() {
    const posts = await fetchPosts();
    const categoriesMap = await fetchCategories();

    posts.forEach(post => {
        if (hasOverviewCategory(post, categoriesMap)) {
            const secondCatName = getSecondCategoryName(post, categoriesMap);
            if (secondCatName) {
                const filename = `${secondCatName.toLowerCase()}.html`;
                const htmlContent = createHtmlContent(post);

                // Im Browser kann man keine Dateien direkt speichern.
                // Zum Download anbieten:
                const blob = new Blob([htmlContent], { type: 'text/html' });
                const link = document.createElement('a');
                link.href = URL.createObjectURL(blob);
                link.download = filename;
                link.textContent = `Download ${filename}`;
                document.body.appendChild(link);
                document.body.appendChild(document.createElement('br'));
            }
        }
    });
}

main();