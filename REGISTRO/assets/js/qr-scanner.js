// Clase para manejo de QR Scanner
class QRScanner {
    constructor() {
        this.video = null;
        this.canvas = null;
        this.scanning = false;
        this.stream = null;
    }

    // Inicializar el escáner
    async init(videoId, canvasId) {
        this.video = document.getElementById(videoId);
        this.canvas = document.getElementById(canvasId);
        
        if (!this.video || !this.canvas) {
            throw new Error('Elementos de video o canvas no encontrados');
        }

        try {
            // Solicitar acceso a la cámara
            this.stream = await navigator.mediaDevices.getUserMedia({
                video: { facingMode: 'environment' }
            });
            
            this.video.srcObject = this.stream;
            
            return new Promise((resolve) => {
                this.video.onloadedmetadata = () => {
                    this.video.play();
                    resolve();
                };
            });
        } catch (error) {
            console.error('Error al acceder a la cámara:', error);
            throw new Error('No se pudo acceder a la cámara. Por favor, verifica los permisos.');
        }
    }

    // Comenzar a escanear
    startScan() {
        if (this.scanning) return;
        
        this.scanning = true;
        this.scanFrame();
    }

    // Detener el escaneo
    stopScan() {
        this.scanning = false;
        
        if (this.stream) {
            this.stream.getTracks().forEach(track => track.stop());
            this.stream = null;
        }
        
        if (this.video) {
            this.video.srcObject = null;
        }
    }

    // Escanear frame por frame
    scanFrame() {
        if (!this.scanning) return;

        const context = this.canvas.getContext('2d');
        
        if (this.video.readyState === this.video.HAVE_ENOUGH_DATA) {
            this.canvas.height = this.video.videoHeight;
            this.canvas.width = this.video.videoWidth;
            
            context.drawImage(this.video, 0, 0, this.canvas.width, this.canvas.height);
            
            // Intentar leer QR
            try {
                const imageData = context.getImageData(0, 0, this.canvas.width, this.canvas.height);
                const code = jsQR(imageData.data, imageData.width, imageData.height);
                
                if (code) {
                    this.onQRDetected(code.data);
                    this.stopScan();
                    return;
                }
            } catch (error) {
                console.error('Error al procesar QR:', error);
            }
        }
        
        requestAnimationFrame(() => this.scanFrame());
    }

    // Callback cuando se detecta un QR
    onQRDetected(qrData) {
        console.log('QR Detectado:', qrData);
        
        // Disparar evento personalizado
        const event = new CustomEvent('qrDetected', {
            detail: { qrData: qrData }
        });
        document.dispatchEvent(event);
    }
}

// Clase para manejo de generación de QR
class QRGenerator {
    constructor() {
        this.qrCode = null;
    }

    // Generar QR a partir de datos
    async generateQR(data, canvasId, options = {}) {
        const canvas = document.getElementById(canvasId);
        if (!canvas) {
            throw new Error('Canvas no encontrado');
        }

        const defaultOptions = {
            width: 256,
            height: 256,
            colorDark: '#000000',
            colorLight: '#ffffff',
            correctLevel: 'H'
        };

        const finalOptions = { ...defaultOptions, ...options };

        try {
            // Limpiar canvas anterior
            const context = canvas.getContext('2d');
            context.clearRect(0, 0, canvas.width, canvas.height);

            // Método 1: Usar QRCode.js (librería real y funcional)
            if (typeof QRCode !== 'undefined') {
                console.log('Usando QRCode.js - librería real');
                try {
                    await QRCode.toCanvas(canvas, data, {
                        width: finalOptions.width,
                        margin: 1,
                        color: {
                            dark: finalOptions.colorDark,
                            light: finalOptions.colorLight
                        }
                    });
                    return canvas;
                } catch (qrError) {
                    console.warn('QRCode.js falló, usando fallback:', qrError);
                }
            }
            
            // Método 2: Usar BasicQR (nuestro generador simple)
            if (typeof BasicQR !== 'undefined') {
                console.log('Usando BasicQR - fallback');
                BasicQR.generate(data, canvas);
                return canvas;
            }
            
            throw new Error('No se encontró ninguna librería QR disponible');

        } catch (error) {
            console.error('Error al generar QR:', error);
            
            // Último recurso: dibujar algo visible
            try {
                const ctx = canvas.getContext('2d');
                canvas.width = finalOptions.width;
                canvas.height = finalOptions.height;
                
                // Fondo blanco
                ctx.fillStyle = '#ffffff';
                ctx.fillRect(0, 0, finalOptions.width, finalOptions.height);
                
                // Borde negro
                ctx.strokeStyle = '#000000';
                ctx.lineWidth = 4;
                ctx.strokeRect(2, 2, finalOptions.width - 4, finalOptions.height - 4);
                
                // Texto de datos
                ctx.fillStyle = '#000000';
                ctx.font = 'bold 16px monospace';
                ctx.textAlign = 'center';
                ctx.fillText('QR DATA', finalOptions.width/2, finalOptions.height/2 - 20);
                
                ctx.font = '12px monospace';
                const text = data.length > 30 ? data.substring(0, 30) + '...' : data;
                ctx.fillText(text, finalOptions.width/2, finalOptions.height/2);
                
                ctx.font = '10px monospace';
                ctx.fillText('MODO FALLBACK', finalOptions.width/2, finalOptions.height/2 + 20);
                
                return canvas;
            } catch (fallbackError) {
                throw new Error('No se pudo generar ningún tipo de QR: ' + fallbackError.message);
            }
        }
    }

    // Limpiar QR
    clearQR(canvasId) {
        const canvas = document.getElementById(canvasId);
        if (canvas) {
            // Limpiar el canvas y cualquier elemento QR generado
            const context = canvas.getContext('2d');
            context.clearRect(0, 0, canvas.width, canvas.height);
            
            // Eliminar cualquier imagen QR generada por qrcodejs
            const qrImages = canvas.parentElement.querySelectorAll('img');
            qrImages.forEach(img => img.remove());
            
            // Limpiar el canvas
            canvas.style.display = 'block';
        }
    }
}

// Utilidades para el sistema de asistencia
class AttendanceSystem {
    constructor() {
        this.apiBase = './api';
    }

    // Generar QR dinámico para empleado
    async generateEmployeeQR(employeeCode) {
        try {
            const response = await fetch(`${this.apiBase}/generate_qr.php`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    codigo_empleado: employeeCode
                })
            });

            const data = await response.json();

            if (!response.ok || !data.success) {
                throw new Error(data.error || 'Error al generar QR');
            }

            return data;
        } catch (error) {
            console.error('Error en generateEmployeeQR:', error);
            throw error;
        }
    }

    // Generar QR estático para empleado
    async generateStaticQR(employeeCode) {
        try {
            const response = await fetch(`${this.apiBase}/generate_static_qr.php`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    codigo_empleado: employeeCode
                })
            });

            const data = await response.json();

            if (!response.ok || !data.success) {
                throw new Error(data.error || 'Error al generar QR estático');
            }

            return data;
        } catch (error) {
            console.error('Error en generateStaticQR:', error);
            throw error;
        }
    }

    // Registrar asistencia dinámica
    async registerAttendance(qrData) {
        try {
            const response = await fetch(`${this.apiBase}/register_attendance.php`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    qr_data: qrData
                })
            });

            const data = await response.json();

            if (!response.ok || !data.success) {
                throw new Error(data.error || 'Error al registrar asistencia');
            }

            return data;
        } catch (error) {
            console.error('Error en registerAttendance:', error);
            throw error;
        }
    }

    // Registrar asistencia estática
    async registerStaticAttendance(qrData) {
        try {
            const response = await fetch(`${this.apiBase}/register_static_attendance.php`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    qr_data: qrData
                })
            });

            const data = await response.json();

            if (!response.ok || !data.success) {
                throw new Error(data.error || 'Error al registrar asistencia estática');
            }

            return data;
        } catch (error) {
            console.error('Error en registerStaticAttendance:', error);
            throw error;
        }
    }

    // Formatear fecha y hora
    formatDateTime(dateTimeStr) {
        const date = new Date(dateTimeStr);
        return date.toLocaleString('es-ES', {
            year: 'numeric',
            month: '2-digit',
            day: '2-digit',
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit'
        });
    }

    // Mostrar mensaje
    showMessage(message, type = 'info') {
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type}`;
        alertDiv.textContent = message;

        // Insertar al principio del container o body
        const container = document.querySelector('.container') || document.body;
        container.insertBefore(alertDiv, container.firstChild);

        // Auto-eliminar después de 5 segundos
        setTimeout(() => {
            if (alertDiv.parentNode) {
                alertDiv.parentNode.removeChild(alertDiv);
            }
        }, 5000);
    }

    // Validar código de empleado
    validateEmployeeCode(code) {
        // Expresión regular para validar formato (ej: EMP001, EMP123)
        const regex = /^[A-Z]{3}\d{3,}$/;
        return regex.test(code.toUpperCase());
    }
}

// Variables globales
let qrScanner = null;
let qrGenerator = null;
let attendanceSystem = null;

// Inicialización cuando el DOM está listo
document.addEventListener('DOMContentLoaded', function() {
    qrScanner = new QRScanner();
    qrGenerator = new QRGenerator();
    attendanceSystem = new AttendanceSystem();
});

// Exportar clases para uso global
window.QRScanner = QRScanner;
window.QRGenerator = QRGenerator;
window.AttendanceSystem = AttendanceSystem;
