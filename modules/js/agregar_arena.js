// Inicializar Firebase
var firebaseConfig = {
    apiKey: "AIzaSyAaNOIIh0793zmZjACLDC-kauSsGNhqSvc",
    authDomain: "battleclash-85fc5.firebaseapp.com",
    projectId: "battleclash-85fc5",
    storageBucket: "battleclash-85fc5.appspot.com",
    messagingSenderId: "162502778140",
    appId: "1:162502778140:web:5b80d0a05d475d9c8a2ed9",
    measurementId: "G-L6D1S53GLK"
};

// Inicializar Firebase
const app = firebase.initializeApp(firebaseConfig);
const storage = firebase.storage();

console.log('Firebase inicializado correctamente:', app);

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
            console.log('Imagen seleccionada:', file);
        }
    };

    input.click(); 
}

function guardarArena(event) {
    event.preventDefault();

    // Variables de los input
    const nombreArena = document.getElementById('nombreArena').value.trim();
    const tipoArena = document.getElementById('tipoArena').value.trim();
    const rankingMin = document.getElementById('rankingMin').value.trim();
    const rankingMax = document.getElementById('rankingMax').value.trim();

    console.log("Variables capturadas:", { nombreArena, tipoArena, rankingMin, rankingMax });

    if (!nombreArena || !tipoArena || !rankingMin || !rankingMax) {
        alert('Por favor, complete todos los campos.');
        return;
    }

    if (!file) {
        alert('Por favor, selecciona una imagen.');
        return;
    }

    const storageRef = storage.ref('arena/' + file.name);
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
                console.log("Enviando datos:", downloadURL, nombreArena, tipoArena, rankingMin, rankingMax);
                subirInfoArena(downloadURL, nombreArena, tipoArena, rankingMin, rankingMax);
            }).catch((error) => {
                console.error('Error al obtener el URL de descarga: ', error);
                alert('Error al obtener el URL de descarga.');
            });
        }
    );
}

function subirInfoArena(downloadURL, nombreArena, tipoArena, rankingMin, rankingMax) {
    const data = {
        link: downloadURL,
        nombre: nombreArena,
        tipo: tipoArena,
        rankingMin: rankingMin,
        rankingMax: rankingMax
    };

    console.log("Datos enviados a la API:", data);

    fetch(urlAPI + '/arena.php?action=insert', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(data)
    })
    .then(response => {
        if (!response.ok) {
            throw new Error("Error en la solicitud de red: " + response.statusText);
        }
        return response.json();
    })
    .then(result => {
        console.log('Respuesta completa de la API:', result);
        if (result.status === 'success') {
            console.log('Arena registrada correctamente:', result.message);
            window.location.href = '../../../templates/admin/arena/arena.html'; 
        } else {
            console.error('Error al registrar la arena:', result.message || 'Respuesta inesperada');
            alert(result.message || 'Hubo un error al registrar la arena.');
        }
    })
    .catch(error => {
        console.error('Error en la solicitud:', error);
        alert('Error en la solicitud a la API. Revisa la consola para más detalles.');
    });
}
