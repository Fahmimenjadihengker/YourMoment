# 📸 Logo YourMoment

## Lokasi Penyimpanan Logo

Folder untuk menyimpan logo project:
```
public/images/
└── logo_yourmoment.png
```

## Cara Menggunakan

1. **Siapkan file logo** dengan nama: `logo_yourmoment.png`
   - Format: PNG, SVG, JPG, atau format gambar lainnya
   - Ukuran rekomendasi: 
     - Navbar: 40px height (auto width)
     - Guest Auth: 64px height (auto width)
   - Rasio aspek: Sebaiknya landscape/horizontal (1.5:1 atau lebih)

2. **Upload logo ke folder** `public/images/`

3. **Logo akan otomatis digunakan di:**
   - ✅ Navbar (Dashboard) - 40px
   - ✅ Login Page - 64px
   - ✅ Register Page - 64px
   - ✅ Wallet Setup Page - 64px
   - ✅ Profile & Recovery Pages - 64px

## Fitur Fallback

Jika file logo tidak ditemukan, sistem akan otomatis menampilkan icon emoji (💚) sebagai pengganti.

## Update Dinamis

- Untuk update logo, cukup ganti file `logo_yourmoment.png`
- Tidak perlu update code atau restart server
- Sistem akan otomatis menggunakan logo terbaru

## Rekomendasi File

- **Ukuran file**: Max 500KB untuk performa optimal
- **Format**: PNG (dengan transparency) atau SVG untuk hasil terbaik
- **Dimensi**: Minimal width 200px untuk hasil yang tajam

---

Logo siap digunakan! Letakkan file `logo_yourmoment.png` di folder `public/images/` 🚀
