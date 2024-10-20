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

const app = firebase.initializeApp(firebaseConfig);
const storage = firebase.storage();  

function seleccionar_imagen() {
    const input = document.createElement('input');
    input.type = 'file';
    input.accept = 'image/*';

    input.onchange = async (event) => {
        const file = event.target.files[0];
        if (file) {
            subirImagen(file); 
        }
    };

    input.click(); 
}

function subirImagen(file) {
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
                document.getElementById('url_img').value = downloadURL; 
                console.log('Imagen subida exitosamente. URL:', downloadURL);
            });
        }
    );
}
