//Esto fue la primera línea solo para comprobar que se había creado este misom archivo con enlace simólico dentro del directorio web>bundles>infunisauser>js
//alert('Hola!');

//JavaScript con JQuery y AJAX. El siguiente código es ejemplo de JQuery y es similar, prácticamente el mismo al que teníamos para eliminar a nuestros usuarios cuando teníamos el módulo de usuarios y lo hemos elimnado con AJAX

$(document).ready(function () {
    $('.btn-process').click(function (e) {
        e.preventDefault();     //esto va a evitar que el formulario haga alguna acción dentro de nuestra página web

        var row = $(this).parents('tr');

        var id = row.data('id');

        var form = $('#form-update');

        var url = form.attr('action').replace(':TASK_ID', id);

        var data = form.serialize();

        $('#button-' + id).addClass('disabled');

        $.post(url, data, function (result) {
            $('#button-' + id).removeClass('disabled');
            if (result.processed == 1)
            {
                $('#message-warning').addClass("hidden");

                $('#message').removeClass("hidden");

                $('#glyphicon-' + id).removeClass('glyphicon-time text-danger').addClass('glyphicon-ok text-success');
                $('#glyphicon-' + id).prop('title', 'Finish');
                
                //así estaba inicialmente esta línea en el video
                //$('#user-message').html('The task has been finish.');
                //$('#user-message').html('The task has been processed.');
                //así estaba esta línea en el repositorio de Edson el youtuber
                $('#user-message').html(result.success);
            } else
            {
                $('#message').addClass("hidden");
                $('#message-warning').removeClass("hidden");
                //así estaba inicialmente esta línea en el video
                //$('#user-message-warning').html('The task was already finished.');
                //$('#user-message-warning').html('The task has already been processed.');
                //así estaba esta línea en el repositorio de Edson el youtuber
                $('#user-message-warning').html(result.warning);
            }
        }).fail(function () {
            $('#button-' + id).removeClass('disabled');
            alert('The task was not finished.')
        });
    });
});