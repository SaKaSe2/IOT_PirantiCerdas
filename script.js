// ========================================
// PEST DETECTOR DASHBOARD - JavaScript
// Real-time Monitoring & Control
// ========================================

// ===== CONFIGURATION =====
const API_BASE_URL = 'api/';
const SSE_URL = API_BASE_URL + 'sse.php';

// ===== GLOBAL VARIABLES =====
let eventSource = null;
let distanceChart = null;
let currentMode = 'AUTO';
let currentBuzzer = 'OFF';
let currentSensitivity = 50;
let lastAlertLevel = 0;

// ===== AUDIO NOTIFICATION =====
const alertSound = new Audio('data:audio/wav;base64,UklGRnoGAABXQVZFZm10IBAAAAABAAEAQB8AAEAfAAABAAgAZGF0YQoGAACBhYqFbF1fdJivrJBhNjVgodDbq2EcBj+a2/LDciUFLIHO8tiJNwgZaLvt559NEAxQp+PwtmMcBjiR1/LMeSwFJHfH8N2QQAoUXrTp66hVFApGn+DyvmwhBi6F0fPTgjMGHm7A7+OZTA0PV6nk7bJgGAc5j9XvxXYpBSd+zPLaizsHGW7C8OSYSwwOVKrj7bJhGQc4kdXvxXYpBSZ9y/LaizsHGW7C8OSYSwwOVKrj7bJhGQc4kdXvxXYpBSZ9y/LaizsHGW7C8OSYSwwOVKrj7bJhGQc4kdXvxXYpBSZ9y/LaizsHGW7C8OSYSwwOVKrj7bJhGQc4kdXvxXYpBSZ9y/LaizsHGW7C8OSYSwwOVKrj7bJhGQc4kdXvxXYpBSZ9y/LaizsHGW7C8OSYSwwOVKrj7bJhGQc4kdXvxXYpBSZ9y/LaizsHGW7C8OSYSwwOVKrj7bJhGQc4kdXvxXYpBSZ9y/LaizsHGW7C8OSYSwwOVKrj7bJhGQc4kdXvxXYpBSZ9y/LaizsHGW7C8OSYSwwOVKrj7bJhGQc4kdXvxXYpBSZ9y/LaizsHGW7C8OSYSwwOVKrj7Q==');
alertSound.volume = 0.3;

// ===== INITIALIZE ON PAGE LOAD =====
document.addEventListener('DOMContentLoaded', function() {
    console.log('🚀 Pest Detector Dashboard Initialized');
    
    // Initialize Chart
    initChart();
    
    // Start SSE Connection
    initSSE();
    
    // Load Statistics
    loadStatistics();
    
    // Update chart periodically
    setInterval(updateChart, 5000);
    
    // Update statistics periodically
    setInterval(loadStatistics, 10000);
});

// ===== SERVER-SENT EVENTS (SSE) =====
function initSSE() {
    if (eventSource) {
        eventSource.close();
    }
    
    console.log('📡 Connecting to SSE...');
    updateConnectionStatus('connecting');
    
    eventSource = new EventSource(SSE_URL);
    
    eventSource.onopen = function() {
        console.log('✅ SSE Connected');
        updateConnectionStatus('connected');
    };
    
    eventSource.onmessage = function(event) {
        try {
            const data = JSON.parse(event.data);
            
            if (data.type === 'data') {
                updateDashboard(data);
            } else if (data.type === 'ping') {
                console.log('💓 Ping:', data.time);
            }
        } catch (error) {
            console.error('❌ SSE Parse Error:', error);
        }
    };
    
    eventSource.onerror = function(error) {
        console.error('❌ SSE Error:', error);
        updateConnectionStatus('disconnected');
        
        // Reconnect after 5 seconds
        setTimeout(() => {
            console.log('🔄 Reconnecting SSE...');
            initSSE();
        }, 5000);
    };
}

// ===== UPDATE CONNECTION STATUS =====
function updateConnectionStatus(status) {
    const statusEl = document.getElementById('connectionStatus');
    
    if (status === 'connecting') {
        statusEl.innerHTML = '<i class="fas fa-circle-notch fa-spin"></i> Connecting...';
        statusEl.className = 'connection-status';
    } else if (status === 'connected') {
        statusEl.innerHTML = '<i class="fas fa-check-circle"></i> Connected';
        statusEl.className = 'connection-status connected';
    } else {
        statusEl.innerHTML = '<i class="fas fa-times-circle"></i> Disconnected';
        statusEl.className = 'connection-status disconnected';
    }
}

// ===== UPDATE DASHBOARD WITH REAL-TIME DATA =====
function updateDashboard(data) {
    // Update timestamp
    document.getElementById('lastUpdate').textContent = formatTime(data.timestamp);
    
    // Update motion detection
    const motionValue = document.getElementById('motionValue');
    motionValue.textContent = data.motion === 1 ? 'Detected!' : 'Clear';
    motionValue.style.color = data.motion === 1 ? '#e74c3c' : '#27ae60';
    
    // Update distance
    document.getElementById('distanceValue').textContent = data.distance.toFixed(1);
    
    // Update LED status
    const ledValue = document.getElementById('ledValue');
    ledValue.textContent = data.led;
    ledValue.style.color = getLedColor(data.led);
    
    // Update buzzer intensity
    document.getElementById('buzzerValue').textContent = data.buzzer;
    
    // Update alert badge
    updateAlertBadge(data.alert_level, data.alert_text);
    
    // Show alert banner if danger detected
    if (data.alert_level === 2 && lastAlertLevel !== 2) {
        showAlertBanner('⚠️ HAMA TERDETEKSI! Jarak: ' + data.distance.toFixed(1) + ' cm');
        playAlertSound();
    }
    
    lastAlertLevel = data.alert_level;
}

// ===== UPDATE ALERT BADGE =====
function updateAlertBadge(level, text) {
    const badge = document.getElementById('alertBadge');
    const alertText = document.getElementById('alertText');
    
    badge.className = 'alert-badge';
    alertText.textContent = text.toUpperCase();
    
    if (level === 0) {
        badge.classList.add('safe');
    } else if (level === 1) {
        badge.classList.add('warning');
    } else if (level === 2) {
        badge.classList.add('danger');
    }
}

// ===== SHOW ALERT BANNER =====
function showAlertBanner(message) {
    const banner = document.getElementById('alertBanner');
    const messageEl = document.getElementById('alertMessage');
    
    messageEl.textContent = message;
    banner.classList.remove('hidden');
    
    // Auto hide after 10 seconds
    setTimeout(() => {
        banner.classList.add('hidden');
    }, 10000);
}

// ===== CLOSE ALERT BANNER =====
function closeAlert() {
    document.getElementById('alertBanner').classList.add('hidden');
}

// ===== PLAY ALERT SOUND =====
function playAlertSound() {
    alertSound.currentTime = 0;
    alertSound.play().catch(e => console.log('Sound play failed:', e));
}

// ===== INITIALIZE CHART =====
function initChart() {
    const ctx = document.getElementById('distanceChart').getContext('2d');
    
    distanceChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: [],
            datasets: [
                {
                    label: 'Jarak (cm)',
                    data: [],
                    borderColor: '#3498db',
                    backgroundColor: 'rgba(52, 152, 219, 0.1)',
                    borderWidth: 2,
                    tension: 0.4,
                    fill: true,
                    yAxisID: 'y'
                },
                {
                    label: 'Alert Level',
                    data: [],
                    borderColor: '#e74c3c',
                    backgroundColor: 'rgba(231, 76, 60, 0.1)',
                    borderWidth: 2,
                    tension: 0.4,
                    fill: true,
                    yAxisID: 'y1'
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                mode: 'index',
                intersect: false,
            },
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    backgroundColor: 'rgba(0,0,0,0.8)',
                    padding: 12,
                    titleFont: {
                        size: 14
                    },
                    bodyFont: {
                        size: 13
                    }
                }
            },
            scales: {
                y: {
                    type: 'linear',
                    display: true,
                    position: 'left',
                    title: {
                        display: true,
                        text: 'Jarak (cm)'
                    },
                    beginAtZero: true
                },
                y1: {
                    type: 'linear',
                    display: true,
                    position: 'right',
                    title: {
                        display: true,
                        text: 'Alert Level'
                    },
                    min: 0,
                    max: 2,
                    grid: {
                        drawOnChartArea: false,
                    }
                }
            }
        }
    });
}

// ===== UPDATE CHART DATA =====
function updateChart() {
    const limit = document.getElementById('chartTimeRange').value;
    
    fetch(API_BASE_URL + 'get_chart_data.php?limit=' + limit)
        .then(response => response.json())
        .then(result => {
            if (result.status === 'success') {
                const chartData = result.chartData;
                
                distanceChart.data.labels = chartData.labels;
                distanceChart.data.datasets[0].data = chartData.distance;
                distanceChart.data.datasets[1].data = chartData.alert;
                distanceChart.update('none'); // Update without animation
            }
        })
        .catch(error => console.error('Chart update error:', error));
}

// ===== UPDATE CHART RANGE =====
function updateChartRange() {
    updateChart();
}

// ===== LOAD STATISTICS =====
function loadStatistics() {
    fetch(API_BASE_URL + 'get_statistics.php')
        .then(response => response.json())
        .then(result => {
            if (result.status === 'success') {
                // Update today's statistics
                document.getElementById('dangerCount').textContent = result.today.danger_alerts;
                document.getElementById('warningCount').textContent = result.today.warning_alerts;
                document.getElementById('motionCount').textContent = result.today.motion_detected;
                document.getElementById('avgDistance').textContent = result.today.avg_distance + ' cm';
                
                // Update log list
                updateLogList(result.pest_log);
                
                // Update control settings
                if (result.settings) {
                    currentMode = result.settings.mode;
                    currentBuzzer = result.settings.buzzer;
                    currentSensitivity = result.settings.sensitivity;
                    
                    updateControlUI();
                }
            }
        })
        .catch(error => console.error('Statistics error:', error));
}

// ===== UPDATE LOG LIST =====
function updateLogList(logs) {
    const logList = document.getElementById('logList');
    
    if (logs.length === 0) {
        logList.innerHTML = `
            <div class="log-empty">
                <i class="fas fa-inbox"></i>
                <p>Belum ada deteksi hari ini</p>
            </div>
        `;
        return;
    }
    
    logList.innerHTML = '';
    logs.forEach(log => {
        const logItem = document.createElement('div');
        logItem.className = 'log-item';
        logItem.innerHTML = `
            <div class="log-info">
                <div class="log-time">${formatTime(log.time)}</div>
                <div class="log-details">Jarak: ${log.distance.toFixed(1)} cm | Alert: ${getAlertText(log.alert)}</div>
            </div>
        `;
        logList.appendChild(logItem);
    });
}

// ===== CONTROL FUNCTIONS =====
function setMode(mode) {
    currentMode = mode;
    updateControlUI();
    
    // Show/hide manual control
    const manualControl = document.getElementById('manualControl');
    if (mode === 'MANUAL') {
        manualControl.style.display = 'block';
    } else {
        manualControl.style.display = 'none';
    }
}

function setBuzzer(status) {
    currentBuzzer = status;
    updateControlUI();
}

function updateSensitivityDisplay(value) {
    document.getElementById('sensitivityDisplay').textContent = value + ' cm';
    currentSensitivity = parseInt(value);
}

function updateControlUI() {
    // Update mode buttons
    document.getElementById('autoBtn').classList.toggle('active', currentMode === 'AUTO');
    document.getElementById('manualBtn').classList.toggle('active', currentMode === 'MANUAL');
    
    // Update buzzer buttons
    document.getElementById('buzzerOnBtn').classList.toggle('active', currentBuzzer === 'ON');
    document.getElementById('buzzerOffBtn').classList.toggle('active', currentBuzzer === 'OFF');
    
    // Update sensitivity slider
    document.getElementById('sensitivitySlider').value = currentSensitivity;
    document.getElementById('sensitivityDisplay').textContent = currentSensitivity + ' cm';
}

function applySettings() {
    const formData = new FormData();
    formData.append('mode', currentMode);
    formData.append('buzzer', currentBuzzer);
    formData.append('sensitivity', currentSensitivity);
    formData.append('notification', true);
    
    fetch(API_BASE_URL + 'update_control.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(result => {
        if (result.status === 'success') {
            showNotification('✅ Pengaturan berhasil disimpan!', 'success');
        } else {
            showNotification('❌ Gagal menyimpan pengaturan!', 'error');
        }
    })
    .catch(error => {
        console.error('Settings error:', error);
        showNotification('❌ Terjadi kesalahan!', 'error');
    });
}

// ===== HELPER FUNCTIONS =====
function formatTime(timestamp) {
    const date = new Date(timestamp);
    return date.toLocaleTimeString('id-ID', {
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit'
    });
}

function getLedColor(status) {
    switch(status) {
        case 'RED': return '#e74c3c';
        case 'YELLOW': return '#f39c12';
        case 'GREEN': return '#27ae60';
        default: return '#95a5a6';
    }
}

function getAlertText(level) {
    switch(level) {
        case 0: return 'Aman';
        case 1: return 'Waspada';
        case 2: return 'Bahaya';
        default: return 'Unknown';
    }
}

function showNotification(message, type) {
    // Create notification element
    const notification = document.createElement('div');
    notification.className = 'notification ' + type;
    notification.textContent = message;
    notification.style.cssText = `
        position: fixed;
        top: 100px;
        right: 20px;
        background: ${type === 'success' ? '#27ae60' : '#e74c3c'};
        color: white;
        padding: 1rem 2rem;
        border-radius: 10px;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        z-index: 9999;
        animation: slideIn 0.3s ease;
    `;
    
    document.body.appendChild(notification);
    
    // Remove after 3 seconds
    setTimeout(() => {
        notification.style.animation = 'slideOut 0.3s ease';
        setTimeout(() => notification.remove(), 300);
    }, 3000);
}

// ===== ADD ANIMATION STYLES =====
const style = document.createElement('style');
style.textContent = `
    @keyframes slideIn {
        from { transform: translateX(400px); opacity: 0; }
        to { transform: translateX(0); opacity: 1; }
    }
    @keyframes slideOut {
        from { transform: translateX(0); opacity: 1; }
        to { transform: translateX(400px); opacity: 0; }
    }
`;
document.head.appendChild(style);

console.log('✅ Dashboard Ready!');