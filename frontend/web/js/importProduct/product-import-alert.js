$(document).ready( function() {
    let swalTitle;
    let swalText;
    if($('#product-import-alert').hasClass('create')) {
        swalTitle = $('.create').attr('data-title');
        swalText = $('.create').attr('data-text');
        swal({
            title: swalTitle,
            text: swalText,
            type: 'success'
        });
    }
    else if($('#product-import-alert').hasClass('update')) {
        swalTitle = $('.update').attr('data-title');
        swalText = $('.update').attr('data-text');
        swal({
            title: swalTitle,
            text: swalText,
            type: 'success'
        });
   }
    else if($('#product-import-alert').hasClass('publishQueueSet')) {
        swalTitle = $('.publishQueueSet').attr('data-title');
        swalText = $('.publishQueueSet').attr('data-text');
        swal({
            title: swalTitle,
            text: swalText,
            type: 'success'
        });
    }
});