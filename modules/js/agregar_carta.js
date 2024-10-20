//Config firebase
const firebaseConfig = {
    apiKey: "AIzaSyCoxj0pmiIVW48XP89jHOvCJ-a4hn8SGQo",
    authDomain: "battleclash-222a4.firebaseapp.com",
    projectId: "battleclash-222a4",
    storageBucket: "battleclash-222a4.appspot.com",
    messagingSenderId: "1067459995622",
    appId: "1:1067459995622:web:0d36dc2132f9583290d827",
    measurementId: "G-TM7YDDNWJ1"
  };

  // Inicializa Firebase
  const app = firebase.initializeApp(firebaseConfig);
  const storage = firebase.storage();  // Initialize Firebase Storage
  

  function irAlInicio(){
    console.log("Usuario ha iniciado sesión:");
  }
  
  function loginFirebase() {
    var emailtv = "eacunap@miumg.edu.gt";
    var passwordtv = "123456789";
  
    if (!validateEmail(emailtv)) {
        alert('Por favor, ingresa un correo electrónico válido.');
        return;
    } else if (passwordtv.trim() === '') {
        alert('Por favor, ingresa una contraseña.');
        return;
    } else if(passwordtv.length < 7){
        alert('La contraseña por lo menos debe de tener 7 caracteres');
        return;
    } else {
        verifyLoginFirebase(emailtv, passwordtv);
    }
  }
  
  function verifyLoginFirebase(emailtv, passwordtv) {
    // Inicia sesión con Firebase Auth
    firebase.auth().signInWithEmailAndPassword(emailtv, passwordtv)
    .then((userCredential) => {
        var user = userCredential.user;
        seleccionar_pdf(); // Iniciar selección de archivo
    })
    .catch((error) => {
        var errorCode = error.code;
        var errorMessage = error.message;
        console.error("Error al iniciar sesión:", errorCode, errorMessage);
    });
  }
  

window.onload = function() {

    fetch('../../../modules/apis/functions.php?action=checkLogin')
        .then(response => response.json())
        .then(data => {
            if (data.status !== 'logged_in') {
                window.location.href = '../../../login.html';
            }
        })
        .catch(error => {
            console.error('Error verificando la sesión:', error);
            window.location.href = '../../../login.html';
        });

    document.getElementById('cardImage').addEventListener('change', previewImage);
};

function previewImage(event) {
    const reader = new FileReader();
    reader.onload = function(){
        const output = document.getElementById('previewImage');
        output.src = reader.result;
    };
    reader.readAsDataURL(event.target.files[0]);
}


function validateEmail(emailtv) {
    var emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailPattern.test(emailtv);
  }