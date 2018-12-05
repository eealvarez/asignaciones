/*  ESTE FRAGMENTO DE CÓDIGO FUE EL PRIMERO QUE ESCRIBÍ EN ESTE ARCHIVO, SOLO FUE PRUEBA P/MOSTRAR SU FUNCIONAMIENTO
$(document).ready(function()
{
    alert('Hola Mundo!');
});
*/

$(document).ready(function()
{
    $('.btn-delete').click(function(e)
    {
        e.preventDefault(); //esto para evitar que la página se recargue cuando demos click en el botón eliminar de la vista index
        
        var row = $(this).parents('tr');    //obtenemos el padre en el elemento en el que estamos. Esta es la etiqueta tr del archivo index (user.id)
        
        var id = row.data('id');
        
        //alert(id);    esto solo es para prueba de que sí está obteniendo el id del elemento que queremos eliminar
        
        var form = $('#form-delete');   //obtemos el formulario que acabamos de crear y dentro de la variable form vamos a selecionnar el id del formulario
        
        var url = form. attr('action').replace(':USER_ID', id);     
        
        var data = form.serialize();    //para el correcto envío del formulario lo serializamos
        
        //alert(data);  ESTO SOLO PARA PRUEBA DE QUE SÍ ESTÁ SERIALIZANDO EL ID
        
                bootbox.confirm(message, function(res)
        {
            if(res == true)
            {
                //esta línea es para mostrar el progress-bar con jquery
                $('#delete-progress').removeClass('hidden');   //el # acà indica un id y efectivamente es el id delete-progress dentro del fragmento de código del progress-bar en el archivo index.html.twig
                
                $.post(url, data, function(result)
                {
                    
                    //acá mostramos nuevamente el progress-bar ocultando nuevamente la clase hidden, agregando la clase nuevamente
                    $('#delete-progress').addClass('hidden');
                    
                    if(result.removed ==1)
                    {
                        row.fadeOut();
                        $('#message').removeClass('hidden');
                        
                        $('#user-message').text(result.message);
                        
                        var totalUsers = $('#total').text();
                        
                        if($.isNumeric(totalUsers))
                        {
                            $('#total').text(totalUsers - 1);
                        }
                        else
                        {
                            $('#total').text(result.countUsers);
                        }
                        
                    }
                    else
                    {
                        $('#message-danger').removeClass('hidden');
                        
                        $('#user-message-danger').text(result.message);
                    }
                }).fail(function()                
                {
                    alert('ERROR');
                    row.show();
                });
            }
        });
        
    });
});