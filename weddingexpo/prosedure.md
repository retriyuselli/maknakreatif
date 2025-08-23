🟦 Expo
• id_expo (PK)
• nama_expo
• tanggal_mulai
• tanggal_selesai
• lokasi
• tahun
• periode

⸻

🟩 Jenis Usaha
• id_jenis_usaha (PK)
• nama_jenis_usaha

⸻

🟨 Vendor
• id_vendor (PK)
• nama_vendor
• id_jenis_usaha (FK)
• alamat
• kota
• no_telepon
• email
• nama_pic
• no_wa_pic

⸻

🟧 Partisipasi
• id_partisipasi (PK)
• id_expo (FK)
• id_vendor (FK)
• tanggal_daftar
• status_pembayaran

⸻

🟥 Pembayaran
• id_pembayaran (PK)
• id_partisipasi (FK)
• nominal
• tanggal_bayar
• metode_pembayaran
• bukti_transfer
• id_rekening (FK)

⸻

🟪 Rekening Tujuan
• id_rekening (PK)
• nama_bank
• nomor_rekening
• nama_pemilik
