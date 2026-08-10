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
        .stage {
            max-width: 1120px; width: 100%; margin: 2rem auto 0; padding: 0 1.5rem 3rem;
            display: grid; grid-template-columns: minmax(0, 1fr) 360px; gap: 1.5rem; align-items: start;
        }

        .column { display: flex; flex-direction: column; gap: 1.25rem; min-width: 0; }

        .rail { position: sticky; top: 1.5rem; }

        /* ---- active document (rail) ---- */

        .card--doc { position: relative; padding: 1.25rem 1.5rem; }

        .active-doc { display: flex; flex-direction: column; gap: 0.35rem; font-family: var(--font-mono); font-size: 0.7rem; padding-right: 2.5rem; }
        .active-doc__label { text-transform: uppercase; letter-spacing: 0.08em; color: var(--ink-muted); }
        .active-doc__name { font-family: var(--font-body); font-size: 0.95rem; font-weight: 600; }
        .active-doc__name em { font-style: normal; font-weight: 400; color: var(--ink-muted); }

        .upload-toggle summary.upload-fab {
            position: absolute; top: 1.1rem; right: 1.25rem;
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

        .upload-form input[type="file"] { font-family: var(--font-body); font-size: 0.8rem; color: var(--ink); max-width: 100%; }

        .hint { width: 100%; margin: 0.3rem 0 0; font-family: var(--font-mono); font-size: 0.7rem; color: var(--ink-muted); }
        .upload-status { width: 100%; margin: 0.4rem 0 0; font-family: var(--font-mono); font-size: 0.75rem; }
        .upload-status:empty { display: none; }
        .upload-status.is-error { color: var(--danger); }

        /* ---- sources rail ---- */

        .sources__label { font-family: var(--font-mono); font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.08em; color: var(--ink-muted); margin: 0 0 0.75rem; }
        .sources-list { display: flex; flex-direction: column; gap: 0.75rem; max-height: calc(100vh - 16rem); overflow-y: auto; }

        .source {
            display: grid; grid-template-columns: auto minmax(0, 1fr); gap: 0.65rem;
            background: transparent; border: 1px solid rgba(33, 29, 24, .15); border-left: 3px solid transparent;
            border-radius: 10px; padding: 0.75rem 0.9rem; cursor: pointer;
            opacity: 0; transform: translateY(8px);
            transition: opacity .45s ease, transform .45s ease, border-color .45s ease, background .45s ease, box-shadow .25s ease;
        }

        .source--lit {
            opacity: 1; transform: translateY(0); border-left-color: var(--lumen);
            background: rgba(255, 255, 255, .45);
            backdrop-filter: blur(10px); -webkit-backdrop-filter: blur(10px);
            box-shadow: 0 15px 30px -22px rgba(33, 29, 24, .3);
        }

        .source.is-target { border-color: var(--lumen); box-shadow: 0 0 0 3px rgba(244, 185, 66, .35); }

        .source__num {
            font-family: var(--font-mono); font-size: 0.68rem; font-weight: 500;
            width: 1.35rem; height: 1.35rem; border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            background: var(--lumen); color: var(--ink);
        }

        .source__excerpt {
            font-size: 0.85rem; line-height: 1.55; color: var(--ink); margin: 0 0 0.35rem;
            display: -webkit-box; -webkit-box-orient: vertical; -webkit-line-clamp: 4; overflow: hidden;
        }

        .source.is-open .source__excerpt { -webkit-line-clamp: unset; }
        .source__file { font-family: var(--font-mono); font-size: 0.68rem; color: var(--ink-muted); margin: 0; }

        /* ---- thread (left column) ---- */

        .card--question { padding: 1rem 1.25rem; }
        .card--question p { margin: 0.3rem 0 0; font-size: 1rem; color: var(--ink); }
        .thread__label { font-family: var(--font-mono); font-size: 0.68rem; text-transform: uppercase; letter-spacing: 0.08em; color: var(--ink-muted); margin: 0; }

        .card--answer {
            background: var(--ink); color: var(--bg); border: none;
            box-shadow: 0 25px 50px -28px rgba(33, 29, 24, .6);
        }

        .answer__text { font-size: 1rem; line-height: 1.65; color: var(--bg); }
        .answer__text > :first-child { margin-top: 0; }
        .answer__text > :last-child { margin-bottom: 0; }
        .answer__text p { margin: 0 0 0.8rem; }
        .answer__text ul { margin: 0 0 0.8rem; padding-left: 1.1rem; }
        .answer__text li { margin-bottom: 0.4rem; }
        .answer__text strong { color: #fff; font-weight: 600; }

        .cite {
            font-family: var(--font-mono); font-size: 0.62rem; line-height: 1;
            background: var(--lumen); color: var(--ink);
            border-radius: 999px; padding: 0.15rem 0.35rem; margin-left: 0.15rem;
            cursor: pointer; vertical-align: super;
        }

        .cite:hover, .cite.is-active { background: #fff; }

        /* ---- ask bar (sticky bottom) ---- */

        .askbar { position: sticky; bottom: 1rem; border-top: 3px solid var(--lumen); padding: 1.1rem 1.25rem; }
        .ask-form { display: flex; align-items: flex-end; gap: 0.75rem; }

        textarea {
            flex: 1; font-family: var(--font-body); font-size: 1rem; padding: 0.75rem;
            border: 1px solid rgba(33, 29, 24, .18); border-radius: 3px;
            background: #fff; color: var(--ink); resize: vertical; min-height: 3.2rem;
        }

        .btn--invert { align-self: auto; }

        .status-line { font-family: var(--font-mono); font-size: 0.8rem; color: var(--ink); margin: 0.75rem 0 0; }
        .status-line:empty { display: none; }
        .status-line.is-error { color: var(--danger); }

        @media (max-width: 900px) {
            .stage { grid-template-columns: 1fr; }
            .rail { position: static; order: 2; }
            .column--main { order: 1; }
            .sources-list { max-height: none; }
        }

        @media (max-width: 480px) {
            .card { padding: 1.25rem; }
            .ask-form { flex-direction: column; align-items: stretch; }
        }
    </style>
</head>
<body>

    <header class="header">
        <a href="{{ url('/') }}" class="back-link">&larr; Back to overview</a>
        <div class="wordmark" style="font-size:1.4rem;">Lumen</div>
    </header>

    <main class="stage">
        <div class="column column--main">
            <div class="card card--question" id="questionEcho" hidden>
                <p class="thread__label">You asked</p>
                <p id="questionEchoText"></p>
            </div>

            <div class="card card--answer" id="answerCard" hidden>
                <div class="answer__text" id="answerText"></div>
            </div>

            <div class="card askbar">
                <form id="askForm" class="ask-form">
                    <label for="question" class="sr-only">Question</label>
                    <textarea id="question" name="question" rows="2" placeholder="What do you want to ask?" required maxlength="2000"></textarea>
                    <button type="submit" class="btn btn--invert" id="askBtn">Ask &rarr;</button>
                </form>
                <p class="status-line" id="statusLine" role="status" aria-live="polite"></p>
            </div>
        </div>

        <aside class="column rail">
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

            <div id="sourcesBlock" hidden>
                <p class="sources__label">From the document:</p>
                <div class="sources-list" id="sources"></div>
            </div>
        </aside>
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
            const questionEcho = document.getElementById('questionEcho');
            const questionEchoText = document.getElementById('questionEchoText');
            const statusLine = document.getElementById('statusLine');
            const answerCard = document.getElementById('answerCard');
            const answerText = document.getElementById('answerText');
            const sourcesBlock = document.getElementById('sourcesBlock');
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
                answerCard.hidden = true;
                sourcesBlock.hidden = true;
                sourcesEl.innerHTML = '';
                questionEchoText.textContent = question;
                questionEcho.hidden = false;
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
                answerText.innerHTML = renderMarkdown(text);
                answerCard.hidden = false;
                sourcesEl.innerHTML = '';
                sourcesBlock.hidden = sources.length === 0;

                sources.forEach((s, i) => {
                    const div = document.createElement('div');
                    div.className = 'source';
                    div.dataset.n = i + 1;

                    const num = document.createElement('span');
                    num.className = 'source__num';
                    num.textContent = i + 1;

                    const body = document.createElement('div');
                    const excerptP = document.createElement('p');
                    excerptP.className = 'source__excerpt';
                    excerptP.textContent = s.excerpt;
                    const fileP = document.createElement('p');
                    fileP.className = 'source__file';
                    fileP.textContent = s.document;
                    body.appendChild(excerptP);
                    body.appendChild(fileP);

                    div.appendChild(num);
                    div.appendChild(body);
                    div.addEventListener('click', () => div.classList.toggle('is-open'));
                    sourcesEl.appendChild(div);

                    setTimeout(() => div.classList.add('source--lit'), 120 * (i + 1));
                });
            }

            // ponytail: 3 markdown patterns only (bold, bullet, [n]) — no marked.js for 15 lines.
            function renderMarkdown(text) {
                const lines = escapeHtml(text).split('\n');
                let html = '';
                let inList = false;

                for (const raw of lines) {
                    const line = raw.trim();
                    if (!line) continue;

                    const bullet = line.match(/^[*-]\s+(.*)$/);
                    if (bullet) {
                        if (!inList) { html += '<ul>'; inList = true; }
                        html += '<li>' + inlineMarkup(bullet[1]) + '</li>';
                        continue;
                    }

                    if (inList) { html += '</ul>'; inList = false; }
                    html += '<p>' + inlineMarkup(line) + '</p>';
                }

                return inList ? html + '</ul>' : html;
            }

            function inlineMarkup(s) {
                return s
                    .replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>')
                    .replace(/\[(\d+)\]/g, '<sup class="cite" data-n="$1" tabindex="0" role="button" aria-label="Show source $1">$1</sup>');
            }

            answerText.addEventListener('click', (e) => {
                const cite = e.target.closest('.cite');
                if (cite) highlightSource(cite.dataset.n, true);
            });

            answerText.addEventListener('mouseover', (e) => {
                const cite = e.target.closest('.cite');
                if (cite) highlightSource(cite.dataset.n, false);
            });

            answerText.addEventListener('mouseout', (e) => {
                if (e.target.closest('.cite')) clearHighlight();
            });

            function highlightSource(n, scroll) {
                clearHighlight();
                const target = sourcesEl.querySelector('.source[data-n="' + n + '"]');
                if (!target) return;
                target.classList.add('is-target');
                if (scroll) target.scrollIntoView({ block: 'nearest', behavior: 'smooth' });
            }

            function clearHighlight() {
                sourcesEl.querySelectorAll('.source.is-target').forEach((el) => el.classList.remove('is-target'));
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
