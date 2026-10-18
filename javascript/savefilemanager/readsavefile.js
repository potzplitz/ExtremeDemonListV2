/**
 * Savefile Upload + lokales Lesen (Geometry Dash CCGameManager.dat)
 * Ersetzt savefile-upload.js und savefile-reader.js – nur diese eine Datei einbinden.
 */

const GDSave = (() => {

    async function decode(buffer) {
        const bytes = new Uint8Array(buffer);

        // Bereits entschlüsselt?
        const head = new TextDecoder().decode(bytes.subarray(0, 5));
        if (head === '<?xml') {
            return new TextDecoder().decode(bytes);
        }

        // 1. XOR mit 11
        const xored = new Uint8Array(bytes.length);
        for (let i = 0; i < bytes.length; i++) {
            xored[i] = bytes[i] ^ 11;
        }

        // 2. URL-safe Base64 → normales Base64
        let b64 = new TextDecoder().decode(xored)
            .replace(/\0/g, '')
            .trim()
            .replace(/-/g, '+')
            .replace(/_/g, '/');

        while (b64.length % 4) b64 += '=';

        const binary = atob(b64);
        const compressed = new Uint8Array(binary.length);
        for (let i = 0; i < binary.length; i++) {
            compressed[i] = binary.charCodeAt(i);
        }

        // 3. gzip entpacken
        const stream = new Blob([compressed])
            .stream()
            .pipeThrough(new DecompressionStream('gzip'));

        return new Response(stream).text();
    }

    function parsePlist(xmlString) {
        const doc = new DOMParser().parseFromString(xmlString, 'application/xml');

        if (doc.querySelector('parsererror')) {
            throw new Error('Savefile XML could not be parsed.');
        }

        const root = doc.querySelector('plist > dict');
        if (!root) throw new Error('No savefile data found.');

        return parseDict(root);
    }

    function parseDict(dictEl) {
        const result = {};
        const children = Array.from(dictEl.children);

        for (let i = 0; i < children.length; i++) {
            if (children[i].tagName !== 'k') continue;
            result[children[i].textContent] = parseValue(children[i + 1]);
            i++;
        }

        return result;
    }

    function parseValue(el) {
        if (!el) return null;

        switch (el.tagName) {
            case 's': return el.textContent;
            case 'i': return parseInt(el.textContent, 10);
            case 'r': return parseFloat(el.textContent);
            case 't': return true;
            case 'f': return false;
            case 'd': return parseDict(el);
            default:  return el.textContent;
        }
    }

    async function load(file) {
        const buffer = await file.arrayBuffer();
        const xml = await decode(buffer);
        return parsePlist(xml);
    }

    return { load, decode, parsePlist };
})();


function initSavefileUpload() {
    const form = document.getElementById('savefile-form');

    if (!form) {
        console.warn('[Savefile] #savefile-form not found – upload not initialised.');
        return;
    }

    const q = (sel) => form.querySelector(sel);
    const input    = q('.gdSaveFile__upload__input');
    const dropzone = q('.gdSaveFile__upload__dropzone');
    const fileBox  = q('.gdSaveFile__upload__file');
    const nameEl   = q('.gdSaveFile__upload__file__name');
    const sizeEl   = q('.gdSaveFile__upload__file__size');
    const removeEl = q('.gdSaveFile__upload__file__remove');
    const errorEl  = q('.gdSaveFile__upload__error');
    const submit   = q('.gdSaveFile__upload__submit');

    let currentFile = null;

    const formatSize = (b) =>
        b < 1024 ? `${b} B`
        : b < 1024 ** 2 ? `${(b / 1024).toFixed(1)} KB`
        : `${(b / 1024 ** 2).toFixed(1)} MB`;

    const showError = (msg) => {
        errorEl.textContent = msg || '';
        errorEl.hidden = !msg;
    };

    const setFile = (file) => {
        showError('');
        currentFile = null;

        if (!file) {
            input.value = '';
            fileBox.hidden = true;
            submit.disabled = true;
            return;
        }

        if (!file.name.toLowerCase().endsWith('.dat')) {
            input.value = '';
            fileBox.hidden = true;
            submit.disabled = true;
            showError('Please choose a .dat savefile.');
            return;
        }

        currentFile = file;
        nameEl.textContent = file.name;
        nameEl.title = file.name;
        sizeEl.textContent = formatSize(file.size);
        fileBox.hidden = false;
        submit.disabled = false;
    };

    // Auswahl per Dateidialog
    input.addEventListener('change', () => setFile(input.files[0]));

    removeEl.addEventListener('click', () => {
        setFile(null);
        input.focus();
    });

    // Drag & Drop
    ['dragenter', 'dragover'].forEach((ev) =>
        dropzone.addEventListener(ev, (e) => {
            e.preventDefault();
            dropzone.classList.add('is-dragover');
        })
    );

    ['dragleave', 'drop'].forEach((ev) =>
        dropzone.addEventListener(ev, (e) => {
            e.preventDefault();
            dropzone.classList.remove('is-dragover');
        })
    );

    dropzone.addEventListener('drop', (e) => {
        const file = e.dataTransfer.files[0];
        if (file) setFile(file);
    });

    // Verhindert, dass der Browser eine daneben gedroppte Datei öffnet
    ['dragover', 'drop'].forEach((ev) =>
        window.addEventListener(ev, (e) => e.preventDefault())
    );

    // Lesen
    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        if (!currentFile) return;

        showError('');
        submit.disabled = true;
        const label = submit.textContent;
        submit.textContent = 'Reading…';

        try {
            const save = await GDSave.load(currentFile);

            const json = JSON.stringify(save, null, 2);

            const playerdata = {
                "username": save.playerName,
                "player_userid": save.playerUserID,
                "player_uuid": save.playerUUID,
                "player_cube": save.playerFrame,
                "player_ship": save.playerShip,
                "player_ball": save.playerBall,
                "player_ufo": save.playerBird,
                "player_wave": save.playerDart,
                "player_robot": save.playerRobot,
                "player_spider": save.playerSpider,
                "player_swing": save.playerSwing,
                "player_color_1": save.playerColor,
                "player_color_2": save.playerColor2,
                "player_jetpack": save.playerJetpack,
                "player_glow": save.playerGlow
            };
            
            const leveldata = {
                onlinelevels: {}
            };

            let total_stars    = 0;
            let total_jumps    = 0;
            let total_clicks   = 0;
            let total_attempts = 0;
            let total_user_coins = 0;
            let total_demons = 0;

            for (const [id, level] of Object.entries(save.GLM_03 ?? {})) {

                if ((level.k19 ?? 0) >= 100) {
                    total_stars += level.k26 ?? 0;
                }

                total_jumps    += level.k36 ?? 0;
                total_clicks   += level.k85 ?? 0;
                total_attempts += level.k18 ?? 0;
                

                if (!level.k25) {
                    continue;
                }

                if ((level.k19 ?? 0) >= 100) {
                    total_demons++;
                }

                leveldata.onlinelevels[id] = {
                    level_id: level.k1,
                    levelname: level.k2,
                    leveldifficulty: level.k7,
                    song_id: level.k8,
                    rating: level.k9,
                    game_version: level.k17,
                    attempts: level.k18,
                    progress: level.k19,
                    jumps: level.k36,
                    levelsize: level.k39,
                    password: level.k41,
                    custom_song_id: level.k45,
                    object_count: level.k48,
                    first_coin: level.k61,
                    second_coin: level.k62,
                    third_coin: level.k63,
                    total_coins: level.k64,
                    trigger_anticheat: level.k68,
                    epic_rating: level.k75,
                    demon_type: level.k76,
                    clicks: level.k85,
                    progress_string: level.k88,
                    vfdchk: level.k89,
                };
            }

            const stats = save.GS_value ?? {};

            playerdata.total_user_coins   = Number(stats['12'] ?? 0);
            playerdata.total_secret_coins = Number(stats['8'] ?? 0);

            playerdata.total_stars    = total_stars;
            playerdata.total_jumps    = total_jumps;
            playerdata.total_clicks   = total_clicks;
            playerdata.total_attempts = total_attempts;
            playerdata.total_demons = total_demons;

            const form = new FormData();
            form.append('playerdata', JSON.stringify(playerdata)); // String oder File/Blob
            form.append('leveldata', JSON.stringify(leveldata));

            const res = await fetch('/api/account/savefiles/upload', {
                method: 'POST',
                body: form,
            });
            const result = await res.json();

            if(result.success == 1) {
                window.location.reload();
                return;
            }

            showError(result.error || 'Upload failed.');

            // TODO: speichern und Tabelle/Cards rendern
        } catch (err) {
            console.error('[Savefile]', err);
            showError('This file could not be read. Is it a valid Geometry Dash savefile?');
        } finally {
            submit.textContent = label;
            submit.disabled = false;
        }
    });

    console.log('[Savefile] upload initialised');
}

function initSavefile() {
    initSavefileUpload();
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