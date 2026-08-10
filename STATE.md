# STATE.md — Lumen

> Diupdate setelah tiap milestone. Format tetap. Entri terbaru di atas.
> Rolling: simpan 10 entri terakhir, sisanya arsip di bawah garis `--- ARSIP ---`.

## Milestone aktif
**M5 — Konten & lamaran** (belum mulai)

## Success criteria (dari PRD, yang harus bisa diverifikasi sendiri)
- [x] Lumen menjawab dari isi dokumen upload (bukti: fakta unik).
- [x] Tabel eval sebelum-vs-sesudah dengan angka konkret.
- [x] Live di URL publik (`http://152.42.239.1`) — endpoint API jalan, dan sejak redesign UI (Agustus 2026) landing page + halaman `/app` juga live, bukan lagi welcome page default Laravel.

---

## Log

### 2026-08-10 — UI: redesign landing + halaman ask (branch `redesign-glass-ui`, ditutup)
- **Passed:**
  - Gap yang dicatat di penutupan M4 ("halaman `/` masih default Laravel welcome page") ditutup: `/` sekarang marketing page (hero, pipeline, keputusan teknis, hasil eval, keterbatasan), `/app` route terpisah untuk fitur upload+ask yang sebenarnya.
  - Tema diganti dari "ruangan gelap + kartu kertas" (kesan generic/AI-template) ke glassmorphism kuning-putih (metafora lampu, sesuai nama "Lumen") — dipicu temuan `design-critique` yang juga menemukan 2 bug lain (kutipan sumber kepotong tengah kata, upload toggle tidak keliatan interaktif), keduanya ikut diperbaiki.
  - Halaman `/app` dirombak jadi layout dua kolom (percakapan kiri, rail dokumen & sumber sticky kanan), jawaban LLM di-render sebagai markdown (bold, bullet), dan sitasi `[n]` di jawaban bisa di-hover/klik untuk menyalakan kartu sumber terkait.
  - Prompt (`PromptBuilder`) diubah agar LLM diminta eksplisit menyertakan penanda `[n]` saat mengutip konteks bernomor — sebelumnya konteks sudah bernomor tapi model tidak pernah diminta memakainya, jadi UI sitasi di atas tidak ada yang bisa ditautkan.
  - Sudah merge ke `main` lewat PR #5.
- **Failed / belum:** —
- **Rule worth remembering:**
  - "API jalan" dan "UI menunjukkan itu jalan" adalah dua hal terpisah yang gampang diverifikasi salah satunya doang — pelajaran ini sudah dicatat di M4 tapi baru benar-benar ditutup sekarang.
  - Fitur sitasi butuh dua sisi yang saling gandeng: prompt yang minta model menandai sumber, DAN UI yang tahu cara menautkan penanda itu. Nambah salah satu tanpa yang lain (mis. UI sitasi tanpa prompt yang minta `[n]`) percuma.
- **Parkir (godaan di luar scope):** —

### 2026-07-18 — M0: Setup & LLM pertama
- **Passed:** Laravel 13.20 scaffold jalan. Endpoint `POST /api/ask` menerima `question`, panggil LLM, balas jawaban. Diverifikasi: `curl` ke `/api/ask` dengan pertanyaan "ibu kota Indonesia?" → jawab "Jakarta".
- **Failed / belum:** —
- **Rule worth remembering:**
  - Provider LLM ganti dari rencana awal (OpenAI/Anthropic) ke **Gemini** — OpenAI butuh billing aktif meski API key valid (`insufficient_quota`), Gemini punya free tier. Lihat `.docs/STACK.md`.
  - Model Gemini pakai alias `gemini-flash-latest`, bukan nama versi spesifik — beberapa versi (`gemini-2.5-flash`) sudah "no longer available to new users" meski masih muncul di list model.
  - Mesin ini punya 2 Postgres: EDB installer PG17 (port 5432, custom build path, tidak bisa dipasangi pgvector) dan Homebrew PG17 (port 5433, dipakai Lumen). Jangan bingung kalau `psql` default connect ke yang salah.
  - Homebrew Postgres pakai `trust` auth untuk koneksi lokal by default — tidak perlu password untuk `DB_USERNAME` di `.env`.
- **Parkir (godaan di luar scope):** —

### 2026-07-18 — M1: RAG minimal
- **Passed:** Upload dokumen (`POST /api/documents`) → ekstrak teks → chunk (1000 char, overlap 200) → embed (Gemini `gemini-embedding-001`, 768 dim) → simpan di pgvector. `POST /api/ask` retrieve top-3 chunk termirip (cosine distance) → prompt → jawaban + sumber chunk yang dipakai. Diverifikasi: upload dokumen berisi fakta unik (nama kode server, nama & ulang tahun maskot) → tanya → jawaban benar mengutip fakta tersebut. Pertanyaan di luar dokumen ("ibu kota Perancis?") dijawab "tidak tahu", bukan mengarang — prompt eksplisit larang menjawab di luar konteks.
- **Failed / belum:** —
- **Rule worth remembering:**
  - Model embedding Gemini juga berganti: `text-embedding-004` sudah tidak tersedia (404), dipakai `gemini-embedding-001` dengan `outputDimensionality: 768` biar cocok sama kolom `vector(768)`. Selalu cek list model aktif dulu (`GET /v1beta/models`) sebelum hardcode nama model Gemini — penamaan model mereka sering berubah.
  - `pgvector/pgvector-php` dipakai untuk kolom `vector` di migration (`$table->vector('embedding', 768)`) dan query nearest-neighbor (`HasNeighbors` trait + `Distance::Cosine`) — menghindari raw SQL manual untuk cosine similarity.
  - Retrieval saat ini tidak ada threshold similarity minimum — kalau database cuma punya 1 dokumen yang tidak relevan, chunk itu tetap keambil sebagai "top-3" meski similarity-nya rendah. Prompt yang melarang mengarang jadi pengaman utama untuk saat ini; threshold/relevance check baru relevan dibahas di M3 (eval).
- **Parkir (godaan di luar scope):** —

### 2026-07-18 — M2: Produksi-grade dasar
- **Passed:**
  - `POST /api/ask` sekarang async: bikin record `Ask` (status pending), lempar `AskJob` ke queue (driver `database`), balikin `ask_id` (202) langsung — bukan nunggu LLM.
  - `AskJob` retry otomatis 3x dengan backoff 5s/15s/30s kalau panggilan LLM gagal (network error, rate limit, dll). Kalau tetap gagal setelah 3x, `Ask` masuk status `failed` dengan pesan ramah — bukan expose error mentah.
  - `GET /api/ask/{askId}/stream` — endpoint SSE yang polling status `Ask` tiap 0.5 detik server-side, kirim event `status` (pending/processing) lalu event `done` (jawaban+sumber) atau `error`. Ada timeout 60 detik biar tidak nge-hang selamanya kalau worker mati.
  - Error handling diverifikasi manual: dokumen kosong/whitespace → pesan jelas (bukan 500). `ask_id` tidak ada → event SSE `error` yang rapi (bukan stack trace — ini sempat bocor di percobaan pertama, sudah diperbaiki dengan ganti route-model-binding jadi manual lookup). API key rusak → 3x retry dengan backoff terverifikasi jalan → `failed` dengan pesan ramah.
- **Failed / belum:** —
- **Rule worth remembering:**
  - Queue job + SSE tidak otomatis nyambung: job jalan async di background, SSE cuma "menonton" status record di DB lewat polling — bukan streaming token asli dari LLM. Trade-off yang disadari, bukan keterbatasan yang kelewatan.
  - **Butuh `php artisan queue:work` jalan terus** biar job kepr proses — kalau lupa nyalain worker, `Ask` bakal stuck di `pending` selamanya. Penting diinget pas testing manual dan nanti pas deploy (M4) — perlu proses worker terpisah dari web server (misal via Supervisor/systemd, atau Laravel Horizon kalau upgrade ke Redis nanti).
  - Route model binding (`Route::get('/ask/{ask}/stream', ...)` dengan `Ask $ask` di controller) otomatis lempar 404 exception mentah kalau record tidak ketemu — untuk endpoint yang responsnya harus konsisten satu format (di sini: SSE event-stream), lookup manual (`Ask::find()`) lebih aman daripada implicit binding.
- **Parkir (godaan di luar scope):**
  - Streaming token asli (real per-token dari Gemini) — didiskusikan tapi sengaja tidak dikerjakan, ditunda kalau UX-nya beneran dibutuhkan nanti.
  - Redis untuk queue — didiskusikan tapi tetap pakai `database` driver biar deploy (M4) lebih simpel.

### 2026-07-18 — M3: Evaluasi (belum selesai, disambung besok)
- **Passed:**
  - Dokumen uji fiktif "Aurion Dynamics" dibuat (`.docs/eval/test-document.txt`, ~20 fakta unik) dan berhasil di-upload+chunk+embed (5 chunk, document_id=1).
  - Dataset 20 pertanyaan (`.docs/eval/dataset.json`) — 16 pertanyaan dari isi dokumen, 4 sengaja di luar dokumen (uji halusinasi).
  - Command baru `php artisan eval:run --out=... --delay=N` — jalanin pipeline RAG langsung (sinkron, skip queue) per pertanyaan, tulis hasil progresif ke tabel markdown untuk digrading manual. Sempat berhasil jalan sampai 10 pertanyaan sebelum kena limit.
- **Failed / belum:**
  - **Kuota harian Gemini free tier abis** sebelum eval selesai. Model `gemini-flash-latest` sekarang resolve ke `gemini-3.5-flash`, dan tier gratisnya cuma **20 request chat/hari** (bukan per-menit — pesan error awal menyesatkan, quotaId sebenarnya `GenerateRequestsPerDayPerProjectPerModel-FreeTier`). Sudah kepakai duluan dari beberapa percobaan uji command.
  - Keputusan: tunggu reset kuota besok (bukan ganti model / aktifkan billing), lanjut `php artisan eval:run --out=.docs/eval/results-baseline.md --delay=20` besok pagi. Dataset & command sudah siap, tinggal jalan.
- **Rule worth remembering:**
  - Model Gemini gratis (`gemini-flash-latest` → `gemini-3.5-flash` saat ini) punya limit **20 request/hari**, jauh lebih ketat dari dugaan awal. Kalau mau eval batch (>20 pertanyaan) di satu sesi, ini jadi constraint keras — pertimbangkan split hari atau upgrade billing kalau butuh volume lebih besar nanti.
  - Pesan error 429 Gemini bisa nunjuk ke `RetryInfo` beberapa detik (kesannya rate-limit per-menit) padahal root cause-nya kuota harian — cek `quotaId` di response, jangan cuma percaya `retryDelay`.
- **Parkir (godaan di luar scope):** —

### 2026-08-05 — M4: Deploy publik (ditutup)
- **Passed:**
  - Provisioning DigitalOcean Droplet (Ubuntu 24.04, Singapore, `152.42.239.1`) via GitHub Student Developer Pack, akses SSH key sudah jalan.
  - Stack server terinstall: PHP 8.3.33 + ekstensi, Composer 2.10.2, PostgreSQL 17.10 (PGDG) + pgvector, Nginx 1.24.0, Node.js v24, Supervisor 4.2.5.
  - App ter-deploy ke `/var/www/lumen` (`composer install --no-dev`), `.env` produksi terisi (DB baru dengan password random, `GEMINI_API_KEY`, `APP_URL=http://152.42.239.1`), `migrate --seed` sukses — dokumen contoh "Aurion Dynamics" (`document_id=1`) siap ditanya.
  - Nginx server block (proxy ke PHP-FPM socket) + Supervisor (`lumen-worker`, auto-restart `queue:work --tries=3`) jalan dan running.
  - **Smoke test end-to-end** di server produksi (bukan simulasi lokal): `POST /api/documents` & `POST /api/ask` validasi jalan (422 rapi untuk input kosong — bukan 500), satu `ask` penuh diverifikasi lewat SSE stream sampai `event: done` — jawaban + sumber chunk keluar benar dari Gemini beneran.
  - README ditulis ulang total (isi default scaffold Laravel dibuang): demo publik, cara pakai via `curl`, arsitektur, keputusan teknis + alasan, tabel eval sebelum-sesudah (dari M3), keterbatasan jujur.
  - Bug ditemukan & diperbaiki saat deploy: `DatabaseSeeder` masih punya `User::factory()->create()` bawaan scaffold Laravel — gagal di server karena `fakerphp/faker` cuma ada di `require-dev`, hilang saat `composer install --no-dev`. Baris dihapus (tidak dipakai fitur Lumen manapun), bukan ditambal dengan pindah dependency ke produksi.
  - Fase 1 — scoping retrieval per dokumen: `Ask` sekarang wajib `document_id`, `AskJob` & `eval:run` filter `Chunk` per dokumen sebelum `nearestNeighbors`. Sebelumnya retrieval selalu lintas semua dokumen di DB — aman selama cuma 1 dokumen uji, tapi bakal salah begitu ada lebih dari satu dokumen (kondisi nyata setelah deploy + seed).
  - Fase 2 — config produksi: `.env.example` diselaraskan ke default produksi (pgsql, debug off). Batas upload diturunkan 10MB→2MB (upload masih sinkron dalam satu request HTTP, file besar berisiko timeout). Ditambah `GeminiQuotaExceededException` — error kuota harian (429 `quotaId` *PerDay*) sekarang langsung gagal dengan pesan jujur, bukan retry 3x backoff yang percuma (kuota baru reset besok, bukan dalam hitungan detik).
  - Fase 3 — seed dokumen contoh: `DocumentSeeder` jalanin dokumen fiktif Aurion Dynamics (dokumen eval M3) lewat pipeline Chunker+Embedder yang sama dengan upload biasa. Tujuannya: deploy publik langsung punya dokumen contoh siap tanya, orang lain tidak perlu upload manual dulu buat coba Lumen.
- **Failed / belum:**
  - **Belum ada domain** — akses masih pakai IP polos (`http://152.42.239.1`), belum HTTPS. Perlu domain (GitHub Student Pack ada jatah gratis Namecheap `.me` — cek sudah diklaim atau belum) baru lanjut HTTPS (Certbot/Let's Encrypt butuh domain, gak bisa untuk IP polos).
  - **Halaman `/` masih default Laravel welcome page** — `routes/web.php` belum diubah, `GET /` masih render `view('welcome')` bawaan scaffold. Ketauan pas Rakha buka `http://152.42.239.1` di browser: yang muncul halaman "Let's get started" Laravel, bukan sesuatu yang menjelaskan Lumen. Ini bentrok langsung sama success criteria M4 di PRD ("kirim link ke teman, mereka pakai tanpa dijelaskan") — API-only + landing page kosong berarti orang awam yang buka link gak akan tahu ini apa atau cara pakainya. Perlu diputuskan: UI minimal (form tanya + upload) atau landing page statis yang jelasin cara pakai `curl`. Belum dikerjakan, nunggu keputusan Rakha.
- **Rule worth remembering:**
  - Retrieval per-dokumen (scoping) itu prasyarat keras sebelum seed dokumen contoh — kalau dibalik urutannya (seed dulu, scoping belakangan), window di mana ada >1 dokumen tanpa scoping bakal kasih jawaban silang-dokumen yang salah tanpa disadari.
  - Kuota Gemini yang habis per hari (bukan per menit — lihat catatan M3) sekarang punya exception dedicated (`GeminiQuotaExceededException`) supaya `AskJob` tidak buang 3x retry+backoff percuma untuk error yang pasti gagal lagi sampai besok.
  - **"Live di URL publik" dan "dipakai tanpa penjelasan" itu dua kriteria beda** — API yang jalan sempurna lewat `curl` tidak otomatis memenuhi kriteria kedua. Baru ketauan pas benar-benar dibuka lewat browser (bukan cuma dites lewat `curl` seperti sepanjang sesi ini), bukan pas smoke test API. Pelajaran: verifikasi "dipakai orang lain" harus lewat cara orang lain benar-benar akan mengakses (browser), bukan cuma lewat cara developer tes (`curl`/API client).
- **Parkir (godaan di luar scope):**
  - HTTPS/SSL — nunggu domain ada dulu, IP polos gak bisa Let's Encrypt.
  - UI web (form upload+tanya) — sempat direncanakan masuk M4 Fase 3 versi awal (Vite+Tailwind), tapi diputuskan skip demi kecepatan; sekarang jadi relevan lagi karena gap di atas. Keputusan final ditunda ke sesi berikutnya.

### 2026-08-03 — M3: Evaluasi (ditutup)
- **Passed:**
  - Baseline eval (chunk size 1000/overlap 200, `.docs/eval/results-baseline.md`) selesai penuh 20/20 pertanyaan setelah kuota reset. Hasil: **19/20 PASS**, 1 FAIL (soal #10, "tujuan Proyek Camar Fase 2" — jawabannya ada di dokumen tapi chunk-nya tidak masuk top-3 retrieval). 4 soal uji halusinasi (17-20) semua lolos abstain dengan benar — **0 halusinasi**.
  - Iterasi perbaikan: `Chunker` diubah dari size 1000/overlap 200 → **size 500/overlap 100** (`app/Services/Chunker.php`), dokumen di-upload ulang (9 chunk vs 5 sebelumnya), eval dijalankan lagi (`.docs/eval/results-after-chunksize.md`).
  - Hasil setelah perubahan: **tetap 19/20 PASS**, tapi soal yang gagal **berpindah** — #10 sekarang benar, tapi #6 ("nama maskot") yang tadinya benar jadi gagal (jawabannya ada di dokumen, chunk-nya tidak masuk top-3 dari 9 chunk). 0 halusinasi tetap terjaga di kedua run.
  - **Temuan utama (bukti eval sebelum-vs-sesudah):** memperkecil chunk size memang bikin tiap chunk lebih fokus satu topik (baik untuk presisi), tapi kalau `top-k` retrieval tidak ikut disesuaikan, cakupan corpus yang ke-cover top-3 justru menyempit (60% dari 5 chunk → 33% dari 9 chunk) — jadi masalah retrieval-miss bergeser ke soal lain, bukan hilang. Chunk size dan top-k itu satu paket, tidak bisa diubah sendiri-sendiri tanpa mikirin yang lain.
- **Failed / belum:** —
- **Rule worth remembering:**
  - Port 3000 dan 8000 di mesin ini sudah dipakai proyek lain (TOP-FIK ews-api via Herd nginx, dan sebuah container Docker) — Lumen pakai **port 8001** buat `artisan serve` mulai sesi ini.
  - Sebelum ubah parameter retrieval (chunk size, top-k, dsb), selalu cek isi chunk aktual dan posisi karakter fakta yang dicari (`mb_strpos` manual) — ini yang mengungkap bahwa soal #10 gagal bukan karena kontennya hilang dari chunk, tapi karena ranking similarity yang meleset. Diagnosis dulu sebelum ubah kode, bukan tebak-tebak ubah parameter.
- **Parkir (godaan di luar scope):**
  - Tuning `top-k` retrieval (naikkan dari 3) atau similarity threshold minimum — relevan buat nutup gap #6 vs #10, tapi di luar scope "satu perubahan" M3. Baru relevan kalau nanti mau eval lanjutan atau pas ada laporan retrieval-miss nyata di produksi.

<!--
Template entri berikutnya (copy saat mulai milestone baru):

### [tanggal] — M[n]: [nama milestone]
- **Passed:** (apa yang beres + cara verifikasinya)
- **Failed / belum:** (apa yang gagal / tertunda)
- **Rule worth remembering:** (gotcha / pelajaran untuk sesi berikutnya)
- **Parkir:** (ide di luar milestone ini — jangan dikerjakan, cukup dicatat)
-->

--- ARSIP ---
