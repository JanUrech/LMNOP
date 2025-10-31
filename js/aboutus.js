const teamMemberList = document.querySelector('.teamMemberList');

// Lade Autoren-Daten aus JSON
fetch('Data/authorsList.json')
  .then(response => response.json())
  .then(teammembers => {
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
        const randX = (Math.random() * 180 - 30).toFixed(1);
        postitDiv.style.transform = `rotate(${randRot}deg) translateX(${randX}%)`;

        const pName = document.createElement('p');
        pName.classList.add('memberName');
        pName.textContent = member.Name;

        const pRole = document.createElement('p');
        pRole.classList.add('memberRole');
        pRole.textContent = `${member.role}`;

        // Zusammenbauen
        postitDiv.appendChild(pName);
        postitDiv.appendChild(pRole);

        photoDiv.appendChild(imgStandard);
        photoDiv.appendChild(imgHover);
        photoDiv.appendChild(postitDiv);

        section.appendChild(photoDiv);
        teamMemberList.appendChild(section);

        // Touch: Toggle hover on tap, close other toggles
        photoDiv.addEventListener('touchstart', function (e) {
            e.preventDefault(); // verhindert direktes Scrollen beim Tippen
            // entferne 'hover' von allen anderen
            document.querySelectorAll('.memberPhotos.hover').forEach(el => {
                if (el !== photoDiv) el.classList.remove('hover');
            });
            // toggle für das aktuelle
            photoDiv.classList.toggle('hover');
        }, { passive: false });
    });

    // global: Tippen außerhalb schliesst offene Hovers
    document.addEventListener('touchstart', function (e) {
        if (!e.target.closest('.memberPhotos')) {
            document.querySelectorAll('.memberPhotos.hover').forEach(el => el.classList.remove('hover'));
        }
    }, { passive: true });
  })
  .catch(error => {
    console.error('Fehler beim Laden der Autoren-Daten:', error);
    teamMemberList.innerHTML = '<p>Fehler beim Laden der Team-Mitglieder.</p>';
  });

