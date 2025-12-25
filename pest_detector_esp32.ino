#include <WiFi.h>
#include <HTTPClient.h>

// ========== KONFIGURASI WIFI ==========
const char* ssid = "NAMA_WIFI_ANDA";        // Ganti dengan nama WiFi
const char* password = "PASSWORD_WIFI_ANDA"; // Ganti dengan password WiFi

// ========== KONFIGURASI SERVER ==========
const char* serverInsert = "http://192.168.1.100/pest_detector/api/insert.php";  // Ganti IP sesuai server
const char* serverControl = "http://192.168.1.100/pest_detector/api/control.json";

// ========== PIN DEFINITION ==========
#define PIR_PIN        13  // Digital Input - Sensor PIR (deteksi gerakan)
#define TRIG_PIN       26  // Ultrasonic Trigger
#define ECHO_PIN       27  // Ultrasonic Echo (Analog Input - ukur jarak)
#define BUZZER_PIN     25  // Analog Output PWM - Alarm intensitas variabel
#define LED_PIN        2   // Digital Output - LED Indikator

// ========== VARIABEL SENSOR ==========
int motionDetected = 0;      // Status PIR (0 = tidak ada gerakan, 1 = ada gerakan)
float distance = 0.0;        // Jarak objek dalam cm
int alertLevel = 0;          // Level peringatan (0=Aman, 1=Waspada, 2=Bahaya)
String ledStatus = "OFF";    // Status LED
int buzzerIntensity = 0;     // Intensitas buzzer (0-255)

// ========== VARIABEL KONTROL DARI WEBSITE ==========
String systemMode = "AUTO";  // Mode: AUTO atau MANUAL
String manualBuzzer = "OFF"; // Kontrol manual buzzer
int sensitivity = 50;        // Sensitivitas jarak (default 50cm)

// ========== TIMING ==========
unsigned long lastSendTime = 0;
const long sendInterval = 3000;  // Kirim data setiap 3 detik

// ========== FUNGSI SETUP ==========
void setup() {
  Serial.begin(115200);
  
  // Setup Pin
  pinMode(PIR_PIN, INPUT);
  pinMode(TRIG_PIN, OUTPUT);
  pinMode(ECHO_PIN, INPUT);
  pinMode(BUZZER_PIN, OUTPUT);
  pinMode(LED_PIN, OUTPUT);
  
  // Koneksi WiFi
  WiFi.begin(ssid, password);
  Serial.print("Connecting to WiFi");
  while (WiFi.status() != WL_CONNECTED) {
    delay(500);
    Serial.print(".");
  }
  Serial.println("\nWiFi Connected!");
  Serial.print("IP Address: ");
  Serial.println(WiFi.localIP());
}

// ========== FUNGSI LOOP ==========
void loop() {
  // Baca Sensor PIR (Digital Input)
  motionDetected = digitalRead(PIR_PIN);
  
  // Baca Sensor Ultrasonik (Analog Input - jarak)
  distance = readUltrasonic();
  
  // Ambil kontrol dari server
  getControl();
  
  // Proses logika sistem
  if (systemMode == "AUTO") {
    autoMode();
  } else {
    manualMode();
  }
  
  // Kirim data ke server setiap interval
  if (millis() - lastSendTime > sendInterval) {
    sendDataToServer();
    lastSendTime = millis();
  }
  
  delay(100);
}

// ========== BACA SENSOR ULTRASONIK ==========
float readUltrasonic() {
  digitalWrite(TRIG_PIN, LOW);
  delayMicroseconds(2);
  digitalWrite(TRIG_PIN, HIGH);
  delayMicroseconds(10);
  digitalWrite(TRIG_PIN, LOW);
  
  long duration = pulseIn(ECHO_PIN, HIGH, 30000); // Timeout 30ms
  float dist = duration * 0.034 / 2;  // Konversi ke cm
  
  // Filter pembacaan tidak valid
  if (dist > 400 || dist == 0) {
    return 999; // Nilai error
  }
  return dist;
}

// ========== MODE OTOMATIS ==========
void autoMode() {
  // Logika deteksi hama
  if (motionDetected == HIGH && distance < sensitivity) {
    // BAHAYA - Gerakan terdeteksi dan objek dekat
    alertLevel = 2;
    ledStatus = "RED";
    digitalWrite(LED_PIN, HIGH);
    buzzerIntensity = 255; // Buzzer penuh
    analogWrite(BUZZER_PIN, buzzerIntensity);
    
  } else if (motionDetected == HIGH && distance < (sensitivity + 20)) {
    // WASPADA - Gerakan terdeteksi tapi masih agak jauh
    alertLevel = 1;
    ledStatus = "YELLOW";
    digitalWrite(LED_PIN, HIGH);
    buzzerIntensity = 150; // Buzzer sedang
    analogWrite(BUZZER_PIN, buzzerIntensity);
    
  } else {
    // AMAN - Tidak ada ancaman
    alertLevel = 0;
    ledStatus = "GREEN";
    digitalWrite(LED_PIN, LOW);
    buzzerIntensity = 0;
    analogWrite(BUZZER_PIN, 0);
  }
  
  Serial.printf("AUTO | Motion: %d | Dist: %.1fcm | Alert: %d | Buzzer: %d\n", 
                motionDetected, distance, alertLevel, buzzerIntensity);
}

// ========== MODE MANUAL ==========
void manualMode() {
  // Kontrol manual dari website
  if (manualBuzzer == "ON") {
    digitalWrite(LED_PIN, HIGH);
    ledStatus = "ON";
    buzzerIntensity = 200;
    analogWrite(BUZZER_PIN, buzzerIntensity);
    alertLevel = 2;
  } else {
    digitalWrite(LED_PIN, LOW);
    ledStatus = "OFF";
    buzzerIntensity = 0;
    analogWrite(BUZZER_PIN, 0);
    alertLevel = 0;
  }
  
  Serial.printf("MANUAL | Buzzer: %s | LED: %s\n", manualBuzzer.c_str(), ledStatus.c_str());
}

// ========== AMBIL KONTROL DARI SERVER ==========
void getControl() {
  if (WiFi.status() == WL_CONNECTED) {
    HTTPClient http;
    http.begin(serverControl);
    int httpCode = http.GET();
    
    if (httpCode == 200) {
      String payload = http.getString();
      
      // Parse JSON sederhana (tanpa library)
      if (payload.indexOf("\"mode\":\"MANUAL\"") > 0) {
        systemMode = "MANUAL";
      } else {
        systemMode = "AUTO";
      }
      
      if (payload.indexOf("\"buzzer\":\"ON\"") > 0) {
        manualBuzzer = "ON";
      } else {
        manualBuzzer = "OFF";
      }
      
      // Parse sensitivitas
      int sensIdx = payload.indexOf("\"sensitivity\":");
      if (sensIdx > 0) {
        sensitivity = payload.substring(sensIdx + 15, sensIdx + 18).toInt();
      }
    }
    http.end();
  }
}

// ========== KIRIM DATA KE SERVER ==========
void sendDataToServer() {
  if (WiFi.status() == WL_CONNECTED) {
    HTTPClient http;
    http.begin(serverInsert);
    http.addHeader("Content-Type", "application/x-www-form-urlencoded");
    
    // Format data POST
    String postData = "motion=" + String(motionDetected) +
                      "&distance=" + String(distance, 1) +
                      "&alert_level=" + String(alertLevel) +
                      "&led=" + ledStatus +
                      "&buzzer=" + String(buzzerIntensity) +
                      "&mode=" + systemMode;
    
    int httpCode = http.POST(postData);
    
    if (httpCode == 200) {
      String response = http.getString();
      Serial.println("Data sent: " + response);
    } else {
      Serial.println("Error sending data: " + String(httpCode));
    }
    
    http.end();
  }
}
