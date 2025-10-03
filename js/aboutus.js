const teamMemberList = document.querySelector('.teamMemberList');
teamMemberList.innerHTML = ""; // Vorherigen Inhalt entfernen

teammembers.forEach(member => {
    // Section für das Teammitglied
    const section = document.createElement('section');
    section.classList.add('teamMember');

    // Container für die Fotos
    const photoDiv = document.createElement('div');
    photoDiv.classList.add('memberPhotos');

    // Standardfoto
    const imgStandard = document.createElement('img');
    imgStandard.src = member.fotoStandard;
    imgStandard.alt = `Foto von ${member.Name}`;
    imgStandard.classList.add('memberPhotoStandard');

    // Hoverfoto
    const imgHover = document.createElement('img');
    imgHover.src = member.fotoHover;
    imgHover.alt = `Fisheye Foto von ${member.Name}`;
    imgHover.classList.add('memberPhotoFisheye');

    // Post-it mit Name und Rolle
    const postitDiv = document.createElement('div');
    postitDiv.classList.add('memberPostit');

// Zufällige Rotation zwischen -8deg und +8deg
const randRot = (Math.random() * 16 - 8).toFixed(1);
// Zufällige horizontale Verschiebung zwischen -50% und +50%
const randX = (Math.random() * 150 - 25).toFixed(1);
postitDiv.style.transform = `rotate(${randRot}deg) translateX(${randX}%)`;

    const h2 = document.createElement('h2');
    h2.textContent = member.Name;

    const p = document.createElement('p');
    p.textContent = `Role: ${member.role}`;

    // Zusammenbauen
    postitDiv.appendChild(h2);
    postitDiv.appendChild(p);

    photoDiv.appendChild(imgStandard);
    photoDiv.appendChild(imgHover);
    photoDiv.appendChild(postitDiv);

    section.appendChild(photoDiv);
    teamMemberList.appendChild(section);

    // Hover-Effekt auch auf Touch-Geräten aktivieren (toggle)
    photoDiv.addEventListener('touchstart', function (e) {
        e.preventDefault(); // Verhindert Scrollen beim Tippen
        photoDiv.classList.toggle('hover');
    }, {passive: false});
});

