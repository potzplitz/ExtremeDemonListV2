// Check/Uncheck + Attempts für die Extreme-Demon-Liste
(() => {
    const ITEM = '.gdList__item';
    const CHECKED_CLASS = 'gdList__item--checked';
    const LOADING_CLASS = 'gdList__item--loading';
    const SUBMIT_URL = '/api/records/submit';

    const isTruthy = (v) => v === '1' || v === 'true';

    const els = (item) => ({
        levelId: item.querySelector('.gdList__item__levelid'),
        attempts: item.querySelector('.gdList__item__attempts input'),
        state: item.querySelector('.gdList__item__state'),
        btn: item.querySelector('.gdList__item__check'),
    });

    const setChecked = (item, checked) => {
        const { state, btn, attempts } = els(item);
        if (!state || !btn) return;

        state.value = checked ? '1' : '0';
        item.classList.toggle(CHECKED_CLASS, checked);
        btn.setAttribute('aria-pressed', String(checked));
        btn.setAttribute('aria-label', checked ? 'Mark as not completed' : 'Mark as completed');

        // Attempts sind gespeichert -> sperren, bis wieder unchecked wird
        if (attempts) attempts.readOnly = checked;
    };

    const setLoading = (item, loading) => {
        const { btn } = els(item);
        item.classList.toggle(LOADING_CLASS, loading);
        if (btn) btn.disabled = loading;
    };

    // ---------- Toast ----------

    const toast = (message, type = 'error') => {
        let wrap = document.querySelector('.gdToast');
        if (!wrap) {
            wrap = document.createElement('div');
            wrap.className = 'gdToast';
            wrap.setAttribute('aria-live', 'polite');
            document.body.appendChild(wrap);
        }

        const el = document.createElement('div');
        el.className = `gdAlert gdAlert--${type} gdToast__item`;
        el.innerHTML = `
            <span class="gdAlert__icon">${type === 'error' ? '!' : '✓'}</span>
            <div class="gdAlert__content">
                <p class="gdAlert__content__message"></p>
            </div>`;
        el.querySelector('p').textContent = message;
        wrap.appendChild(el);

        setTimeout(() => {
            el.classList.add('gdToast__item--out');
            el.addEventListener('animationend', () => el.remove(), { once: true });
        }, 3500);
    };

    // ---------- Request ----------

    const submitRecord = async (item) => {
        const { levelId, attempts, state } = els(item);
        if (!levelId || !attempts || !state) return;

        const attemptCount = Math.max(0, Math.floor(Number(attempts.value) || 0));
        attempts.value = attemptCount;

        const params = new URLSearchParams({
            level_id: levelId.value,
            attempts: attemptCount,
        });

        setLoading(item, true);

        try {
            const res = await fetch(`${SUBMIT_URL}?${params}`, {
                method: 'GET',
                headers: { 'Accept': 'application/json' },
                cache: 'no-store', // jeder Klick muss wirklich beim Server ankommen
            });

            let data;
            try {
                data = await res.json();
            } catch {
                throw new Error(`Unexpected server response (${res.status})`);
            }

            if (data.error) {
                toast(data.error, 'error');
                return;
            }

            if (String(data.success) !== '1') {
                toast('Something went wrong. Please try again.', 'error');
                return;
            }

            // Zustand kommt vom Server, nicht vom Client
            if (data.action === 'submit') {
                setChecked(item, true);
                toast('Record saved!', 'success');
            } else if (data.action === 'delete') {
                setChecked(item, false);
                toast('Record removed.', 'success');
            }

            state.dispatchEvent(new Event('change', { bubbles: true }));
        } catch (err) {
            toast(err.message || 'Network error. Please try again.', 'error');
        } finally {
            setLoading(item, false);
        }
    };

    // ---------- Init ----------

    document.querySelectorAll(ITEM).forEach((item) => {
        const { state } = els(item);
        if (state) setChecked(item, isTruthy(state.value));
    });

    // Check-Button
    document.addEventListener('click', (e) => {
        const btn = e.target.closest('.gdList__item__check');
        if (!btn || btn.disabled) return;
        submitRecord(btn.closest(ITEM));
    });

    // Enter im Attempts-Feld = abhaken (nur wenn noch nicht abgehakt)
    document.addEventListener('keydown', (e) => {
        if (e.key !== 'Enter' || !e.target.matches('.gdList__item__attempts input')) return;
        e.preventDefault();
        const item = e.target.closest(ITEM);
        if (!item.classList.contains(CHECKED_CLASS) && !item.classList.contains(LOADING_CLASS)) {
            submitRecord(item);
        }
    });

    // Attempts: beim Antippen alles markieren
    document.addEventListener('focusin', (e) => {
        if (e.target.matches('.gdList__item__attempts input') && !e.target.readOnly) e.target.select();
    });

    // Attempts: nur ganze Zahlen >= 0
    document.addEventListener('change', (e) => {
        if (!e.target.matches('.gdList__item__attempts input')) return;
        e.target.value = Math.max(0, Math.floor(Number(e.target.value) || 0));
    });
})();