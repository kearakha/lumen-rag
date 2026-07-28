# STATE.md — Lumen

> Diupdate setelah tiap milestone. Format tetap. Entri terbaru di atas.
> Rolling: simpan 10 entri terakhir, sisanya arsip di bawah garis `--- ARSIP ---`.

## Milestone aktif
**M3 — Evaluasi** (in progress — terhambat kuota harian Gemini, lanjut besok)

## Success criteria (dari PRD, yang harus bisa diverifikasi sendiri)
- [ ] Lumen menjawab dari isi dokumen upload (bukti: fakta unik).
- [ ] Tabel eval sebelum-vs-sesudah dengan angka konkret.
- [ ] Live di URL publik, dipakai orang lain tanpa penjelasan.

---

## Log

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

<!--
Template entri berikutnya (copy saat mulai milestone baru):

### [tanggal] — M[n]: [nama milestone]
- **Passed:** (apa yang beres + cara verifikasinya)
- **Failed / belum:** (apa yang gagal / tertunda)
- **Rule worth remembering:** (gotcha / pelajaran untuk sesi berikutnya)
- **Parkir:** (ide di luar milestone ini — jangan dikerjakan, cukup dicatat)
-->

--- ARSIP ---
