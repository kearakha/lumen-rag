<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Lumen — Ask</title>
    <meta name="description" content="Upload a document, ask it questions. Lumen shows you the exact passage that answers you — not just an answer.">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:ital,opsz,wght@0,9..144,400;0,9..144,500;0,9..144,600;1,9..144,500&family=IBM+Plex+Mono:wght@400;500&family=IBM+Plex+Sans:wght@400;500;600&display=swap" rel="stylesheet">
    @include('partials.styles')
    <style>
        .stage { max-width: 640px; width: 100%; margin: 2.5rem auto 0; padding: 0 1.5rem 3.5rem; display: flex; flex-direction: column; gap: 1.25rem; }

        .card--doc { position: relative; padding: 1.5rem 1.75rem; }

        .active-doc { display: flex; flex-wrap: wrap; align-items: baseline; gap: 0.5rem; font-family: var(--font-mono); font-size: 0.75rem; padding-right: 2.5rem; }
        .active-doc__label { text-transform: uppercase; letter-spacing: 0.08em; color: var(--ink-muted); }
        .active-doc__name { font-family: var(--font-body); font-size: 0.9rem; font-weight: 600; }
        .active-doc__name em { font-style: normal; font-weight: 400; color: var(--ink-muted); }

        .upload-toggle summary.upload-fab {
            position: absolute; top: 1.25rem; right: 1.5rem;
            width: 2rem; height: 2rem;
            display: flex; align-items: center; justify-content: center;
            cursor: pointer; list-style: none; border-radius: 50%;
            background: var(--lumen); color: var(--ink);
            font-family: var(--font-mono); font-size: 1.1rem; font-weight: 600;
            box-shadow: 0 8px 18px -8px rgba(201, 138, 31, .7);
        }

        .upload-toggle summary.upload-fab:hover { background: var(--lumen-deep); }
        .upload-toggle summary.upload-fab::-webkit-details-marker { display: none; }
        .upload-toggle summary.upload-fab::before { content: "+"; }
        .upload-toggle[open] summary.upload-fab::before { content: "\2212"; }

        .upload-form {
            margin-top: 1rem; padding-top: 1rem; border-top: 1px solid var(--rule);
            display: flex; flex-wrap: wrap; align-items: center; gap: 0.6rem;
        }

        .upload-form input[type="file"] { font-family: var(--font-body); font-size: 0.85rem; color: var(--ink); max-width: 100%; }

        .hint { width: 100%; margin: 0.3rem 0 0; font-family: var(--font-mono); font-size: 0.7rem; color: var(--ink-muted); }
        .upload-status { width: 100%; margin: 0.4rem 0 0; font-family: var(--font-mono); font-size: 0.75rem; min-height: 1em; }

        .card--ask { border-top: 3px solid var(--lumen); }

        .ask-form { display: flex; flex-direction: column; gap: 0.75rem; }

        textarea {
            font-family: var(--font-body); font-size: 1rem; padding: 0.85rem;
            border: 1px solid rgba(33, 29, 24, .18); border-radius: 3px;
            background: #fff; color: var(--ink); resize: vertical; min-height: 4.5rem;
        }

        .status-line { font-family: var(--font-mono); font-size: 0.8rem; color: var(--ink); min-height: 1.2em; margin: 0.9rem 0 0; }
        .status-line.is-error { color: var(--danger); }

        .card--answer {
            background: var(--ink); color: var(--bg); border: none;
            box-shadow: 0 25px 50px -28px rgba(33, 29, 24, .6);
        }

        .card--answer .answer__text { font-size: 1.02rem; line-height: 1.6; margin: 0; color: var(--bg); }

        .sources__label { font-family: var(--font-mono); font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.08em; color: var(--ink-muted); margin: 0 0 0.75rem; }
        .sources-grid__items { display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 0.9rem; }

        .source {
            background: transparent; border: 1px solid rgba(33, 29, 24, .15); border-left: 3px solid transparent;
            border-radius: 10px; padding: 0.75rem 0.9rem;
            opacity: 0; transform: translateY(8px);
            transition: opacity .45s ease, transform .45s ease, border-color .45s ease, background .45s ease;
        }

        .source--lit {
            opacity: 1; transform: translateY(0); border-left-color: var(--lumen);
            background: rgba(255, 255, 255, .45);
            backdrop-filter: blur(10px); -webkit-backdrop-filter: blur(10px);
            box-shadow: 0 15px 30px -22px rgba(33, 29, 24, .3);
        }

        .source__excerpt { font-size: 0.88rem; line-height: 1.55; color: var(--ink); margin: 0 0 0.3rem; }
        .source__file { font-family: var(--font-mono); font-size: 0.7rem; color: var(--ink-muted); margin: 0; }

        @media (max-width: 480px) {
            .card { padding: 1.25rem; }
            .card--doc { padding: 1.25rem 1.5rem; }
            .btn--invert { align-self: stretch; }
        }
    </style>
</head>
<body>

    <header class="header">
        <a href="{{ url('/') }}" class="back-link">&larr; Back to overview</a>
        <div class="wordmark" style="font-size:1.4rem;">Lumen</div>
    </header>

    <main class="stage">
        <div class="card card--doc">
            <div class="active-doc">
                <span class="active-doc__label">Active document</span>
                <span class="active-doc__name" id="activeDocName">Aurion Dynamics <em>(sample)</em></span>
            </div>

            <details class="upload-toggle">
                <summary class="upload-fab" aria-label="Upload another document"></summary>
                <form id="uploadForm" class="upload-form">
                    <input type="file" name="file" id="fileInput" accept=".pdf,.txt" required>
                    <button type="submit" class="btn btn--ghost">Index document</button>
                    <p class="hint">PDF or text, 2MB max.</p>
                    <p class="upload-status" id="uploadStatus" role="status"></p>
                </form>
            </details>
        </div>

        <div class="card card--ask">
            <form id="askForm" class="ask-form">
                <label for="question" class="sr-only">Question</label>
                <textarea id="question" name="question" rows="2" placeholder="What do you want to ask?" required maxlength="2000"></textarea>
                <button type="submit" class="btn btn--invert" id="askBtn">Ask &rarr;</button>
            </form>
            <p class="status-line" id="statusLine" role="status" aria-live="polite"></p>
        </div>

        <div id="answer" hidden>
            <div class="card card--answer">
                <p class="answer__text" id="answerText"></p>
            </div>
            <div id="sourcesBlock" style="margin-top: 1.25rem;">
                <p class="sources__label">From the document:</p>
                <div class="sources-grid__items" id="sources"></div>
            </div>
        </div>
    </main>

    <footer class="ledger">
        <span>MODEL gemini-flash-latest</span>
        <span>CHUNK 500 / OVERLAP 100</span>
        <span>TOP-K 3</span>
        <span>EMBED 768D</span>
        <a href="https://github.com/kearakha/lumen-rag" target="_blank" rel="noopener">code on GitHub &rarr;</a>
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

                uploadStatus.textContent = 'Indexing document…';
                uploadStatus.classList.remove('is-error');

                const body = new FormData();
                body.append('file', file);

                try {
                    const res = await fetch('/api/documents', { method: 'POST', body, headers: { Accept: 'application/json' } });
                    const data = await res.json();

                    if (!res.ok) {
                        uploadStatus.textContent = data.message || 'Upload failed.';
                        uploadStatus.classList.add('is-error');
                        return;
                    }

                    activeDocId = data.document_id;
                    activeDocName.innerHTML = escapeHtml(data.filename) + ' <em>(uploaded)</em>';
                    uploadStatus.textContent = data.chunks + ' chunks indexed. Ready to ask.';
                    uploadStatus.classList.remove('is-error');
                } catch {
                    uploadStatus.textContent = 'Lumen is unreachable right now. Try again shortly.';
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
                statusLine.textContent = 'Sending question…';

                try {
                    const res = await fetch('/api/ask', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
                        body: JSON.stringify({ question, document_id: activeDocId }),
                    });
                    const data = await res.json();

                    if (!res.ok) {
                        statusLine.textContent = data.message || 'Question rejected.';
                        statusLine.classList.add('is-error');
                        askBtn.disabled = false;
                        return;
                    }

                    listen(data.ask_id);
                } catch {
                    statusLine.textContent = 'Lumen is unreachable right now. Try again shortly.';
                    statusLine.classList.add('is-error');
                    askBtn.disabled = false;
                }
            });

            function listen(askId) {
                const es = new EventSource('/api/ask/' + askId + '/stream');

                es.addEventListener('status', (e) => {
                    const payload = JSON.parse(e.data);
                    statusLine.textContent = payload.status === 'processing'
                        ? 'Reading document…'
                        : 'Waiting in queue…';
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
