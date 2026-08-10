<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Lumen — Tanya Jawab Dokumen</title>
    <meta name="description" content="Upload dokumen, tanya isinya. Lumen menunjukkan bagian dokumen yang jadi sumber jawaban.">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:ital,opsz,wght@0,9..144,400;0,9..144,500;1,9..144,500&family=IBM+Plex+Mono:wght@400;500&family=IBM+Plex+Sans:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg: #fafaf7;
            --ink: #211d18;
            --ink-muted: #6b6459;
            --rule: #c9c0ac;
            --lumen: #f4b942;
            --lumen-deep: #c98a1f;
            --danger: #a6432e;
            --font-display: 'Fraunces', serif;
            --font-body: 'IBM Plex Sans', sans-serif;
            --font-mono: 'IBM Plex Mono', monospace;
        }

        * { box-sizing: border-box; }

        html, body {
            margin: 0;
            padding: 0;
            min-height: 100%;
        }

        body {
            background:
                radial-gradient(circle at 15% 0%, rgba(244, 185, 66, .22), transparent 55%),
                radial-gradient(circle at 85% 100%, rgba(244, 185, 66, .14), transparent 50%),
                var(--bg);
            color: var(--ink-muted);
            font-family: var(--font-body);
            display: flex;
            flex-direction: column;
        }

        .sr-only {
            position: absolute;
            width: 1px; height: 1px;
            overflow: hidden;
            clip: rect(0 0 0 0);
        }

        a { color: var(--lumen-deep); }
        a:hover { color: var(--ink); }

        :focus-visible {
            outline: 3px solid rgba(244, 185, 66, .65);
            outline-offset: 2px;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: baseline;
            gap: 1rem;
            flex-wrap: wrap;
            padding: 2rem 1.5rem 1rem;
        }

        .wordmark {
            font-family: var(--font-display);
            font-style: italic;
            font-weight: 500;
            font-size: 1.75rem;
            color: var(--ink);
            letter-spacing: 0.01em;
        }

        .tagline {
            font-family: var(--font-mono);
            font-size: 0.7rem;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: var(--ink-muted);
        }

        .stage {
            flex: 1;
            display: flex;
            justify-content: center;
            align-items: flex-start;
            padding: 1.5rem;
        }

        .card-wrap {
            position: relative;
            width: 100%;
            max-width: 640px;
        }

        .card-wrap::before {
            content: "";
            position: absolute;
            inset: -25% -15%;
            background: radial-gradient(closest-side, rgba(244, 185, 66, .35), transparent 70%);
            filter: blur(16px);
            pointer-events: none;
            z-index: 0;
        }

        .card {
            position: relative;
            z-index: 1;
            background: rgba(255, 255, 255, .55);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, .8);
            color: var(--ink);
            border-radius: 12px;
            padding: 2.25rem;
            box-shadow: 0 30px 60px -25px rgba(33, 29, 24, .25);
        }

        .intro {
            font-family: var(--font-display);
            font-size: 1.3rem;
            line-height: 1.45;
            margin: 0 0 1.5rem;
            color: var(--ink);
        }

        .active-doc {
            display: flex;
            align-items: baseline;
            gap: 0.5rem;
            font-family: var(--font-mono);
            font-size: 0.75rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid var(--rule);
            margin-bottom: 1rem;
        }

        .active-doc__label {
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: var(--ink-muted);
        }

        .active-doc__name {
            font-family: var(--font-body);
            font-size: 0.9rem;
            font-weight: 600;
        }

        .active-doc__name em {
            font-style: normal;
            font-weight: 400;
            color: var(--ink-muted);
        }

        details.upload-toggle {
            margin-bottom: 1.25rem;
        }

        details.upload-toggle summary {
            display: inline-block;
            cursor: pointer;
            font-family: var(--font-mono);
            font-size: 0.75rem;
            letter-spacing: 0.04em;
            color: var(--lumen-deep);
            list-style: none;
            padding: 0.4rem 0.75rem;
            border: 1px solid var(--lumen-deep);
            border-radius: 3px;
            background: rgba(244, 185, 66, .12);
        }

        details.upload-toggle summary:hover {
            background: rgba(244, 185, 66, .22);
        }

        details.upload-toggle summary::-webkit-details-marker { display: none; }
        details.upload-toggle summary::before { content: "+ "; }
        details.upload-toggle[open] summary::before { content: "\2212 "; }

        .upload-form {
            margin-top: 0.75rem;
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 0.6rem;
        }

        .upload-form input[type="file"] {
            font-family: var(--font-body);
            font-size: 0.85rem;
            color: var(--ink);
            max-width: 100%;
        }

        .hint {
            width: 100%;
            margin: 0.3rem 0 0;
            font-family: var(--font-mono);
            font-size: 0.7rem;
            color: var(--ink-muted);
        }

        .upload-status {
            width: 100%;
            margin: 0.4rem 0 0;
            font-family: var(--font-mono);
            font-size: 0.75rem;
            min-height: 1em;
        }

        .ask-form {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
        }

        textarea {
            font-family: var(--font-body);
            font-size: 1rem;
            padding: 0.85rem;
            border: 1px solid var(--rule);
            border-radius: 3px;
            background: #fff;
            color: var(--ink);
            resize: vertical;
            min-height: 4.5rem;
        }

        .btn {
            font-family: var(--font-mono);
            font-size: 0.8rem;
            letter-spacing: 0.03em;
            border: none;
            border-radius: 3px;
            padding: 0.7rem 1.1rem;
            cursor: pointer;
            align-self: flex-end;
        }

        .btn--primary {
            background: var(--lumen);
            color: var(--ink);
        }

        .btn--primary:hover { background: var(--lumen-deep); }
        .btn--primary:disabled { opacity: 0.5; cursor: wait; }

        .btn--ghost {
            background: transparent;
            border: 1px solid var(--ink-muted);
            color: var(--ink-muted);
        }

        .btn--ghost:hover { background: rgba(0, 0, 0, .05); }

        .status-line {
            font-family: var(--font-mono);
            font-size: 0.8rem;
            color: var(--ink-muted);
            min-height: 1.2em;
            margin: 0.9rem 0 0;
        }

        .status-line.is-error { color: var(--danger); }

        .answer {
            margin-top: 1rem;
            padding-top: 1rem;
            border-top: 1px solid var(--rule);
        }

        .answer__text {
            font-size: 1.02rem;
            line-height: 1.6;
            margin: 0 0 1rem;
        }

        .sources__label {
            font-family: var(--font-mono);
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: var(--ink-muted);
            margin: 0 0 0.6rem;
        }

        .source {
            background: transparent;
            border-left: 3px solid transparent;
            border-radius: 6px;
            padding: 0.6rem 0.85rem;
            margin-bottom: 0.9rem;
            opacity: 0;
            transform: translateY(4px);
            transition: opacity .5s ease, transform .5s ease, border-color .5s ease, background .5s ease;
        }

        .source--lit {
            opacity: 1;
            transform: translateY(0);
            border-left-color: var(--lumen);
            background: rgba(255, 255, 255, .4);
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
        }

        .source__excerpt {
            font-size: 0.88rem;
            line-height: 1.55;
            color: var(--ink);
            margin: 0 0 0.3rem;
        }

        .source__file {
            font-family: var(--font-mono);
            font-size: 0.7rem;
            color: var(--ink-muted);
            margin: 0;
        }

        .ledger {
            display: flex;
            flex-wrap: wrap;
            gap: 0.4rem 1rem;
            justify-content: center;
            padding: 1.5rem 1rem 2rem;
            font-family: var(--font-mono);
            font-size: 0.7rem;
            letter-spacing: 0.03em;
            color: var(--ink-muted);
            border-top: 1px solid var(--rule);
            margin-top: 1rem;
        }

        .ledger a { text-decoration: none; }
        .ledger a:hover { text-decoration: underline; }

        @media (max-width: 480px) {
            .card { padding: 1.5rem; }
            .intro { font-size: 1.15rem; }
            .btn { align-self: stretch; }
        }

        @media (prefers-reduced-motion: reduce) {
            .source { transition: none; }
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="wordmark">Lumen</div>
        <div class="tagline">Tanya jawab dokumen &middot; Laravel + pgvector</div>
    </div>

    <main class="stage">
        <div class="card-wrap">
            <div class="card">
                <p class="intro">Tanya sesuatu. Lumen membaca dokumenmu, mencari bagian yang benar-benar menjawab, dan menunjukkan bagian itu — bukan cuma jawabannya.</p>

                <div class="active-doc">
                    <span class="active-doc__label">Dokumen aktif</span>
                    <span class="active-doc__name" id="activeDocName">Aurion Dynamics <em>(contoh)</em></span>
                </div>

                <details class="upload-toggle">
                    <summary>Upload dokumen lain</summary>
                    <form id="uploadForm" class="upload-form">
                        <input type="file" name="file" id="fileInput" accept=".pdf,.txt" required>
                        <button type="submit" class="btn btn--ghost">Index dokumen</button>
                        <p class="hint">PDF atau teks, maksimal 2MB.</p>
                        <p class="upload-status" id="uploadStatus" role="status"></p>
                    </form>
                </details>

                <form id="askForm" class="ask-form">
                    <label for="question" class="sr-only">Pertanyaan</label>
                    <textarea id="question" name="question" rows="2" placeholder="Mau tanya apa?" required maxlength="2000"></textarea>
                    <button type="submit" class="btn btn--primary" id="askBtn">Tanya &rarr;</button>
                </form>

                <p class="status-line" id="statusLine" role="status" aria-live="polite"></p>

                <div class="answer" id="answer" hidden>
                    <p class="answer__text" id="answerText"></p>
                    <div id="sourcesBlock">
                        <p class="sources__label">Dari dokumen:</p>
                        <div id="sources"></div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <footer class="ledger">
        <span>MODEL gemini-flash-latest</span>
        <span>CHUNK 500 / OVERLAP 100</span>
        <span>TOP-K 3</span>
        <span>EMBED 768D</span>
        <a href="https://github.com/kearakha/lumen-rag" target="_blank" rel="noopener">kode di GitHub &rarr;</a>
    </footer>

    <script>
        (function () {
            let activeDocId = {{ (int) (\App\Models\Document::query()->orderBy('id')->value('id') ?? 1) }};

            const activeDocName = document.getElementById('activeDocName');
            const uploadForm = document.getElementById('uploadForm');
            const uploadStatus = document.getElementById('uploadStatus');
            const askForm = document.getElementById('askForm');
            const askBtn = document.getElementById('askBtn');
            const questionInput = document.getElementById('question');
            const statusLine = document.getElementById('statusLine');
            const answerBlock = document.getElementById('answer');
            const answerText = document.getElementById('answerText');
            const sourcesEl = document.getElementById('sources');

            uploadForm.addEventListener('submit', async (e) => {
                e.preventDefault();
                const file = document.getElementById('fileInput').files[0];
                if (!file) return;

                uploadStatus.textContent = 'Mengindex dokumen…';
                uploadStatus.classList.remove('is-error');

                const body = new FormData();
                body.append('file', file);

                try {
                    const res = await fetch('/api/documents', { method: 'POST', body, headers: { Accept: 'application/json' } });
                    const data = await res.json();

                    if (!res.ok) {
                        uploadStatus.textContent = data.message || 'Upload gagal.';
                        uploadStatus.classList.add('is-error');
                        return;
                    }

                    activeDocId = data.document_id;
                    activeDocName.innerHTML = escapeHtml(data.filename) + ' <em>(baru diupload)</em>';
                    uploadStatus.textContent = data.chunks + ' bagian ter-index. Siap ditanya.';
                    uploadStatus.classList.remove('is-error');
                } catch {
                    uploadStatus.textContent = 'Lumen sedang tidak bisa dihubungi. Coba lagi sebentar lagi.';
                    uploadStatus.classList.add('is-error');
                }
            });

            askForm.addEventListener('submit', async (e) => {
                e.preventDefault();
                const question = questionInput.value.trim();
                if (!question) return;

                askBtn.disabled = true;
                answerBlock.hidden = true;
                sourcesEl.innerHTML = '';
                statusLine.classList.remove('is-error');
                statusLine.textContent = 'Mengirim pertanyaan…';

                try {
                    const res = await fetch('/api/ask', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
                        body: JSON.stringify({ question, document_id: activeDocId }),
                    });
                    const data = await res.json();

                    if (!res.ok) {
                        statusLine.textContent = data.message || 'Pertanyaan ditolak.';
                        statusLine.classList.add('is-error');
                        askBtn.disabled = false;
                        return;
                    }

                    listen(data.ask_id);
                } catch {
                    statusLine.textContent = 'Lumen sedang tidak bisa dihubungi. Coba lagi sebentar lagi.';
                    statusLine.classList.add('is-error');
                    askBtn.disabled = false;
                }
            });

            function listen(askId) {
                const es = new EventSource('/api/ask/' + askId + '/stream');

                es.addEventListener('status', (e) => {
                    const payload = JSON.parse(e.data);
                    statusLine.textContent = payload.status === 'processing'
                        ? 'Membaca dokumen…'
                        : 'Menunggu giliran…';
                });

                es.addEventListener('done', (e) => {
                    const payload = JSON.parse(e.data);
                    statusLine.textContent = '';
                    showAnswer(payload.answer, payload.sources || []);
                    askBtn.disabled = false;
                    es.close();
                });

                es.addEventListener('error', (e) => {
                    if (e.data) {
                        const payload = JSON.parse(e.data);
                        statusLine.textContent = payload.message;
                        statusLine.classList.add('is-error');
                        askBtn.disabled = false;
                        es.close();
                    }
                });
            }

            function showAnswer(text, sources) {
                answerText.textContent = text;
                answerBlock.hidden = false;
                sourcesEl.innerHTML = '';

                sources.forEach((s, i) => {
                    const div = document.createElement('div');
                    div.className = 'source';
                    const excerptP = document.createElement('p');
                    excerptP.className = 'source__excerpt';
                    excerptP.textContent = s.excerpt;
                    const fileP = document.createElement('p');
                    fileP.className = 'source__file';
                    fileP.textContent = s.document;
                    div.appendChild(excerptP);
                    div.appendChild(fileP);
                    sourcesEl.appendChild(div);

                    setTimeout(() => div.classList.add('source--lit'), 120 * (i + 1));
                });
            }

            function escapeHtml(str) {
                const div = document.createElement('div');
                div.textContent = str;
                return div.innerHTML;
            }
        })();
    </script>
</body>
</html>
