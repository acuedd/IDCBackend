let existTable = document.getElementById("table_device");
if(existTable){
    let table = $(`#table_device`).DataTable({
        responsive: true,
        "language": {
            "lengthMenu": "Mostrando _MENU_ registros por página",
            "zeroRecords": "No encontrado",
            "info": "Mostrando página _PAGE_ de _PAGES_",
            "infoEmpty": "No hay registros disponibles",
            "infoFiltered": "(Filtrado de _MAX_ registros)",
            "search": " ",
            "searchPlaceholder": "Buscar",
            "paginate": {
                "first": "Primero",
                "last": "Último",
                "next": "Siguiente",
                "previous": "Anterior"
            }
        }
    });
    new $.fn.dataTable.FixedHeader(table);
}
/*¯\_(?)_/¯*/