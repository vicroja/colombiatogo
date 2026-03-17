$(document).ready(function() {
    var tablaConversationsId = '#tablaConversations';

    if ($(tablaConversationsId).length) {

        $('.datepicker-filter').datepicker({
            format: "yyyy-mm-dd",
            autoclose: true,
            todayHighlight: true,
            language: 'es'
        });

        var tablaConversations = $(tablaConversationsId).DataTable({
            "ajax": {
                "url": "whatsapp/getConversationsListAjax",
                "type": "POST",
                "data": function(d) {
                    d.filtro_estado_conv = $('#filtro_estado_conv').val();
                    d.filtro_fecha_desde_conv = $('#filtro_fecha_desde_conv').val();
                    d.filtro_fecha_hasta_conv = $('#filtro_fecha_hasta_conv').val();
                }
            },
            "processing": true,
            "serverSide": true,
            "searchDelay": 700,
            "responsive": true,
            "autoWidth": false,
            "dom": "<'row'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6'f>>" +
                "<'row'<'col-sm-12'tr>>" +
                "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
            "language": {
                "url": "//cdn.datatables.net/plug-ins/1.10.25/i18n/Spanish.json"
            },
            "order": [[4, "desc"]], // Ordenar por fecha del último mensaje descendente
            "columns": [
                { "data": 0 }, // Índice 0: Hilo
                { "data": 1 }, // Índice 1: Teléfono
                { "data": 2 }, // Índice 2: Propietario
                { "data": 3 }, // Índice 3: Mascota(s)
                { "data": 4, "className": "text-center" }, // NUEVO Índice 4: Origen (SaaS o En Casa)
                { "data": 5 }, // Índice 5: Último Mensaje (se desplazó)
                { "data": 6, "className": "text-center" }, // Índice 6: Estado (se desplazó)
                { "data": 7, "orderable": false, "searchable": false, "className": "text-center" } // Índice 7: Acciones (se desplazó)
            ]
        });

        $('#btnAplicarFiltrosConv').on('click', function() {
            tablaConversations.ajax.reload();
        });

        $('#btnLimpiarFiltrosConv').on('click', function() {
            $('#filtro_estado_conv').val('').trigger('change');
            $('#filtro_fecha_desde_conv').val('');
            $('#filtro_fecha_hasta_conv').val('');
            tablaConversations.search('').draw(); // Limpia la búsqueda global y redibuja
            tablaConversations.ajax.reload();
        });
    }
});