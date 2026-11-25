(() => {
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initAll);
    } else {
        initAll();
    }

    function initAll() {
        document.querySelectorAll('[data-order-form]').forEach(initOrderForm);
    }

    function initOrderForm(form) {
        const list = form.querySelector('[data-order-list]');
        const input = form.querySelector('[data-order-input]');
        if (!list || !input) return;

        const items = () => [...list.querySelectorAll('.gdOrder__item')];

        function writeOrder() {
            input.value = JSON.stringify(items().map(li => li.dataset.key));
        }

        function renumber() {
            items().forEach((li, i, arr) => {
                li.querySelector('.gdOrder__pos').textContent = i + 1;
                li.querySelector('[data-dir="up"]').disabled = i === 0;
                li.querySelector('[data-dir="down"]').disabled = i === arr.length - 1;
            });
            writeOrder();
        }

        list.addEventListener('click', e => {
            const btn = e.target.closest('.gdIconBtn');
            const dir = btn?.dataset.dir;
            if (!dir) return;

            const item = btn.closest('.gdOrder__item');
            if (dir === 'up' && item.previousElementSibling) list.insertBefore(item, item.previousElementSibling);
            if (dir === 'down' && item.nextElementSibling) list.insertBefore(item.nextElementSibling, item);
            renumber();

            (btn.disabled ? item.querySelector('.gdIconBtn:not(:disabled)') : btn)?.focus();
        });

        let dragged = null;

        list.addEventListener('dragstart', e => {
            dragged = e.target.closest('.gdOrder__item');
            if (!dragged) return;
            dragged.classList.add('is-dragging');
            e.dataTransfer.effectAllowed = 'move';
            e.dataTransfer.setData('text/plain', dragged.dataset.key);
        });

        list.addEventListener('dragend', () => {
            dragged?.classList.remove('is-dragging');
            items().forEach(li => li.classList.remove('is-over'));
            dragged = null;
            renumber();
        });

        list.addEventListener('dragover', e => {
            if (!dragged) return;
            e.preventDefault();

            const target = e.target.closest('.gdOrder__item');
            if (!target || target === dragged) return;

            items().forEach(li => li.classList.remove('is-over'));
            target.classList.add('is-over');

            const rect = target.getBoundingClientRect();
            const after = e.clientY > rect.top + rect.height / 2;
            list.insertBefore(dragged, after ? target.nextElementSibling : target);
        });

        list.addEventListener('drop', e => {
            if (dragged) e.preventDefault();
        });

        form.addEventListener('submit', writeOrder);

        renumber();
    }
})();
