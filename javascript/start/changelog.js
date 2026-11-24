$(document).ready(() => {
    const $trigger = $('.gdStart__changelog__trigger');

    if ($trigger.data('show') !== true) {
        return;
    }

    $.ajax({
        url: '/api/changelog/read',
        method: 'POST',
        data: {
            release_id: $trigger.data('release-id')
        },
        dataType: 'json',

        error: function (xhr, status, error) {
            console.error('Fehler beim Speichern des Changelog-Status:', error);
        },

        complete: function () {
            $trigger.trigger('click');
        }
    });
});