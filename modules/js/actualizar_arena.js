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
    const arenaId = getParameterByName('id'); 

    if (arenaId) {
        cargarDatosArena(arenaId);
    } else {
        alert('No se encontró el ID de la arena en la URL');
    }
});

function cargarDatosArena(arenaId) {
    $.ajax({
        url: '../../../modules/apis/arena.php?action=obtenerArenaPorId',  // Usa el nuevo action
        method: 'GET',
        data: { id: arenaId },  

        success: function(response) {
            console.log(response);

            if (response.status === 'success') {

                const arena = response.data;
    
                if (arena) {
                    $('#tipoArena').val(arena.tipo_arena);
                    $('#nombreArena').val(arena.nombre_arena);
                    $('#rankingMin').val(arena.ranking_min);
                    $('#rankingMax').val(arena.ranking_max);

                    if (arena.fondo) { 
                        $('#previewImage').attr('src', arena.fondo);
                    } else {
                        $('#previewImage').attr('src', '../../assets/img/img_select.png');  // Imagen por defecto
                    }
                } else {
                    alert('No se encontró la información de la arena.');
                }
            } else {
                alert('Error al cargar los datos de la arena: ' + response.message);
            }
        },
        error: function(xhr, status, error) {
            console.error('Error al obtener los datos de la arena:', xhr.responseText);
            alert('Ocurrió un error al intentar cargar los datos de la arena.');
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

function guardarArena(event) {
    event.preventDefault();

    const tipoArena = document.getElementById('tipoArena').value.trim();
    const nombreArena = document.getElementById('nombreArena').value.trim();
    const rankingMin = document.getElementById('rankingMin').value.trim();
    const rankingMax = document.getElementById('rankingMax').value.trim();

    console.log("Variables capturadas:", { tipoArena, nombreArena, rankingMin, rankingMax });

    if (!tipoArena || !nombreArena || !rankingMin || !rankingMax) {
        alert('Por favor, complete todos los campos.');
        return;
    }

    if (!file) {
        if (confirm('¿Quieres actualizar solo los datos sin ninguna imagen?')) {
            actualizarInfoArenaSoloDatos(nombreArena, tipoArena, rankingMin, rankingMax);
        }
    } else {
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
                    actualizarInfoArena(downloadURL, nombreArena, tipoArena, rankingMin, rankingMax);
                }).catch((error) => {
                    console.error('Error al obtener el URL de descarga: ', error);
                    alert('Error al obtener el URL de descarga.');
                });
            }
        );
    }
}

function actualizarInfoArena(downloadURL, nombreArena, tipoArena, rankingMin, rankingMax) {
    const arenaId = getParameterByName('id');

    const data = {
        id: arenaId,
        foto: downloadURL,
        nombre: nombreArena,
        tipo: tipoArena,
        rankingMin: rankingMin,
        rankingMax: rankingMax
    };

    fetch(urlAPI + '/arena.php?action=update', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(result => {
        if (result.status === 'success') {
            window.location.href = '../../../templates/admin/arenas/arenas.html';
            alert('Arena actualizada correctamente');
        } else {
            console.error('Error al actualizar la arena:', result.message);
            alert('Error al actualizar la arena: ' + result.message);
        }
    })
    .catch(error => {
        console.error('Error en la solicitud:', error);
        alert('Ocurrió un error al intentar actualizar la arena.');
    });
}

function actualizarInfoArenaSoloDatos(nombreArena, tipoArena, rankingMin, rankingMax) {
    const arenaId = getParameterByName('id');

    const data = {
        id: arenaId,
        nombre: nombreArena,
        tipo: tipoArena,
        rankingMin: rankingMin,
        rankingMax: rankingMax
    };

    fetch(urlAPI + '/arena.php?action=updateData', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(result => {
        if (result.status === 'success') {
            window.location.href = '../../../templates/admin/arenas/arenas.html';
            alert('Arena actualizada correctamente');
        } else {
            console.error('Error al actualizar la arena:', result.message);
            alert('Error al actualizar la arena: ' + result.message);
        }
    })
    .catch(error => {
        console.error('Error en la solicitud:', error);
        alert('Ocurrió un error al intentar actualizar la arena.');
    });
}
