# Lumen

RAG document-QA — upload dokumen, tanya isinya, dapat jawaban yang mengutip sumbernya. Dibangun full di **Laravel + PostgreSQL/pgvector**, tanpa framework RAG (LangChain dkk) — chunking, embedding, retrieval, dan prompt semuanya ditulis manual supaya tiap langkah bisa dijelaskan.

**Demo publik:** http://152.42.239.1 — sudah ada dokumen contoh ("Aurion Dynamics", perusahaan fiktif) yang bisa langsung ditanya tanpa upload dulu.

Proyek ini flagship portfolio, bukan produk komersial. Tujuannya membuktikan satu rantai skill utuh: panggil LLM dari backend, RAG, queue+retry, eval terukur, sampai deploy publik — bukan cuma prototipe di notebook.

## Cara pakai

Upload dokumen:

```bash
curl -X POST http://152.42.239.1/api/documents \
  -F "file=@dokumen-kamu.pdf"
```

Tanya (async — balikin `ask_id` langsung, jawaban diproses di background):

```bash
curl -X POST http://152.42.239.1/api/ask \
  -H "Content-Type: application/json" \
  -d '{"question": "isi pertanyaan", "document_id": 1}'
```

Ambil jawaban lewat SSE stream (`event: done` berisi jawaban + kutipan chunk sumber):

```bash
curl -N http://152.42.239.1/api/ask/{ask_id}/stream
```

Dokumen contoh yang sudah ter-seed punya `document_id=1`.

## Arsitektur

```
Upload dokumen → ekstrak teks → chunk (500 char, overlap 100)
              → embed (Gemini gemini-embedding-001, 768 dim) → simpan pgvector

Pertanyaan → embed pertanyaan → cari 3 chunk termirip (cosine distance,
           discope ke document_id yang diminta) → susun prompt →
           panggil LLM lewat queue job → hasil dipoll via SSE
```

Semua panggilan ke Gemini (embedding maupun chat) lewat job antrian (`database` queue driver), retry otomatis 3x dengan backoff 5s/15s/30s kalau gagal — kecuali error kuota harian, yang langsung gagal dengan pesan jujur (backoff tidak akan menolong kuota yang baru reset besok).

## Keputusan teknis & alasannya

- **Provider LLM: Gemini, bukan OpenAI/Anthropic.** OpenAI menolak API key tanpa billing aktif (`insufficient_quota`); Gemini punya free tier yang cukup untuk proyek portfolio.
- **pgvector, bukan vector DB terpisah (Pinecone/Qdrant).** Satu database untuk data relasional dan vector — lebih sedikit moving part untuk skala proyek ini, dan `pgvector/pgvector-php` menyediakan migration helper + query nearest-neighbor lewat Eloquent langsung.
- **SSE = polling status, bukan streaming token asli dari LLM.** Job jalan async di background (biar bisa di-retry dan tidak mem-block request); SSE "menonton" status record di DB tiap 0.5 detik, bukan mem-forward token per-token dari Gemini. Trade-off yang disadari: UX-nya bukan token-by-token, tapi arsitekturnya tetap kompatibel dengan retry dan tidak butuh infrastruktur tambahan (Redis pub/sub, dll).
- **Chunk size 500 / overlap 100** (bukan 1000/200) — hasil dari eval M3, lihat bagian di bawah.
- **Retrieval discope per `document_id`.** Tanpa ini, pertanyaan tentang satu dokumen bisa terjawab pakai potongan dokumen lain begitu database punya lebih dari satu dokumen — jadi wajib sebelum dipakai publik oleh banyak orang.
- **Batas upload 2MB.** Upload diproses sinkron dalam satu request HTTP (ekstrak → chunk → embed sekaligus) — file besar berisiko timeout.
- **Deploy: DigitalOcean Droplet manual** (Nginx + PHP-FPM + Postgres + Supervisor), bukan platform-as-a-service — dipilih karena kontrol penuh atas pgvector dan proses queue worker, dengan konsekuensi setup infra dikerjakan manual.

## Hasil evaluasi

20 pertanyaan (16 dari isi dokumen uji, 4 sengaja di luar dokumen untuk uji halusinasi), dinilai manual benar/salah/halusinasi.

| | Chunk 1000/overlap 200 (baseline) | Chunk 500/overlap 100 (setelah) |
|---|---|---|
| Benar | 19/20 | 19/20 |
| Halusinasi | 0/20 | 0/20 |
| Gagal-retrieve | #10 (tujuan Proyek Camar Fase 2) | #6 (nama maskot) |

Memperkecil chunk size membuat tiap chunk lebih fokus satu topik (baik untuk soal #10), tapi karena `top-k` retrieval (3) tidak ikut disesuaikan, cakupan corpus yang ter-cover top-3 menyempit — dari 60% (5 chunk) jadi 33% (9 chunk). Hasilnya: masalah retrieval-miss **berpindah** ke soal lain, bukan hilang. Chunk size dan top-k adalah satu paket parameter, tidak bisa diubah sendiri-sendiri tanpa dampak ke yang lain.

Detail lengkap tiap soal: `.docs/eval/results-baseline.md` dan `.docs/eval/results-after-chunksize.md`.

## Keterbatasan jujur

- **Retrieval bisa meleset** kalau top-3 chunk termirip secara embedding ternyata bukan chunk yang benar-benar menjawab pertanyaan (lihat hasil eval di atas). Sistem tidak berhalusinasi dalam kasus ini — dia jawab "tidak tahu" — tapi jawabannya salah karena informasinya "ketinggalan" di luar top-3.
- **Tidak ada streaming token asli** — jawaban baru muncul utuh setelah LLM selesai memproses, SSE cuma menunjukkan status pending/processing di antaranya.
- **Kuota Gemini free tier: 20 request/hari** per model — bisa kehabisan kalau dipakai testing berat dalam satu hari.
- **Upload maksimum 2MB**, diproses sinkron — dokumen besar bisa gagal karena timeout.
- **Tidak ada autentikasi** — endpoint publik terbuka, cocok untuk demo, bukan multi-tenant produksi sungguhan.
- **Belum ada UI web** — semua interaksi lewat API (`curl`/Postman).

## Stack

Laravel 13 · PHP 8.3 · PostgreSQL 17 + pgvector · Gemini API (`gemini-embedding-001`, `gemini-flash-latest`) · queue `database` driver · SSE · Nginx + PHP-FPM + Supervisor (DigitalOcean Droplet).

Detail lebih lanjut di `.docs/STACK.md`, `.docs/DESIGN.md`, `.docs/COMPONENTS.md`. Progres tiap milestone dicatat di `STATE.md`.
