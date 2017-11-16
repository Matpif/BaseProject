$(function () {

    $('.show-details').click(function () {
        var id = $(this).data('id');
        if ($('.detail-' + id).hasClass('open')) {
            $(this).removeClass('glyphicon-chevron-up').addClass('glyphicon-chevron-down');
            $('.detail-' + id).removeClass('open').addClass('close');
        } else {
            $(this).removeClass('glyphicon-chevron-down').addClass('glyphicon-chevron-up');
            $('.detail-' + id).removeClass('close').addClass('open');
        }
    });
});