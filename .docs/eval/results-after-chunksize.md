| # | Pertanyaan | Dokumen diretrieve | Jawaban Lumen | Verdict (isi manual) |
|---|---|---|---|---|
| 1 | Kapan Aurion Dynamics didirikan dan di kota mana? | test-document.txt | Aurion Dynamics didirikan pada tanggal 14 Maret 2019 di kota Bandung. | PASS |
| 2 | Siapa pendiri Aurion Dynamics? | test-document.txt | Pendiri Aurion Dynamics adalah Wirawan Sastra. | PASS |
| 3 | Apa nama kode proyek utama Aurion Dynamics? | test-document.txt | Nama kode proyek utama Aurion Dynamics adalah "Proyek Camar". | PASS |
| 4 | Berapa daya jelajah maksimum drone Elang-9? | test-document.txt | Daya jelajah maksimum drone Elang-9 adalah 340 kilometer dalam sekali pengisian baterai. | PASS |
| 5 | Berapa lama Elang-9 bisa terbang nonstop? | test-document.txt | Berdasarkan konteks di atas, Elang-9 mampu terbang selama **6 jam nonstop**. | PASS |
| 6 | Siapa nama maskot resmi Aurion Dynamics? | test-document.txt | Berdasarkan konteks yang diberikan, tidak ada informasi mengenai siapa nama maskot resmi Aurion Dynamics (saya tidak tahu). | FAIL (baru) — jawabannya ada di dokumen (maskot "Kiko"), chunk yang memuatnya tidak masuk top-3 dari 9 chunk. Regresi dari baseline (soal ini PASS sebelumnya). |
| 7 | Kenapa tanggal lahir Kiko (1 Januari 2016) lebih tua dari perusahaannya? | test-document.txt | Hal tersebut sengaja dibuat sebagai lelucon internal karena Kiko "diciptakan sebelum perusahaan ada". | PASS |
| 8 | Siapa yang memimpin Divisi Riset Aurion Dynamics? | test-document.txt | Divisi Riset Aurion Dynamics dipimpin oleh **Dr. Bunga Larasati**. | PASS |
| 9 | Sebelum bergabung ke Aurion Dynamics, Dr. Bunga Larasati bekerja di mana? | test-document.txt | Berdasarkan konteks di atas, sebelum bergabung ke Aurion Dynamics, Dr. Bunga Larasati bekerja di sebuah laboratorium penerbangan di Toulouse, Prancis. | PASS |
| 10 | Apa tujuan dari Proyek Camar Fase 2? | test-document.txt | Tujuan dari Proyek Camar Fase 2 adalah untuk menambahkan kemampuan deteksi asap kebakaran hutan secara otomatis menggunakan sensor inframerah. | PASS — ini yang FAIL di baseline, sekarang benar setelah chunk diperkecil. |
| 11 | Apa penyebab insiden kegagalan Elang-9 pada 9 September 2023? | test-document.txt | Penyebab insiden kegagalan Elang-9 pada 9 September 2023 adalah kerusakan pada modul GPS internal. | PASS |
| 12 | Apa nama protokol keamanan yang dibuat setelah insiden Gunung Puntang? | test-document.txt | Nama protokol keamanan yang dibuat setelah insiden Gunung Puntang adalah **"Protokol Camar-9"**. | PASS |
| 13 | Berapa harga langganan paket dasar CamarView per bulan? | test-document.txt | Harga langganan paket dasar CamarView adalah Rp 4.500.000 per bulan. | PASS |
| 14 | Berapa pendapatan tahunan Aurion Dynamics di tahun 2024? | test-document.txt | Pendapatan tahunan Aurion Dynamics pada tahun 2024 adalah Rp 8,2 miliar. | PASS |
| 15 | Siapa investor utama Aurion Dynamics dan berapa modal yang ditanamkan? | test-document.txt | Investor utama Aurion Dynamics adalah firma modal ventura bernama **Ventura Nusantara Capital**, dengan modal yang ditanamkan sebesar **Rp 15 miliar** (pada putaran pendanaan seri A di tahun 2022). | PASS |
| 16 | Di kota mana Aurion Dynamics berencana membuka kantor cabang kedua? | test-document.txt | Berdasarkan konteks di atas, Aurion Dynamics berencana membuka kantor cabang kedua di kota Balikpapan. | PASS |
| 17 | Siapa CEO Aurion Dynamics saat ini? | test-document.txt | Berdasarkan konteks yang diberikan, tidak ada informasi mengenai siapa CEO Aurion Dynamics saat ini. Saya tidak tahu. | PASS — abstain benar. |
| 18 | Berapa jumlah total drone Elang-9 yang sudah diproduksi Aurion Dynamics? | test-document.txt | Saya tidak tahu. Context yang diberikan tidak memuat informasi mengenai jumlah total drone Elang-9 yang sudah diproduksi oleh Aurion Dynamics. | PASS — abstain benar. |
| 19 | Apa ibu kota Prancis? | test-document.txt | Berdasarkan konteks yang diberikan, saya tidak tahu karena informasi mengenai ibu kota Prancis tidak disebutkan dalam teks. | PASS — abstain benar. |
| 20 | Siapa nama presiden direktur Ventura Nusantara Capital? | test-document.txt | Saya tidak tahu. Informasi mengenai nama presiden direktur Ventura Nusantara Capital tidak ada dalam konteks yang diberikan. | PASS — abstain benar. |
