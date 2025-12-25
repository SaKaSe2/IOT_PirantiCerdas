# 🐛 SISTEM PENDETEKSI HAMA OTOMATIS BERBASIS IOT

**Proyek UAP Piranti Cerdas - Universitas Muhammadiyah Malang**

---

## 📋 DESKRIPSI PROYEK

Sistem pendeteksi hama otomatis untuk kebun hidroponik rumah yang menggunakan teknologi IoT. Sistem ini dapat mendeteksi keberadaan hama (tikus, burung, serangga) secara real-time, memberikan peringatan melalui alarm suara, dan mengirimkan notifikasi ke dashboard web.

### ✨ Fitur Utama

- ✅ **Deteksi Gerakan Real-time** menggunakan sensor PIR
- ✅ **Pengukuran Jarak** menggunakan sensor ultrasonik HC-SR04
- ✅ **Alarm Suara Otomatis** dengan intensitas variabel (PWM)
- ✅ **Indikator LED** untuk status sistem
- ✅ **Dashboard Web Custom** tanpa platform pihak ketiga
- ✅ **Grafik Real-time** menggunakan Chart.js
- ✅ **Server-Sent Events (SSE)** untuk update real-time
- ✅ **Mode AUTO & MANUAL** dengan kontrol jarak jauh
- ✅ **Statistik & Log Deteksi** harian

---

## 🎯 KEBUTUHAN MODUL UAP

### ✅ Checklist Ketentuan

| Ketentuan | Status | Keterangan |
|-----------|--------|------------|
| Tema IoT dengan permasalahan & solusi | ✅ | Pendeteksi hama pada hidroponik |
| 1 Input Analog | ✅ | Sensor Ultrasonik (jarak) |
| 1 Input Digital | ✅ | Sensor PIR (gerakan) |
| 1 Output Analog | ✅ | Buzzer PWM (intensitas variabel) |
| 1 Output Digital | ✅ | LED Indikator |
| Kontrol jarak jauh | ✅ | Via website dashboard |
| Grafik real-time | ✅ | Chart.js dengan SSE |
| Website custom (BUKAN Blynk/ThingSpeak) | ✅ | Custom PHP + JavaScript |
| Teknologi komunikasi | ✅ | HTTP + Server-Sent Events (SSE) |

---

## 🛠️ KOMPONEN HARDWARE

### Daftar Komponen

1. **ESP32 DevKit V1** - Mikrokontroler IoT
2. **Sensor PIR HC-SR501** - Deteksi gerakan (Digital Input)
3. **Sensor Ultrasonik HC-SR04** - Pengukur jarak (Analog Input)
4. **Buzzer Aktif 5V** - Alarm suara (Analog Output PWM)
5. **LED** - Indikator status (Digital Output)
6. **Resistor 220Ω** - Untuk LED
7. **Breadboard & Kabel Jumper**
8. **Power Supply 5V**

### Skema Koneksi

```
ESP32 Pin Layout:
┌─────────────────────────────┐
│ GPIO 13 ──→ PIR (OUT)       │  Digital Input
│ GPIO 26 ──→ TRIG (HC-SR04)  │  Trigger
│ GPIO 27 ──→ ECHO (HC-SR04)  │  Echo (Analog Input)
│ GPIO 25 ──→ BUZZER (+)      │  PWM (Analog Output)
│ GPIO 2  ──→ LED (+)         │  Digital Output
│ GND     ──→ GND (All)       │
│ 5V      ──→ VCC (All)       │
└─────────────────────────────┘
```

---

## 💻 INSTALASI SOFTWARE

### 1. Persiapan Tools

- **Arduino IDE** (dengan library ESP32)
- **XAMPP** atau **WAMP** (PHP + MySQL)
- **Web Browser** (Chrome/Firefox)
- **Text Editor** (VS Code recommended)

### 2. Setup Database

```bash
# 1. Buka phpMyAdmin (http://localhost/phpmyadmin)
# 2. Import file database.sql
# 3. Database "pest_detector" akan otomatis dibuat
```

**Atau jalankan manual:**

```sql
CREATE DATABASE pest_detector;
USE pest_detector;
-- Copy paste isi file database.sql
```

### 3. Setup Backend (PHP API)

```bash
# 1. Copy folder proyek ke htdocs/www
C:\xampp\htdocs\pest_detector\

# 2. Struktur folder:
pest_detector/
├── api/
│   ├── control.json
│   ├── insert.php
│   ├── get_latest.php
│   ├── get_chart_data.php
│   ├── get_statistics.php
│   ├── update_control.php
│   └── sse.php
├── config/
│   └── database.php
├── index.html
├── style.css
├── script.js
└── database.sql

# 3. Edit config/database.php sesuai MySQL Anda
```

### 4. Setup ESP32

```bash
# 1. Buka file pest_detector_esp32.ino di Arduino IDE
# 2. Install library jika belum:
#    - WiFi (built-in ESP32)
#    - HTTPClient (built-in ESP32)

# 3. Edit konfigurasi WiFi dan Server:
const char* ssid = "NAMA_WIFI_ANDA";
const char* password = "PASSWORD_WIFI";
const char* serverInsert = "http://192.168.1.100/pest_detector/api/insert.php";
const char* serverControl = "http://192.168.1.100/pest_detector/api/control.json";

# 4. Upload ke ESP32
```

### 5. Cek IP Address Server

**Windows:**
```cmd
ipconfig
```

**Linux/Mac:**
```bash
ifconfig
```

Gunakan IP Local (contoh: 192.168.1.100) di ESP32 code!

---

## 🚀 CARA PENGGUNAAN

### 1. Jalankan Sistem

```bash
# 1. Start XAMPP (Apache + MySQL)
# 2. Upload code ke ESP32
# 3. Buka Serial Monitor (115200 baud)
# 4. Tunggu ESP32 connect ke WiFi
# 5. Akses dashboard: http://localhost/pest_detector/
```

### 2. Dashboard Features

**A. Real-time Monitoring**
- Status alert (Aman/Waspada/Bahaya)
- Data sensor (gerakan, jarak, LED, buzzer)
- Grafik real-time jarak & alert level
- Update otomatis via SSE

**B. Panel Kontrol**
- **Mode AUTO**: Sistem otomatis deteksi hama
- **Mode MANUAL**: Kontrol buzzer manual
- **Sensitivitas**: Atur jarak deteksi (10-200 cm)

**C. Statistik**
- Total deteksi bahaya hari ini
- Total peringatan
- Gerakan terdeteksi
- Rata-rata jarak

**D. Log Deteksi**
- Histori 10 deteksi terakhir
- Timestamp & detail jarak

---

## 📊 CARA KERJA SISTEM

### Alur Logika AUTO Mode

```
1. PIR mendeteksi gerakan → motion = 1
2. Ultrasonik ukur jarak → distance = X cm
3. ESP32 analisis:
   
   IF motion == 1 AND distance < sensitivity:
      → BAHAYA (Alert Level 2)
      → LED ON (RED)
      → Buzzer FULL (255)
   
   ELSE IF motion == 1 AND distance < (sensitivity + 20):
      → WASPADA (Alert Level 1)
      → LED ON (YELLOW)
      → Buzzer MEDIUM (150)
   
   ELSE:
      → AMAN (Alert Level 0)
      → LED OFF
      → Buzzer OFF
      
4. Data dikirim ke server setiap 3 detik
5. Website update via SSE
6. Grafik & statistik terupdate otomatis
```

### Komunikasi Data

```
ESP32 ──[HTTP POST]──→ insert.php ──→ MySQL Database
  ↓                                        ↓
  ├─[HTTP GET]─→ control.json             ├─[SSE]─→ Website
  └─[Loop 3s]                              └─[Query]
```

---

## 🎨 SCREENSHOT & DEMO

### Dashboard Preview

```
┌──────────────────────────────────────────────┐
│  🐛 Pest Detector System                    │
│  Status: Connected | Last Update: 14:23:45   │
├──────────────────────────────────────────────┤
│                                               │
│  ┌─────────────────┐  ┌──────────────────┐  │
│  │ STATUS: AMAN    │  │ PANEL KONTROL    │  │
│  │ 🛡️ Tidak Ada   │  │ Mode: ● AUTO     │  │
│  │    Ancaman      │  │       ○ MANUAL   │  │
│  │                 │  │                  │  │
│  │ Gerakan: Clear  │  │ Sensitivitas:    │  │
│  │ Jarak: 125.3 cm │  │ [======|====] 50cm│ │
│  │ LED: GREEN      │  │                  │  │
│  │ Buzzer: 0/255   │  │ [💾 Terapkan]   │  │
│  └─────────────────┘  └──────────────────┘  │
│                                               │
│  ┌─────────────────────────────────────────┐ │
│  │ GRAFIK REAL-TIME                        │ │
│  │ ┌─────────────────────────────────────┐ │ │
│  │ │    Distance Chart                   │ │ │
│  │ │ 200├─────────────────────────────── │ │ │
│  │ │    │        ╱╲                      │ │ │
│  │ │ 100├───────╱──╲─────────────────── │ │ │
│  │ │    │      ╱    ╲    ╱╲             │ │ │
│  │ │   0├─────────────────────────────── │ │ │
│  │ └─────────────────────────────────────┘ │ │
│  └─────────────────────────────────────────┘ │
└──────────────────────────────────────────────┘
```

---

## 📝 PRESENTASI UAP

### Struktur Presentasi (8 Menit)

**Bagian 1: Konsep (3 menit)**

1. **Latar Belakang** (30 detik)
   - Hidroponik rumah rentan hama
   - Pengawasan manual tidak efektif
   
2. **Solusi IoT** (1 menit)
   - Sistem deteksi otomatis
   - Komponen yang digunakan
   - Fitur unggulan
   
3. **Arsitektur Sistem** (1.5 menit)
   - Skema hardware
   - Flow diagram
   - Komunikasi data

**Bagian 2: Demo (5 menit)**

1. **Hardware Demo** (2 menit)
   - Tunjukkan rangkaian
   - Test sensor PIR (gerakkan tangan)
   - Test ultrasonik (dekatkan objek)
   
2. **Software Demo** (3 menit)
   - Buka dashboard
   - Tunjukkan real-time update
   - Switch mode AUTO/MANUAL
   - Ubah sensitivitas
   - Tunjukkan grafik & statistik

### Tips Presentasi

✅ **Persiapan:**
- Test semua sebelum presentasi
- Backup video demo jika ada masalah
- Siapkan slide PowerPoint yang sudah ada

✅ **Saat Demo:**
- Jelaskan sambil menunjukkan
- Highlight fitur custom website (BUKAN Blynk!)
- Tunjukkan SSE real-time
- Jelaskan input/output (analog/digital)

✅ **Pertanyaan Asisten (20 poin!):**
- Pahami cara kerja PIR & Ultrasonik
- Jelaskan PWM untuk buzzer
- Jelaskan SSE vs HTTP Polling
- Siap jawab tentang database structure

---

## 🎯 RUBRIK PENILAIAN

| Aspek | Nilai | Target |
|-------|-------|--------|
| Ide yang dipilih | 10 | ✅ Solusi hama hidroponik |
| Sesuai ketentuan | 15 | ✅ Semua requirement terpenuhi |
| Program berjalan | 20 | ✅ Hardware & software works |
| Penguasaan materi | 10 | ✅ Pahami konsep IoT |
| Pertanyaan asisten | 20 | ⚠️ Study dokumentasi ini! |
| Kreativitas & problem solving | 25 | ✅ Dashboard custom + SSE |
| **TOTAL** | **100** | **Target A** 🎯 |

---

## 🔧 TROUBLESHOOTING

### Problem 1: ESP32 Tidak Connect WiFi

```cpp
// Cek:
1. SSID dan password benar?
2. WiFi 2.4GHz (bukan 5GHz)?
3. ESP32 dalam jangkauan WiFi?
4. Serial Monitor menunjukkan error apa?
```

### Problem 2: Data Tidak Masuk Database

```php
// Cek:
1. MySQL running di XAMPP?
2. Database "pest_detector" sudah dibuat?
3. config/database.php credentials benar?
4. Cek error di Serial Monitor ESP32
5. Test API manual: http://localhost/pest_detector/api/get_latest.php
```

### Problem 3: Dashboard Tidak Real-time

```javascript
// Cek:
1. SSE.php running? Buka di browser langsung
2. Browser support SSE? (Chrome/Firefox OK)
3. CORS error di console? Cek config/database.php header
4. Connection status di dashboard "Connected"?
```

### Problem 4: Sensor Tidak Akurat

```
PIR False Detection:
- Turunkan sensitivitas potensio di sensor
- Jauhi sumber panas (AC, heater)
- Tunggu 1 menit setelah power on

Ultrasonik Jarak Error:
- Cek kabel TRIG & ECHO
- Hindari permukaan miring/soft
- Max range: 4 meter
- Min range: 2 cm
```

---

## 📚 REFERENSI TEKNIS

### ESP32 Pinout
- [ESP32 Pinout Reference](https://randomnerdtutorials.com/esp32-pinout-reference-gpios/)

### Sensor Datasheet
- [PIR HC-SR501 Datasheet](https://www.mpja.com/download/31227sc.pdf)
- [HC-SR04 Ultrasonic Datasheet](https://cdn.sparkfun.com/datasheets/Sensors/Proximity/HCSR04.pdf)

### Web Technologies
- [Chart.js Documentation](https://www.chartjs.org/docs/latest/)
- [Server-Sent Events MDN](https://developer.mozilla.org/en-US/docs/Web/API/Server-sent_events)
- [ESP32 HTTP Client](https://github.com/espressif/arduino-esp32/tree/master/libraries/HTTPClient)

---

## 👥 TEAM

**Developed by:**
- **Rikza Ahmad NM**
- **Muhammad Nabil FS**

**Dosen Pengampu:**
- Muhammad Ilham Perdana S.Tr.T., M.T
- Nadhira Ulya Nisa
- Muhammad Zaky Darajat

**Laboratorium Informatika**  
**Universitas Muhammadiyah Malang**

---

## 📄 LISENSI

Project ini dibuat untuk keperluan akademik UAP Piranti Cerdas.  
© 2025 Universitas Muhammadiyah Malang

---

## 🎉 SELAMAT MENGERJAKAN!

**Tips Terakhir:**
1. ✅ Test SEMUA fitur sebelum presentasi
2. ✅ Record video backup demo
3. ✅ Siapkan jawaban untuk pertanyaan asisten
4. ✅ Pahami alur kerja sistem dari A-Z
5. ✅ Percaya diri saat presentasi!

**Good Luck! 🚀**

---

**Last Updated:** December 2025  
**Version:** 2.2