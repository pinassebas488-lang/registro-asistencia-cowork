// QR Code Generator - Implementación simplificada
class SimpleQRGenerator {
    static generateQRCode(text, canvas, size = 256) {
        const ctx = canvas.getContext('2d');
        canvas.width = size;
        canvas.height = size;
        
        // Para una implementación simple, usaremos un placeholder visual
        // En producción, aquí iría un algoritmo completo de generación QR
        
        // Limpiar canvas
        ctx.fillStyle = '#ffffff';
        ctx.fillRect(0, 0, size, size);
        
        // Dibujar un patrón que simule un QR (placeholder)
        ctx.fillStyle = '#000000';
        const cellSize = size / 25; // 25x25 grid
        
        // Esquinas características del QR
        this.drawCornerPattern(ctx, 0, 0, cellSize);
        this.drawCornerPattern(ctx, size - 7 * cellSize, 0, cellSize);
        this.drawCornerPattern(ctx, 0, size - 7 * cellSize, cellSize);
        
        // Patrón aleatorio basado en el texto (simulación)
        const hash = this.simpleHash(text);
        for (let i = 0; i < 25; i++) {
            for (let j = 0; j < 25; j++) {
                // Evitar las esquinas
                if ((i < 7 && j < 7) || (i < 7 && j >= 18) || (i >= 18 && j < 7)) {
                    continue;
                }
                
                if ((hash * (i + j + 1)) % 3 === 0) {
                    ctx.fillRect(i * cellSize, j * cellSize, cellSize, cellSize);
                }
            }
        }
        
        // Agregar texto pequeño en el centro para debugging
        ctx.fillStyle = '#666666';
        ctx.font = '8px Arial';
        ctx.fillText('QR: ' + text.substring(0, 10) + '...', size/2 - 30, size/2);
    }
    
    static drawCornerPattern(ctx, x, y, cellSize) {
        // Patrón de esquina característico de QR codes
        ctx.fillStyle = '#000000';
        
        // Cuadrado exterior
        ctx.fillRect(x, y, 7 * cellSize, 7 * cellSize);
        
        // Cuadrado blanco medio
        ctx.fillStyle = '#ffffff';
        ctx.fillRect(x + cellSize, y + cellSize, 5 * cellSize, 5 * cellSize);
        
        // Cuadrado negro interior
        ctx.fillStyle = '#000000';
        ctx.fillRect(x + 2 * cellSize, y + 2 * cellSize, 3 * cellSize, 3 * cellSize);
    }
    
    static simpleHash(str) {
        let hash = 0;
        for (let i = 0; i < str.length; i++) {
            const char = str.charCodeAt(i);
            hash = ((hash << 5) - hash) + char;
            hash = hash & hash; // Convert to 32-bit integer
        }
        return Math.abs(hash);
    }
}

// Exportar para uso global
window.SimpleQRGenerator = SimpleQRGenerator;
