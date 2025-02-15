$(document).ready(function () {
    $('.login-input').change(function () {
        let shop = $(this).val();
        shop = shop.replace('https://', '');
        shop = shop.replace('.myshopify.com', '');
        $(this).val(shop);
    });
/*    $('body').on('submit', '#login-form', function () {
        let shop = $('.login-input').val();

    })*/
});