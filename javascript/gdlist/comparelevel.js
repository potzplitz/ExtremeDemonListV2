$(document).ready(() => {

    let side = 0;
    let debounceTimer;

    $(document).on('input', '.gdList__compare__input', (e) => {
        const $input = $(e.currentTarget);
        const $suggest = $('.gdList__compare__suggest');

        const input = $input.val().length;
        side = $input.data('input');

        clearTimeout(debounceTimer);

        if (input > 2) {
            debounceTimer = setTimeout(() => {
                request_suggest($input.val(), side);
                $suggest.removeClass('hiddenIp');
            }, 300);
        } else {
            $suggest.addClass('hiddenIp');
            $suggest.html('');
        }
    });

    $(document).on('click', '.gdList__compare__suggest__item', (e) => {
        close_suggest();
        add_level(e);
    });

    $(document).on('click', (e) => {
        if (!$(e.target).closest('.gdList__compare__suggest').length) {
            close_suggest();
        }
    });

    function add_level(e) {
        const $elem = $(e.currentTarget);

        const levelname = $elem.find('p b').text();
        const pos = Number($elem.find('p[data-pos]').data('pos'));

        const $sideelem = $(`.gdList__compare__levelwrapper__level[data-side="${side}"]`);
        const $levelText = $sideelem.find('p');

        $levelText.text(`${levelname} (#${pos})`);
        $levelText.attr('data-pos', pos);

        const pos1 = Number(
            $('.gdList__compare__levelwrapper__level[data-side="1"] p').attr('data-pos')
        );

        const pos2 = Number(
            $('.gdList__compare__levelwrapper__level[data-side="2"] p').attr('data-pos')
        );

        if (
            $('.gdList__compare__levelwrapper__level[data-side="1"] p').attr('data-pos') !== undefined &&
            $('.gdList__compare__levelwrapper__level[data-side="2"] p').attr('data-pos') !== undefined
        ) {
            calculate_difference(pos1, pos2);
        }
    }

    function request_suggest(input) {
        let query = input;
        let sart = $('.gdList__compare__wrapper').data('listtype') ?? 'AREDL';

        $.ajax({
            url: '/api/levels/search',
            method: 'GET',
            data: {
                q: query,
                sart: sart
            },
            dataType: 'json',

            success: function (response) {
                render_suggest(response);
            },

            error: function (xhr, status, error) {
                console.error('Error fetching levels: ', error);
            }
        });
    }

    function calculate_difference(pos1, pos2) {
        const $diffElem = $('.gdList__compare__difference');

        const difference = Math.abs(pos1 - pos2);

        $diffElem.find('.gdList__compare__difference__number').text(difference);
    }

    function render_suggest(levels, side) {
        const $suggest = $('.gdList__compare__suggest');

        let html = "";

        levels.forEach(function(curentValue, index, arr) {
            console.log(curentValue);
            html += `<div class="gdList__compare__suggest__item" data-levelid="${curentValue.level_id}">
                        <p><b>${curentValue.levelname}</b></p>
                        <p data-pos='${curentValue.position}'>#${curentValue.position}</p>
                     </div>`
        });

        $suggest.html(html);
    }

    function close_suggest() {
        const $suggest = $('.gdList__compare__suggest');
        $suggest.addClass("hiddenIp");
        $suggest.html('');
    }
});