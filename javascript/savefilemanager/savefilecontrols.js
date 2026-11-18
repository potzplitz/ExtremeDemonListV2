function initSavefileSetActive() {
    document.addEventListener('click', async (e) => {
        const btn = e.target.closest('.gdSaveFile__savelist__action[data-action="activate"]');
        if (!btn) return;

        const row = btn.closest('.gdSaveFile__savelist__row');
        const id  = row?.dataset.saveId;
        if (!id) return;

        btn.disabled = true;

        try {
            const formData = new FormData();
            formData.append('savefile_id', id);

            const res = await fetch('/api/account/savefiles/setactive', {
                method: 'POST',
                body: formData,
            });
            const result = await res.json();

            if (result.success == 1) {
                window.location.reload();
                return;
            }

            alert(result.error || 'Savefile could not be set as active.');
        } catch (err) {
            console.error('[Savefile] setactive', err);
            alert('Savefile could not be set as active.');
        } finally {
            btn.disabled = false;
        }
    });
}

function initSavefileDelete() {
    document.addEventListener('click', async (e) => {
        const btn = e.target.closest('.gdSaveFile__savelist__action[data-action="delete"]');
        if (!btn) return;

        const row = btn.closest('.gdSaveFile__savelist__row');
        const id  = row?.dataset.saveId;   // data-save-id → dataset.saveId
        if (!id) return;

        if (!confirm('Do you really want to delete this savefile?')) return;

        btn.disabled = true;

        try {
            const formData = new FormData();
            formData.append('savefile_id', id);

            const res = await fetch('/api/account/savefiles/delete', {
                method: 'POST',
                body: formData,
            });
            const result = await res.json();

            if (result.success == 1) {
                window.location.reload();
                return;
            }

            alert(result.error || 'Savefile could not be deleted.');
        } catch (err) {
            console.error('[Savefile] delete', err);
            alert('Savefile could not be deleted.');
        } finally {
            btn.disabled = false;
        }
    });
}

function initSavefileRename() {
    document.addEventListener('click', (e) => {
        const btn = e.target.closest('.gdSaveFile__savelist__action[data-action="rename"]');
        if (!btn) return;

        const row    = btn.closest('.gdSaveFile__savelist__row');
        const id     = row?.dataset.saveId;
        const nameEl = row?.querySelector('.gdSaveFile__savelist__file__name');
        if (!id || !nameEl || row.classList.contains('is-renaming')) return;

        const oldName = nameEl.textContent.trim();

        const input = document.createElement('input');
        input.type      = 'text';
        input.className = 'gdSaveFile__savelist__rename';
        input.value     = oldName;
        input.maxLength = 100;
        input.setAttribute('aria-label', 'Savefile name');

        nameEl.hidden = true;
        nameEl.after(input);
        row.classList.add('is-renaming');
        input.focus();
        input.select();

        let done = false;

        const close = () => {
            input.remove();
            nameEl.hidden = false;
            row.classList.remove('is-renaming');
        };

        const save = async () => {
            if (done) return;
            done = true;

            const value = input.value.trim();
            if (!value || value === oldName) {
                close();
                return;
            }

            input.disabled = true;

            try {
                const formData = new FormData();
                formData.append('savefile_id', id);
                formData.append('value', value);

                const res = await fetch('/api/account/savefiles/rename', {
                    method: 'POST',
                    body: formData,
                });
                const result = await res.json();

                if (result.success == 1) {
                    window.location.reload();
                    return;
                }

                alert(result.error || 'Savefile could not be renamed.');
            } catch (err) {
                console.error('[Savefile] rename', err);
                alert('Savefile could not be renamed.');
            }

            close();
        };

        input.addEventListener('keydown', (ev) => {
            if (ev.key === 'Enter') {
                ev.preventDefault();
                save();
            } else if (ev.key === 'Escape') {
                done = true;
                close();
            }
        });

        input.addEventListener('blur', save);
    });
}

function initSavefileImportRecords() {
    document.addEventListener('click', async (e) => {
        const btn = e.target.closest('[data-action="import-records"]');
        if (!btn) return;

        const id = btn.dataset.saveId;
        if (!id) return;

        btn.disabled = true;
        const label = btn.textContent;
        btn.textContent = 'Importing…';

        try {
            const formData = new FormData();
            formData.append('savefile_id', id);

            const res = await fetch('/api/account/savefiles/updaterecords', {
                method: 'POST',
                body: formData,
            });
            const result = await res.json();

            if (result.success == 1) {
                btn.textContent = 'Imported ✓';
                setTimeout(() => {
                    btn.textContent = label;
                    btn.disabled = false;
                }, 2000);
                return;
            }

            alert(result.error || 'Levels could not be imported.');
        } catch (err) {
            console.error('[Savefile] import records', err);
            alert('Levels could not be imported.');
        }

        btn.textContent = label;
        btn.disabled = false;
    });
}

function initSavefileDeleteImported() {
    document.addEventListener('click', async (e) => {
        const btn = e.target.closest('[data-action="delete-imported"]');
        if (!btn) return;

        if (!confirm('Do you really want to remove all imported levels from your list? Levels you checked yourself will stay.')) return;

        btn.disabled = true;
        const label = btn.textContent;
        btn.textContent = 'Deleting…';

        try {
            const res = await fetch('/api/account/savefiles/deleteimported', {
                method: 'POST',
            });
            const result = await res.json();

            if (result.success == 1) {
                btn.textContent = 'Deleted ✓';
                setTimeout(() => {
                    btn.textContent = label;
                    btn.disabled = false;
                }, 2000);
                return;
            }

            alert(result.error || 'Imported levels could not be deleted.');
        } catch (err) {
            console.error('[Savefile] delete imported', err);
            alert('Imported levels could not be deleted.');
        }

        btn.textContent = label;
        btn.disabled = false;
    });
}

function initSavefile() {
    initSavefileDelete();
    initSavefileSetActive();
    initSavefileRename();
    initSavefileImportRecords();
    initSavefileDeleteImported();
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initSavefile);
} else {
    initSavefile();
}
