// Inicializar Firebase
const firebaseConfig = {
    apiKey: "AIzaSyAaNOIIh0793zmZjACLDC-kauSsGNhqSvc",
    authDomain: "battleclash-85fc5.firebaseapp.com",
    projectId: "battleclash-85fc5",
    storageBucket: "battleclash-85fc5.appspot.com",
    messagingSenderId: "162502778140",
    appId: "1:162502778140:web:5b80d0a05d475d9c8a2ed9",
    measurementId: "G-L6D1S53GLK"
  };


// Variables globales
const app = firebase.initializeApp(firebaseConfig);
const storage = firebase.storage();  
let input, file, previewImage;
const urlAPI = "http://localhost/BATTLE-CLASH/modules/apis";


function getParameterByName(name, url = window.location.href) {
    name = name.replace(/[\[\]]/g, '\\$&');
    const regex = new RegExp('[?&]' + name + '(=([^&#]*)|&|#|$)'),
        results = regex.exec(url);
    if (!results) return null;
    if (!results[2]) return '';
    return decodeURIComponent(results[2].replace(/\+/g, ' '));
}

$(document).ready(function() {
    const cartaId = getParameterByName('id'); 

    if (cartaId) {

        cargarDatosCarta(cartaId);

    } else {
        alert('No se encontró el ID de la carta en la URL');
    }
});

function cargarDatosCarta(cartaId) {
    $.ajax({
        url: '../../../modules/apis/carta.php?action=obtenerCartaPorId',  
        method: 'GET',
        data: { id: cartaId },  

        success: function(response) {
            console.log(response);  

            if (response.status === 'success') {

                $('#tipoCarta').val(response.data.tipo_carta);
                $('#nombreCarta').val(response.data.nombre_carta);
                $('#poderAtaque').val(response.data.poder_ataque);
                $('#poderDefensa').val(response.data.poder_defensa);

                if (response.data.foto) {
                    $('#previewImage').attr('src', response.data.foto);
                } else {
                    $('#previewImage').attr('src', '../../assets/img/img_select.png');  // Imagen por defecto
                }
            } else {
                alert('Error al cargar los datos de la carta: ' + response.message);
            }
        },
        error: function(xhr, status, error) {
            console.error('Error al obtener los datos de la carta:', xhr.responseText);
            alert('Ocurrió un error al intentar cargar los datos de la carta.');
        }
    });
}

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

    // Si no se ha seleccionado una nueva imagen
    if (!file) {
        if (confirm('¿Quieres actualizar solo los datos sin ninguna imagen?')) {
            actualizarInfoCarta(nombreCarta, tipoCarta, poderAtaque, poderDefensa);
        }
    } else {
        const storageRef = storage.ref('carta/' + file.name);
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

                    actualizarInfoCarta(downloadURL, nombreCarta, tipoCarta, poderAtaque, poderDefensa);
                }).catch((error) => {
                    console.error('Error al obtener el URL de descarga: ', error);
                    alert('Error al obtener el URL de descarga.');
                });
            }
        );
    }
}


function actualizarInfoCarta(downloadURL, nombreCarta, tipoCarta, poderAtaque, poderDefensa) {
    // Obtener el ID de la carta desde la URL
    const cartaId = getParameterByName('id');
    
    const data = {
        id: cartaId,  
        foto: downloadURL,  
        nombre: nombreCarta,
        tipo: tipoCarta,
        poderAtaque: poderAtaque,
        poderDefensa: poderDefensa
    };

    fetch(urlAPI + '/carta.php?action=update', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(result => {
        if (result.status === 'success') {

            window.location.href = '../../../templates/admin/cartas/cartas.html'; 

            alert('Carta actualizada correctamente');

        } else {
            console.error('Error al actualizar la carta:', result.message);
            alert('Error al actualizar la carta: ' + result.message);
        }
    })
    .catch(error => {
        console.error('Error en la solicitud:', error);
        alert('Ocurrió un error al intentar actualizar la carta.');
    });
}


function actualizarInfoCarta(nombreCarta, tipoCarta, poderAtaque, poderDefensa) {

    const cartaId = getParameterByName('id');
    
    const data = {
        id: cartaId,  
        nombre: nombreCarta,
        tipo: tipoCarta,
        poderAtaque: poderAtaque,
        poderDefensa: poderDefensa
    };

    fetch(urlAPI + '/carta.php?action=updateData', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(result => {
        if (result.status === 'success') {

            window.location.href = '../../../templates/admin/cartas/cartas.html'; 

            alert('Carta actualizada correctamente');

        } else {
            console.error('Error al actualizar la carta:', result.message);
            alert('Error al actualizar la carta: ' + result.message);
        }
    })
    .catch(error => {
        console.error('Error en la solicitud:', error);
        alert('Ocurrió un error al intentar actualizar la carta.');
    });
}