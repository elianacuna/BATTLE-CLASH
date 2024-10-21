// Inicializar Firebase
const firebaseConfig = {
    apiKey: "AIzaSyCoxj0pmiIVW48XP89jHOvCJ-a4hn8SGQo",
    authDomain: "battleclash-222a4.firebaseapp.com",
    projectId: "battleclash-222a4",
    storageBucket: "battleclash-222a4.appspot.com",
    messagingSenderId: "1067459995622",
    appId: "1:1067459995622:web:0d36dc2132f9583290d827",
    measurementId: "G-TM7YDDNWJ1"
};

// Variables globales
const app = firebase.initializeApp(firebaseConfig);
const storage = firebase.storage();  
let input, file, previewImage;
const urlAPI = "http://localhost/BATTLE-CLASH/modules/apis";

function seleccionar_imagen() {
    input = document.createElement('input');
    input.type = 'file';
    input.accept = 'image/*';

    input.onchange = async (event) => {
        file = event.target.files[0];
        if (file) {
            previewImage = document.getElementById('previewImage');
            previewImage.src = URL.createObjectURL(file); 
        }
    };

    input.click(); 
}

function guardarCarta(event) {
    event.preventDefault();

    // Variables
    const tipoCarta = document.getElementById('tipoCarta').value.trim();
    const nombreCarta = document.getElementById('nombreCarta').value.trim();
    const poderAtaque = document.getElementById('poderAtaque').value.trim();
    const poderDefensa = document.getElementById('poderDefensa').value.trim();

    console.log("Variables capturadas:", { tipoCarta, nombreCarta, poderAtaque, poderDefensa });

    if (!tipoCarta || !nombreCarta || !poderAtaque || !poderDefensa) {
        alert('Por favor, complete todos los campos.');
        return;
    }

    if (!file) {
        alert('Por favor, selecciona una imagen.');
        return;
    }

    const storageRef = storage.ref('game/' + file.name);
    const task = storageRef.put(file);

    task.on(
        'state_changed',
        (snapshot) => {
            const progress = (snapshot.bytesTransferred / snapshot.totalBytes) * 100;
            const progressBar = document.getElementById('uploadProgress');
            progressBar.style.width = progress + '%';
            progressBar.setAttribute('aria-valuenow', progress);
            progressBar.textContent = Math.round(progress) + '%';

            if (progress === 100) {
                progressBar.classList.add('bg-success'); 
            }
        },
        (error) => {
            console.error('Error subiendo la imagen: ', error);
            alert('Error subiendo la imagen.');
        },
        () => {
            task.snapshot.ref.getDownloadURL().then((downloadURL) => {

                console.log( "enviando datos:" + downloadURL, nombreCarta, tipoCarta, poderAtaque, poderDefensa)

                subirInfoCarta(downloadURL, nombreCarta, tipoCarta, poderAtaque, poderDefensa);

            }).catch((error) => {
                console.error('Error al obtener el URL de descarga: ', error);
                alert('Error al obtener el URL de descarga.');
            });
        }
    );
}

function subirInfoCarta(downloadURL, nombreCarta, tipoCarta, poderAtaque, poderDefensa) {
    
    console.log("url:", downloadURL);

    const data = {
        link: downloadURL,
        nombre: nombreCarta,
        tipo: tipoCarta,
        poderAtaque: poderAtaque,
        poderDefensa: poderDefensa
    };

    //console.log("Datos enviados:", data);  // Para ver los datos enviados

    fetch(urlAPI + '/carta.php?action=insert', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(data)
    })
    .then(response => {
        console.log("Respuesta completa del servidor:", response);  // Para ver la respuesta completa
        return response.json();
    })
    .then(result => {
        if (result.status === 'success') {
            console.log('Carta registrada correctamente:', result.message);
        } else {
            console.error('Error al registrar la carta:', result.message);
        }
    })
    .catch(error => {
        console.error('Error en la solicitud:', error);
    });
}


