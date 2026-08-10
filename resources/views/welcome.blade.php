<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Lumen — Ask your documents</title>
    <meta name="description" content="Upload a document, ask it questions. Lumen shows you the exact passage that answers you — not just an answer.">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:ital,opsz,wght@0,9..144,400;0,9..144,500;0,9..144,600;1,9..144,500&family=IBM+Plex+Mono:wght@400;500&family=IBM+Plex+Sans:wght@400;500;600&display=swap" rel="stylesheet">
    @include('partials.styles')
    <style>
        .hero {
            max-width: 1120px; width: 100%; margin: 0 auto;
            padding: 3.5rem 1.5rem 3rem;
            display: flex; flex-direction: column; gap: 1.4rem;
        }

        .hero__eyebrow {
            font-family: var(--font-mono); font-size: 0.72rem; letter-spacing: 0.1em; text-transform: uppercase;
            color: var(--lumen-deep);
            animation: fadeInUp .6s ease both;
        }

        .hero__title {
            font-family: var(--font-display); font-style: italic; font-weight: 500;
            font-size: clamp(2.3rem, 5.2vw, 3.6rem); line-height: 1.08;
            color: var(--ink); margin: 0; max-width: 820px;
            animation: fadeInUp .6s ease both; animation-delay: .08s;
        }

        .hero__body {
            font-size: 1.08rem; line-height: 1.65; max-width: 620px; margin: 0;
            animation: fadeInUp .6s ease both; animation-delay: .16s;
        }

        .hero__meta {
            display: flex; gap: 0.9rem; flex-wrap: wrap; align-items: center; margin-top: 0.4rem;
            animation: fadeInUp .6s ease both; animation-delay: .24s;
        }

        .live-dot-row { display: flex; align-items: center; gap: 0.45rem; font-family: var(--font-mono); font-size: 0.72rem; color: var(--ink-muted); }
        .live-dot { width: 7px; height: 7px; border-radius: 50%; background: var(--ok); display: inline-block; animation: pulseDot 2s ease-in-out infinite; }

        .stage {
            max-width: 640px; width: 100%; margin: 0 auto;
            padding: 0 1.5rem 3.5rem;
        }

        .stage .card { animation-delay: .3s; }

        .ask-placeholder-label { margin: 0 0 0.85rem; text-transform: uppercase; letter-spacing: 0.08em; font-family: var(--font-mono); font-size: 0.7rem; color: var(--ink-muted); }

        .ask-placeholder {
            font-family: var(--font-body); font-size: 1rem; padding: 0.85rem;
            border: 1px solid rgba(33, 29, 24, .15); border-radius: 3px;
            background: #fff; color: var(--ink-muted); min-height: 4.5rem;
            cursor: default;
        }

        .type-cursor {
            display: inline-block;
            margin-left: 1px;
            animation: blink 1s step-end infinite;
        }

        .ask-row { display: flex; margin-top: 1rem; }

        section.section { max-width: 1120px; width: 100%; margin: 0 auto; padding: 1rem 1.5rem 3.5rem; }

        .section__eyebrow { font-family: var(--font-mono); font-size: 0.72rem; text-transform: uppercase; letter-spacing: 0.1em; color: var(--lumen-deep); margin: 0 0 1.5rem; }
        .section__title { font-family: var(--font-display); font-style: italic; font-weight: 500; font-size: 1.7rem; color: var(--ink); margin: 0 0 0.4rem; }
        .section__lead { max-width: 620px; margin: 0 0 1.5rem; line-height: 1.6; }

        .pipeline { display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 1.5rem 1.1rem; }
        .pipeline__step { border-top: 2px solid var(--lumen-deep); padding-top: 0.7rem; transition: transform .2s ease; cursor: default; }
        .pipeline__step:hover { transform: translateY(-3px); }
        .pipeline__index { font-family: var(--font-mono); font-size: 0.68rem; color: var(--lumen-deep); margin-bottom: 0.35rem; }
        .pipeline__label { font-family: var(--font-display); font-weight: 500; font-size: 1.05rem; color: var(--ink); margin-bottom: 0.25rem; }
        .pipeline__detail { font-family: var(--font-mono); font-size: 0.68rem; color: var(--ink-muted); line-height: 1.4; }

        .decisions { display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 1.1rem; }
        .decision {
            background: rgba(255, 255, 255, .55); border: 1px solid rgba(255, 255, 255, .8);
            border-radius: 10px; padding: 1.25rem 1.4rem;
            transition: transform .2s ease, box-shadow .2s ease;
        }
        .decision:hover { transform: translateY(-3px); box-shadow: 0 15px 30px -20px rgba(33, 29, 24, .35); }
        .decision__title { font-family: var(--font-body); font-weight: 600; font-size: 0.95rem; color: var(--ink); margin-bottom: 0.5rem; }
        .decision__body { font-size: 0.88rem; line-height: 1.55; }

        .eval-table {
            display: grid; grid-template-columns: 1.4fr 1fr 1fr; max-width: 640px;
            border: 1px solid var(--rule); border-radius: 10px; overflow: hidden;
            font-family: var(--font-mono); font-size: 0.82rem;
        }
        .eval-table__head { padding: 0.75rem 1rem; background: rgba(33, 29, 24, .06); font-weight: 500; color: var(--ink); }
        .eval-table__cell { padding: 0.75rem 1rem; border-top: 1px solid var(--rule); color: var(--ink-muted); }
        .eval-table__cell--val { color: var(--ink); }
        .eval-note { max-width: 640px; margin: 1.1rem 0 0; line-height: 1.6; font-size: 0.92rem; }

        .limitations { display: flex; flex-direction: column; gap: 0.7rem; max-width: 720px; }
        .limitation {
            display: flex; gap: 0.8rem; align-items: baseline; padding: 0.7rem 0;
            border-bottom: 1px solid rgba(201, 192, 172, .6);
            transition: padding-left .2s ease;
        }
        .limitation:hover { padding-left: 0.4rem; }
        .limitation__index { font-family: var(--font-mono); font-size: 0.7rem; color: var(--danger); min-width: 1.4rem; }
        .limitation__text { font-size: 0.92rem; line-height: 1.55; }

        @media (max-width: 480px) {
            .card { padding: 1.25rem; }
        }
    </style>
</head>
<body>

    <header class="header">
        <div class="wordmark">Lumen</div>
        <nav class="nav">
            <a href="{{ url('/app') }}">Try it</a>
            <a href="#decisions">Decisions</a>
            <a href="#limitations">Limitations</a>
        </nav>
    </header>

    <section class="hero">
        <span class="hero__eyebrow">AI Application Engineer Portfolio &middot; Laravel + pgvector</span>
        <h1 class="hero__title">Ask a question. Lumen reads your document, finds the passage that actually answers it, and shows you that passage &mdash; not just an answer.</h1>
        <p class="hero__body">Built end-to-end in Laravel: LLM calls from the backend, retrieval-augmented generation over pgvector, queued retries, a measured evaluation, and a public deploy. No RAG framework &mdash; every step is hand-written so it can be explained on the spot.</p>
        <div class="hero__meta">
            <span class="live-dot-row"><span class="live-dot"></span>Public demo &middot; no signup</span>
        </div>
    </section>

    <div class="stage">
        <div class="card">
            <div class="ask-placeholder-label">Ask the sample document</div>
            <div class="ask-placeholder" id="askPlaceholder" aria-hidden="true">
                <span id="askPlaceholderText"></span><span class="type-cursor">|</span>
            </div>
            <div class="ask-row">
                <a href="{{ url('/app') }}" class="btn btn--invert btn--cta">Ask now &rarr;</a>
            </div>
        </div>
    </div>

    <section class="section">
        <h2 class="section__eyebrow">How it works</h2>
        <div class="pipeline">
            <div class="pipeline__step"><div class="pipeline__index">01</div><div class="pipeline__label">Upload</div><div class="pipeline__detail">PDF or text, &le;2MB</div></div>
            <div class="pipeline__step"><div class="pipeline__index">02</div><div class="pipeline__label">Chunk</div><div class="pipeline__detail">500 char / 100 overlap</div></div>
            <div class="pipeline__step"><div class="pipeline__index">03</div><div class="pipeline__label">Embed</div><div class="pipeline__detail">gemini-embedding-001, 768d</div></div>
            <div class="pipeline__step"><div class="pipeline__index">04</div><div class="pipeline__label">Store</div><div class="pipeline__detail">pgvector, Postgres</div></div>
            <div class="pipeline__step"><div class="pipeline__index">05</div><div class="pipeline__label">Retrieve</div><div class="pipeline__detail">top-3, cosine, scoped</div></div>
            <div class="pipeline__step"><div class="pipeline__index">06</div><div class="pipeline__label">Answer</div><div class="pipeline__detail">queued job + SSE poll</div></div>
        </div>
    </section>

    <section id="decisions" class="section">
        <h2 class="section__title">Technical decisions</h2>
        <p class="section__lead">Every choice below trades something away on purpose. Ask about any of them &mdash; that's the point of shipping this instead of a tutorial clone.</p>
        <div class="decisions">
            <div class="decision"><div class="decision__title">Gemini, not OpenAI/Anthropic</div><div class="decision__body">OpenAI rejects an API key without active billing. Gemini has a free tier that covers a portfolio project.</div></div>
            <div class="decision"><div class="decision__title">pgvector, not a separate vector DB</div><div class="decision__body">One database for relational and vector data &mdash; fewer moving parts at this scale, with Eloquent-native nearest-neighbor queries.</div></div>
            <div class="decision"><div class="decision__title">SSE polling, not token streaming</div><div class="decision__body">The job runs async so it can retry safely. SSE watches a DB status row every 0.5s rather than forwarding tokens &mdash; answers arrive whole, not word-by-word.</div></div>
            <div class="decision"><div class="decision__title">Chunk 500 / overlap 100</div><div class="decision__body">Chosen from an eval run, not a guess &mdash; smaller chunks fixed one retrieval miss but shifted the risk elsewhere (see results below).</div></div>
            <div class="decision"><div class="decision__title">Retrieval scoped per document</div><div class="decision__body">Without this, a question about one document could be answered with another document&rsquo;s chunks once the corpus grows past one file.</div></div>
        </div>
    </section>

    <section class="section">
        <h2 class="section__title">Measured, not assumed</h2>
        <p class="section__lead">20 questions, 16 drawn from the seeded document, 4 deliberately out-of-scope to test hallucination. Scored by hand: correct / hallucinated / retrieval-miss.</p>
        <div class="eval-table">
            <div class="eval-table__head"></div>
            <div class="eval-table__head">Chunk 1000/200</div>
            <div class="eval-table__head">Chunk 500/100</div>
            <div class="eval-table__cell">Correct</div>
            <div class="eval-table__cell eval-table__cell--val">19/20</div>
            <div class="eval-table__cell eval-table__cell--val">19/20</div>
            <div class="eval-table__cell">Hallucinated</div>
            <div class="eval-table__cell eval-table__cell--val">0/20</div>
            <div class="eval-table__cell eval-table__cell--val">0/20</div>
            <div class="eval-table__cell">Retrieval-miss</div>
            <div class="eval-table__cell eval-table__cell--val">Q10</div>
            <div class="eval-table__cell eval-table__cell--val">Q6</div>
        </div>
        <p class="eval-note">Smaller chunks sharpened retrieval for some questions but narrowed corpus coverage for the fixed top-3 &mdash; the miss moved to a different question rather than disappearing. Chunk size and top-k are one parameter set, not two independent knobs.</p>
    </section>

    <section id="limitations" class="section">
        <h2 class="section__title">Honest limitations</h2>
        <p class="section__lead">Stated up front, not discovered by the reader.</p>
        <div class="limitations">
            <div class="limitation"><span class="limitation__index">01</span><span class="limitation__text">Retrieval can miss: if the true top-3 chunks by embedding similarity aren&rsquo;t the ones that answer the question, Lumen says "I don&rsquo;t know" rather than guessing &mdash; but the answer is incomplete.</span></div>
            <div class="limitation"><span class="limitation__index">02</span><span class="limitation__text">No real token streaming &mdash; the answer appears whole once the LLM finishes; SSE only reports pending/processing in between.</span></div>
            <div class="limitation"><span class="limitation__index">03</span><span class="limitation__text">Gemini free tier caps at 20 requests/day per model &mdash; heavy testing in one day can exhaust it.</span></div>
            <div class="limitation"><span class="limitation__index">04</span><span class="limitation__text">Upload is capped at 2MB and processed synchronously &mdash; larger documents risk a timeout.</span></div>
            <div class="limitation"><span class="limitation__index">05</span><span class="limitation__text">No authentication. The endpoints are public by design for a demo, not for real multi-tenant production use.</span></div>
        </div>
    </section>

    <footer class="ledger">
        <span>MODEL gemini-flash-latest</span>
        <span>CHUNK 500 / OVERLAP 100</span>
        <span>TOP-K 3</span>
        <span>EMBED 768D</span>
        <a href="https://github.com/kearakha/lumen-rag" target="_blank" rel="noopener">code on GitHub &rarr;</a>
    </footer>

    <script>
        (function () {
            const el = document.getElementById('askPlaceholderText');
            const text = 'What do you want to ask?';
            const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

            if (reduceMotion) {
                el.textContent = text;
                return;
            }

            const TYPE_MS = 45;
            const DELETE_MS = 30;
            const HOLD_FULL_MS = 1800;
            const HOLD_EMPTY_MS = 500;

            let i = 0;
            (function typeLoop() {
                i++;
                el.textContent = text.slice(0, i);
                if (i < text.length) {
                    setTimeout(typeLoop, TYPE_MS);
                } else {
                    setTimeout(deleteLoop, HOLD_FULL_MS);
                }
            })();

            function deleteLoop() {
                i--;
                el.textContent = text.slice(0, i);
                if (i > 0) {
                    setTimeout(deleteLoop, DELETE_MS);
                } else {
                    setTimeout(typeLoop, HOLD_EMPTY_MS);
                }
            }
        })();
    </script>
</body>
</html>
