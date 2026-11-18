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

function parseCoinDict(dict) {
    const out = {};
    for (const key of Object.keys(dict ?? {})) {
        const [levelId, coinIdxRaw] = key.split('_');
        const idx = parseInt(coinIdxRaw, 10);
        if (!levelId || ![1, 2, 3].includes(idx)) continue;
        (out[levelId] ??= []).push(idx);
    }
    for (const id in out) out[id].sort();
    return out;
}

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

            // Verifizierte & unverifizierte Coins pro Level-ID
            const verifiedCoins = parseCoinDict(save.GS_4);
            const pendingCoins  = parseCoinDict(save.GS_3);

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

                const coinsVerified = verifiedCoins[level.k1] ?? [];
                const coinsPending  = pendingCoins[level.k1] ?? [];

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
                    player_first_coin: coinsVerified.includes(1),
                    player_second_coin: coinsVerified.includes(2),
                    player_third_coin: coinsVerified.includes(3),
                    player_first_coin_pending: coinsPending.includes(1),
                    player_second_coin_pending: coinsPending.includes(2),
                    player_third_coin_pending: coinsPending.includes(3),
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
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initSavefile);
} else {
    initSavefile();
}