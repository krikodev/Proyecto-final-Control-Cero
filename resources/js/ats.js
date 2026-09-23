// ── Registro EPP/ATS ──────────────────────────────────────────────
// Cámara en tiempo real, firma en canvas y guardas del formulario.
// Todo se activa solo si el formulario de inicio existe en la página.

const atsForm = document.getElementById('ats-start-form');

if (atsForm) {
    setupCamera();
    setupSignaturePad();
    setupTabs();
    setupSubmitGuard(atsForm);
}

function setupCamera() {
    const video = document.getElementById('ats-video');
    const canvas = document.getElementById('ats-photo-canvas');
    const preview = document.getElementById('ats-photo-preview');
    const placeholder = document.getElementById('ats-camera-placeholder');
    const hidden = document.getElementById('photo_data');
    const startBtn = document.getElementById('ats-camera-start');
    const captureBtn = document.getElementById('ats-camera-capture');
    const retakeBtn = document.getElementById('ats-camera-retake');
    const note = document.getElementById('ats-camera-note');

    if (!video || !canvas || !hidden) return;

    const ctx = canvas.getContext('2d');
    let stream = null;

    const setNote = (text) => {
        if (note) note.textContent = text;
    };

    const showPreview = (data) => {
        preview.src = data;
        preview.classList.remove('hidden');
        video.classList.add('hidden');
        placeholder.classList.add('hidden');
        captureBtn.classList.add('hidden');
        startBtn.classList.add('hidden');
        retakeBtn.classList.remove('hidden');
    };

    const stop = () => {
        if (!stream) return;
        stream.getTracks().forEach((track) => track.stop());
        stream = null;
        video.srcObject = null;
    };

    const start = async () => {
        setNote('');

        if (!navigator.mediaDevices?.getUserMedia) {
            setNote('Este navegador no permite usar la cámara. Sube la foto desde el archivo.');
            return;
        }

        try {
            stream = await navigator.mediaDevices.getUserMedia({
                video: { facingMode: 'environment' },
                audio: false,
            });

            video.srcObject = stream;
            await video.play();

            video.classList.remove('hidden');
            preview.classList.add('hidden');
            placeholder.classList.add('hidden');
            startBtn.classList.add('hidden');
            retakeBtn.classList.add('hidden');
            captureBtn.classList.remove('hidden');
        } catch (error) {
            setNote(
                'No se pudo acceder a la cámara (' +
                    (error?.name || 'error') +
                    '). Usa la opción de subir una foto.'
            );
            placeholder.classList.remove('hidden');
        }
    };

    const capture = () => {
        if (!stream) return;

        canvas.width = video.videoWidth || 1280;
        canvas.height = video.videoHeight || 720;
        ctx.drawImage(video, 0, 0, canvas.width, canvas.height);

        const data = canvas.toDataURL('image/jpeg', 0.85);
        hidden.value = data;

        stop();
        showPreview(data);
        setNote('');
    };

    const retake = () => {
        hidden.value = '';
        preview.classList.add('hidden');
        start();
    };

    startBtn?.addEventListener('click', start);
    captureBtn?.addEventListener('click', capture);
    retakeBtn?.addEventListener('click', retake);

    if (hidden.value) showPreview(hidden.value);

    window.addEventListener('pagehide', stop);
}

function setupSignaturePad() {
    const canvas = document.getElementById('ats-signature-canvas');
    const hidden = document.getElementById('signature_data');
    const clearBtn = document.getElementById('ats-signature-clear');

    if (!canvas || !hidden) return;

    const ctx = canvas.getContext('2d');
    const ratio = window.devicePixelRatio || 1;
    let drawing = false;

    const fit = () => {
        const rect = canvas.getBoundingClientRect();

        canvas.width = Math.max(1, Math.round(rect.width * ratio));
        canvas.height = Math.max(1, Math.round(rect.height * ratio));

        ctx.setTransform(ratio, 0, 0, ratio, 0, 0);
        ctx.lineWidth = 2;
        ctx.lineCap = 'round';
        ctx.lineJoin = 'round';
        ctx.strokeStyle = '#18181b';
    };

    const point = (event) => {
        const rect = canvas.getBoundingClientRect();

        return { x: event.clientX - rect.left, y: event.clientY - rect.top };
    };

    const save = () => {
        hidden.value = canvas.toDataURL('image/png');
    };

    fit();

    canvas.addEventListener('pointerdown', (event) => {
        event.preventDefault();
        drawing = true;

        try {
            canvas.setPointerCapture(event.pointerId);
        } catch {
            /* ignorado */
        }

        const p = point(event);
        ctx.beginPath();
        ctx.moveTo(p.x, p.y);
        ctx.lineTo(p.x + 0.01, p.y + 0.01);
        ctx.stroke();
    });

    canvas.addEventListener('pointermove', (event) => {
        if (!drawing) return;

        const p = point(event);
        ctx.lineTo(p.x, p.y);
        ctx.stroke();
    });

    const endStroke = (event) => {
        if (!drawing) return;
        drawing = false;

        try {
            canvas.releasePointerCapture(event.pointerId);
        } catch {
            /* ignorado */
        }

        save();
    };

    canvas.addEventListener('pointerup', endStroke);
    canvas.addEventListener('pointercancel', endStroke);

    clearBtn?.addEventListener('click', () => {
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        hidden.value = '';
    });
}

function setupTabs() {
    const tabs = Array.from(document.querySelectorAll('[data-ats-tab]'));

    if (!tabs.length) return;

    const select = (active) => {
        const target = active.dataset.atsTab;

        tabs.forEach((tab) => {
            const isActive = tab === active;

            tab.setAttribute('aria-selected', String(isActive));
            tab.classList.toggle('border-zinc-900', isActive);
            tab.classList.toggle('bg-zinc-900', isActive);
            tab.classList.toggle('text-white', isActive);
        });

        document.getElementById('ats-tab-draw')?.classList.toggle('hidden', target !== 'draw');
        document.getElementById('ats-tab-upload')?.classList.toggle('hidden', target !== 'upload');
    };

    tabs.forEach((tab) => tab.addEventListener('click', () => select(tab)));

    select(tabs.find((tab) => tab.getAttribute('aria-selected') === 'true') || tabs[0]);
}

function setupSubmitGuard(form) {
    form.addEventListener('submit', (event) => {
        const photo = document.getElementById('photo_data');
        const photoFile = document.getElementById('photo_file');
        const signature = document.getElementById('signature_data');
        const signatureFile = document.getElementById('signature_file');

        const hasPhoto = Boolean(photo?.value) || Boolean(photoFile?.files.length);
        const hasSignature = Boolean(signature?.value) || Boolean(signatureFile?.files.length);

        if (!hasPhoto) {
            event.preventDefault();
            window.alert('Debes tomar una foto con la cámara o subir una imagen.');
            document.getElementById('ats-camera-start')?.scrollIntoView({ behavior: 'smooth', block: 'center' });

            return;
        }

        if (!hasSignature) {
            event.preventDefault();
            window.alert('Debes dibujar tu firma o subir un archivo con ella.');
            document.getElementById('ats-signature-canvas')?.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    });
}
