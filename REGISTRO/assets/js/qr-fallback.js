// QR Code Fallback usando Google Charts API
class QRFallback {
    static async generateQRWithGoogleCharts(text, canvas, size = 256) {
        try {
            // Crear URL para Google Charts QR API
            const qrUrl = `https://chart.googleapis.com/chart?chs=${size}x${size}&cht=qr&chl=${encodeURIComponent(text)}&choe=UTF-8`;
            
            // Crear imagen
            const img = new Image();
            img.crossOrigin = 'anonymous';
            
            return new Promise((resolve, reject) => {
                img.onload = () => {
                    const ctx = canvas.getContext('2d');
                    canvas.width = size;
                    canvas.height = size;
                    
                    // Limpiar canvas
                    ctx.fillStyle = '#ffffff';
                    ctx.fillRect(0, 0, size, size);
                    
                    // Dibujar QR
                    ctx.drawImage(img, 0, 0, size, size);
                    resolve(canvas);
                };
                
                img.onerror = () => {
                    reject(new Error('Error cargando QR desde Google Charts'));
                };
                
                img.src = qrUrl;
            });
        } catch (error) {
            throw new Error('No se pudo generar QR con Google Charts: ' + error.message);
        }
    }
    
    static generateQRWithText(text, canvas, size = 256) {
        const ctx = canvas.getContext('2d');
        canvas.width = size;
        canvas.height = size;
        
        // Fondo blanco
        ctx.fillStyle = '#ffffff';
        ctx.fillRect(0, 0, size, size);
        
        // Borde negro
        ctx.strokeStyle = '#000000';
        ctx.lineWidth = 4;
        ctx.strokeRect(2, 2, size - 4, size - 4);
        
        // Texto central
        ctx.fillStyle = '#000000';
        ctx.font = 'bold 16px monospace';
        ctx.textAlign = 'center';
        ctx.textBaseline = 'middle';
        
        // Dividir texto en líneas si es muy largo
        const maxWidth = size - 20;
        const words = text.split('');
        let line = '';
        let y = size / 2 - 20;
        
        for (let n = 0; n < words.length; n++) {
            const testLine = line + words[n];
            const metrics = ctx.measureText(testLine);
            const testWidth = metrics.width;
            
            if (testWidth > maxWidth && n > 0) {
                ctx.fillText(line, size / 2, y);
                line = words[n];
                y += 20;
            } else {
                line = testLine;
            }
        }
        ctx.fillText(line, size / 2, y);
        
        // Agregar "QR CODE" label
        ctx.font = 'bold 12px Arial';
        ctx.fillText('QR CODE', size / 2, size - 20);
        
        return canvas;
    }
}

// Exportar
window.QRFallback = QRFallback;
