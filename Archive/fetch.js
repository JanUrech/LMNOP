// Lädt das JSON von /PHP/fetch.php und schreibt es in die Konsole
document.addEventListener("DOMContentLoaded", async () => {
  try {
    const res = await fetch('/PHP/fetch.php');
    if (!res.ok) throw new Error(`HTTP ${res.status}`);
    const data = await res.json();
    console.log('fetch.php JSON:', data);
    console.log('Anzahl Einträge:', Array.isArray(data) ? data.length : 'nicht-array');
  } catch (err) {
    console.error('Fehler beim Laden von /PHP/fetch.php:', err);
  }
});