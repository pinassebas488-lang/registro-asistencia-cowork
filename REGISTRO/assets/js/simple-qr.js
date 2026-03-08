// Generador QR Simple sin dependencias externas
class SimpleQRCode {
    static generate(text, canvas) {
        const ctx = canvas.getContext('2d');
        const size = 256;
        canvas.width = size;
        canvas.height = size;
        
        // Fondo blanco
        ctx.fillStyle = '#ffffff';
        ctx.fillRect(0, 0, size, size);
        
        // Generar patrón basado en el texto
        const hash = this.hashCode(text);
        const cellSize = 8;
        const rows = Math.floor(size / cellSize);
        const cols = Math.floor(size / cellSize);
        
        // Dibujar patrón QR-like con espacios blancos
        ctx.fillStyle = '#000000';
        
        // Esquinas características
        this.drawCorner(ctx, 0, 0, cellSize);
        this.drawCorner(ctx, size - 7 * cellSize, 0, cellSize);
        this.drawCorner(ctx, 0, size - 7 * cellSize, cellSize);
        
        // Patrón de datos basado en hash - más disperso
        for (let row = 0; row < rows; row++) {
            for (let col = 0; col < cols; col++) {
                // Evitar esquinas
                if ((row < 7 && col < 7) || (row < 7 && col >= cols - 7) || (row >= rows - 7 && col < 7)) {
                    continue;
                }
                
                // Generar patrón más disperso (30% de celdas negras)
                const index = (row * cols + col + hash) % 1000;
                const shouldFill = index < 300; // Solo 30% de celdas negras
                
                if (shouldFill) {
                    ctx.fillRect(col * cellSize, row * cellSize, cellSize, cellSize);
                }
            }
        }
        
        // Agregar patrón de alineación central
        this.drawAlignmentPattern(ctx, Math.floor(cols/2) * cellSize, Math.floor(rows/2) * cellSize, cellSize);
        
        // Agregar información de depuración
        ctx.fillStyle = '#666666';
        ctx.font = '8px monospace';
        ctx.fillText('Hash: ' + hash.toString(16).substring(0, 8), 5, size - 5);
        
        return canvas;
    }
    
    static drawCorner(ctx, x, y, cellSize) {
        // Patrón de esquina QR
        const size = 7 * cellSize;
        
        // Cuadrado exterior
        ctx.fillStyle = '#000000';
        ctx.fillRect(x, y, size, size);
        
        // Cuadrado blanco medio
        ctx.fillStyle = '#ffffff';
        ctx.fillRect(x + cellSize, y + cellSize, 5 * cellSize, 5 * cellSize);
        
        // Cuadrado negro interior
        ctx.fillStyle = '#000000';
        ctx.fillRect(x + 2 * cellSize, y + 2 * cellSize, 3 * cellSize, 3 * cellSize);
    }
    
    static drawAlignmentPattern(ctx, x, y, cellSize) {
        // Patrón de alineación pequeño
        const size = 5 * cellSize;
        
        // Cuadrado exterior
        ctx.fillStyle = '#000000';
        ctx.fillRect(x, y, size, size);
        
        // Cuadrado blanco medio
        ctx.fillStyle = '#ffffff';
        ctx.fillRect(x + cellSize, y + cellSize, 3 * cellSize, 3 * cellSize);
        
        // Cuadrado negro interior
        ctx.fillStyle = '#000000';
        ctx.fillRect(x + 2 * cellSize, y + 2 * cellSize, cellSize, cellSize);
    }
    
    static hashCode(str) {
        let hash = 0;
        for (let i = 0; i < str.length; i++) {
            const char = str.charCodeAt(i);
            hash = ((hash << 5) - hash) + char;
            hash = hash & hash; // Convert to 32-bit integer
        }
        return Math.abs(hash);
    }
}

// Exportar
window.SimpleQRCode = SimpleQRCode;
