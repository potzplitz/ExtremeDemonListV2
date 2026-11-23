(() => {
    const MAX_SIZE = 5 * 1024 * 1024;   // max. 5 MB Originaldatei
    const OUTPUT_SIZE = 512;            // Ausgabe: 512x512 px
    const ALLOWED_TYPES = ['image/png', 'image/jpeg', 'image/webp'];

    // Funktioniert egal ob das Script im <head> oder am Ende von <body> eingebunden ist
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initAll);
    } else {
        initAll();
    }

    function initAll() {
        document.querySelectorAll('[data-pfp-upload]').forEach(initPfpUpload);
    }

    function initPfpUpload(root) {
        if (root.dataset.pfpReady) return;
        root.dataset.pfpReady = '1';

        const uploadUrl = root.dataset.uploadUrl;
        const img = root.querySelector('.gdPfpUpload__img');
        const input = root.querySelector('.gdPfpUpload__input');
        const bar = root.querySelector('.gdPfpUpload__progressBar');
        const msg = root.querySelector('.gdPfpUpload__msg');

        let busy = false;

        // Der Datei-Dialog öffnet sich nativ über die <label>s, hier nur auswerten
        input.addEventListener('change', () => {
            const file = input.files[0];
            input.value = ''; // gleiche Datei erneut wählbar
            if (file) handleFile(file);
        });

        // Drag & Drop auf den ganzen Block
        ['dragenter', 'dragover'].forEach(type =>
            root.addEventListener(type, e => {
                e.preventDefault();
                if (!busy) root.classList.add('is-dragover');
            })
        );

        root.addEventListener('dragleave', e => {
            if (!root.contains(e.relatedTarget)) root.classList.remove('is-dragover');
        });

        root.addEventListener('drop', e => {
            e.preventDefault();
            root.classList.remove('is-dragover');
            const file = e.dataTransfer?.files[0];
            if (file && !busy) handleFile(file);
        });

        async function handleFile(file) {
            setMessage('');

            if (!ALLOWED_TYPES.includes(file.type)) {
                return setMessage('Only PNG, JPG or WebP allowed.', true);
            }
            if (file.size > MAX_SIZE) {
                return setMessage('File is too large (max. 5 MB).', true);
            }

            let blob;
            try {
                blob = await cropToSquare(file, OUTPUT_SIZE);
            } catch {
                return setMessage('Could not read the image.', true);
            }

            // Sofortige Vorschau
            const previousSrc = img.getAttribute('src');
            const previewUrl = URL.createObjectURL(blob);
            img.src = previewUrl;

            setBusy(true);
            try {
                await upload(blob);
                if (previousSrc?.startsWith('blob:')) URL.revokeObjectURL(previousSrc);
                setMessage('Profile picture saved.');
            } catch (err) {
                img.setAttribute('src', previousSrc ?? '');
                URL.revokeObjectURL(previewUrl);
                setMessage(err.message, true);
            } finally {
                setBusy(false);
            }
        }

        function upload(blob) {
            return new Promise((resolve, reject) => {
                const data = new FormData();
                data.append('pfp', blob, `pfp.${blob.type.split('/')[1]}`);

                const xhr = new XMLHttpRequest();
                xhr.open('POST', uploadUrl);
                xhr.responseType = 'json';

                xhr.upload.addEventListener('progress', e => {
                    if (e.lengthComputable) setProgress(e.loaded / e.total);
                });

                xhr.addEventListener('load', () => {
                    if (xhr.status >= 200 && xhr.status < 300) {
                        setProgress(1);
                        resolve(xhr.response);
                    } else {
                        reject(new Error(xhr.response?.error || `Upload failed (${xhr.status}).`));
                    }
                });

                xhr.addEventListener('error', () => reject(new Error('Network error – please try again.')));
                xhr.send(data);
            });
        }

        function setBusy(state) {
            busy = state;
            input.disabled = state; // blockiert den Dialog während des Uploads
            root.classList.toggle('is-uploading', state);
            root.setAttribute('aria-busy', String(state));
            if (state) setProgress(0);
        }

        function setProgress(ratio) {
            bar.style.strokeDashoffset = String(100 - ratio * 100);
        }

        function setMessage(text, isError = false) {
            msg.textContent = text;
            root.classList.toggle('is-error', isError);
        }
    }

    // Mittig quadratisch zuschneiden + verkleinern (spart Upload & Speicher)
    async function cropToSquare(file, maxSize) {
        const bitmap = await createImageBitmap(file);
        const side = Math.min(bitmap.width, bitmap.height);
        const sx = (bitmap.width - side) / 2;
        const sy = (bitmap.height - side) / 2;
        const size = Math.min(maxSize, side);

        const canvas = document.createElement('canvas');
        canvas.width = canvas.height = size;

        const ctx = canvas.getContext('2d');
        ctx.imageSmoothingQuality = 'high';
        ctx.drawImage(bitmap, sx, sy, side, side, 0, 0, size, size);
        bitmap.close();

        // WebP; Browser ohne WebP-Encoder liefern automatisch PNG
        return new Promise((resolve, reject) =>
            canvas.toBlob(b => (b ? resolve(b) : reject(new Error('toBlob failed'))), 'image/webp', 0.9)
        );
    }
})();