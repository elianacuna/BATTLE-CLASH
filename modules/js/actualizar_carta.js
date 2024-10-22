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
