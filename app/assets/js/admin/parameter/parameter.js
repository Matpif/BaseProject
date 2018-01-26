jQuery(function($) {

    $('.menu-group li').on('click', function() {
        $('.menu-group li.active').removeClass('active');
        $(this).addClass('active');
        var group = $(this).data('group');
        if (!$('.sections').is('visible')) {
            $('.sections').show();
            $('.parameter-message').hide();
        }

        $('.section').hide();
        $('.group-' + group).show();
    });
});