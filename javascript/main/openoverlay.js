$(document).ready(() => {
    $('.__openoverlay__').on('click', (e) => {
        const $element = $(e.currentTarget);

        const title = $element.data('title');
        const content = $element.data('content');

        $.ajax({
            url: '/api/overlay/open',
            method: 'GET',
            data: {
                title: title,
                content: content
            },
            dataType: 'json',

            success: function (response) {
                show_overlay(response.overlay);
            },

            error: function (xhr, status, error) {
                console.error('Fehler beim Laden des Overlays:', error);
            }
        });
    });

    function show_overlay(overlay) {
        const $wrapper = $('.gdMain__overlaywrapper');

        $wrapper.html(overlay).addClass('gdMain__overlaywrapper--is-active');
    }

    $(document).on('click', '.gdMain__overlaywrapper__close', () => {
        $('.gdMain__overlaywrapper').html('').removeClass('gdMain__overlaywrapper--is-active');
    });

    $(document).on('click', '.gdMain__overlaywrapper', (e) => {
        if (e.target === e.currentTarget) {
            $('.gdMain__overlaywrapper').html('').removeClass('gdMain__overlaywrapper--is-active');
        }
    });
});