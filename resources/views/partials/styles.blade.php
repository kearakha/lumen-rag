<style>
    :root {
        --bg: #fafaf7;
        --ink: #211d18;
        --ink-muted: #6b6459;
        --rule: #c9c0ac;
        --lumen: #f4b942;
        --lumen-deep: #c98a1f;
        --danger: #a6432e;
        --ok: #4a7a4a;
        --font-display: 'Fraunces', serif;
        --font-body: 'IBM Plex Sans', sans-serif;
        --font-mono: 'IBM Plex Mono', monospace;
    }

    * { box-sizing: border-box; }

    html, body { margin: 0; padding: 0; min-height: 100%; }

    body {
        background:
            radial-gradient(circle at 15% 0%, rgba(244, 185, 66, .38), transparent 60%),
            radial-gradient(circle at 85% 8%, rgba(244, 185, 66, .28), transparent 55%),
            var(--bg);
        color: var(--ink-muted);
        font-family: var(--font-body);
        display: flex;
        flex-direction: column;
        min-height: 100vh;
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

    [hidden] { display: none !important; }

    .header {
        max-width: 1120px; width: 100%; margin: 0 auto;
        padding: 2rem 1.5rem 0;
        display: flex; justify-content: space-between; align-items: center;
        gap: 1rem; flex-wrap: wrap;
    }

    .wordmark {
        font-family: var(--font-display);
        font-style: italic;
        font-weight: 500;
        font-size: 1.6rem;
        color: var(--ink);
        letter-spacing: 0.01em;
        text-decoration: none;
    }

    .nav { display: flex; gap: 1.6rem; align-items: center; font-family: var(--font-mono); font-size: 0.7rem; letter-spacing: 0.06em; text-transform: uppercase; }
    .nav a { color: var(--ink-muted); text-decoration: none; }
    .nav a:hover { color: var(--ink); }

    .back-link { font-family: var(--font-mono); font-size: 0.72rem; letter-spacing: 0.06em; text-transform: uppercase; color: var(--ink-muted); text-decoration: none; }
    .back-link:hover { color: var(--ink); }

    @keyframes pulseDot { 0%, 100% { opacity: 1; } 50% { opacity: .35; } }
    @keyframes fadeInUp { from { opacity: 0; transform: translateY(14px); } to { opacity: 1; transform: translateY(0); } }
    @keyframes blink { 50% { opacity: 0; } }
    ::selection { background: var(--lumen); color: var(--ink); }

    .card {
        background: rgba(255, 255, 255, .55);
        backdrop-filter: blur(20px);
        -webkit-backdrop-filter: blur(20px);
        border: 1px solid rgba(255, 255, 255, .8);
        color: var(--ink);
        border-radius: 12px;
        padding: 1.75rem;
        box-shadow: 0 25px 50px -25px rgba(33, 29, 24, .25);
        animation: fadeInUp .6s ease both;
    }

    .btn {
        font-family: var(--font-mono); font-size: 0.8rem; letter-spacing: 0.03em;
        border: none; border-radius: 3px; padding: 0.7rem 1.1rem; cursor: pointer;
        text-decoration: none; display: inline-block;
        transition: transform .18s ease, background .18s ease, color .18s ease;
    }

    .btn--invert { background: var(--ink); color: var(--lumen); align-self: flex-end; }
    .btn--invert:hover { background: #3a3226; color: var(--lumen); transform: translateY(-2px); }
    .btn--invert:disabled { opacity: 0.5; cursor: wait; transform: none; }

    .btn--ghost { background: transparent; border: 1px solid var(--ink-muted); color: var(--ink-muted); }
    .btn--ghost:hover { background: rgba(0, 0, 0, .05); }

    .btn--cta { margin-left: auto; padding: 0.85rem 1.5rem; }

    .ledger {
        display: flex; flex-wrap: wrap; gap: 0.5rem 0.6rem; justify-content: center;
        padding: 1.5rem 1rem 2.5rem;
    }

    .ledger span, .ledger a {
        font-family: var(--font-mono); font-size: 0.68rem; letter-spacing: 0.03em; color: var(--ink-muted);
        background: rgba(255, 255, 255, .5);
        backdrop-filter: blur(10px); -webkit-backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, .8);
        border-radius: 999px; padding: 0.35rem 0.75rem; text-decoration: none;
    }

    .ledger a:hover { color: var(--ink); background: rgba(255, 255, 255, .75); }

    @media (prefers-reduced-motion: reduce) {
        * { animation-duration: .001ms !important; animation-iteration-count: 1 !important; transition-duration: .001ms !important; }
    }
</style>
