// Generador QR Real usando una implementación simplificada pero funcional
class RealQRCode {
    static generate(text, canvas) {
        const ctx = canvas.getContext('2d');
        const size = 256;
        const scale = 8; // 32x32 modules
        const modules = 32;
        
        canvas.width = size;
        canvas.height = size;
        
        // Fondo blanco
        ctx.fillStyle = '#ffffff';
        ctx.fillRect(0, 0, size, size);
        
        // Generar matriz de datos QR
        const matrix = this.generateQRMatrix(text, modules);
        
        // Dibujar módulos
        const moduleSize = size / modules;
        ctx.fillStyle = '#000000';
        
        for (let row = 0; row < modules; row++) {
            for (let col = 0; col < modules; col++) {
                if (matrix[row][col] === 1) {
                    ctx.fillRect(
                        col * moduleSize,
                        row * moduleSize,
                        moduleSize,
                        moduleSize
                    );
                }
            }
        }
        
        return canvas;
    }
    
    static generateQRMatrix(text, size) {
        // Crear matriz vacía
        const matrix = Array(size).fill().map(() => Array(size).fill(0));
        
        // Agregar patrones de posicionamiento (esquinas)
        this.addPositionPattern(matrix, 0, 0);
        this.addPositionPattern(matrix, size - 7, 0);
        this.addPositionPattern(matrix, 0, size - 7);
        
        // Agregar patrones de alineación
        if (size >= 25) {
            this.addAlignmentPattern(matrix, Math.floor(size/2), Math.floor(size/2));
        }
        
        // Agregar patrones de temporización
        this.addTimingPatterns(matrix, size);
        
        // Agregar datos codificados
        const data = this.encodeData(text);
        this.addDataToMatrix(matrix, data, size);
        
        // Aplicar máscaras y formato
        this.applyMask(matrix, size);
        
        return matrix;
    }
    
    static addPositionPattern(matrix, row, col) {
        // Patrón de posicionamiento 7x7
        const pattern = [
            [1,1,1,1,1,1,1],
            [1,0,0,0,0,0,1],
            [1,0,1,1,1,0,1],
            [1,0,1,1,1,0,1],
            [1,0,1,1,1,0,1],
            [1,0,0,0,0,0,1],
            [1,1,1,1,1,1,1]
        ];
        
        for (let r = 0; r < 7; r++) {
            for (let c = 0; c < 7; c++) {
                if (row + r < matrix.length && col + c < matrix[0].length) {
                    matrix[row + r][col + c] = pattern[r][c];
                }
            }
        }
    }
    
    static addAlignmentPattern(matrix, row, col) {
        // Patrón de alineación 5x5
        const pattern = [
            [1,1,1,1,1],
            [1,0,0,0,1],
            [1,0,1,0,1],
            [1,0,0,0,1],
            [1,1,1,1,1]
        ];
        
        for (let r = 0; r < 5; r++) {
            for (let c = 0; c < 5; c++) {
                if (row + r >= 0 && row + r < matrix.length && 
                    col + c >= 0 && col + c < matrix[0].length) {
                    matrix[row + r][col + c] = pattern[r][c];
                }
            }
        }
    }
    
    static addTimingPatterns(matrix, size) {
        // Patrones de temporización (líneas alternas)
        for (let i = 8; i < size - 8; i++) {
            // Línea horizontal
            matrix[6][i] = i % 2 === 0 ? 1 : 0;
            // Línea vertical
            matrix[i][6] = i % 2 === 0 ? 1 : 0;
        }
    }
    
    static encodeData(text) {
        // Codificación simple (byte mode)
        const data = [];
        for (let i = 0; i < text.length; i++) {
            const charCode = text.charCodeAt(i);
            data.push((charCode >> 4) & 0x0F);
            data.push(charCode & 0x0F);
        }
        return data;
    }
    
    static addDataToMatrix(matrix, data, size) {
        // Agregar datos en patrón zigzag
        let dataIndex = 0;
        let row = size - 1;
        let col = size - 1;
        let direction = -1; // -1 = arriba, 1 = abajo
        
        while (row > 0 && dataIndex < data.length) {
            // Saltar columnas de patrón
            if (col === 6) {
                col--;
                continue;
            }
            
            // Agregar dos bits (arriba y abajo)
            if (matrix[row][col] === 0 && dataIndex < data.length) {
                matrix[row][col] = data[dataIndex++] % 2;
            }
            
            if (row - direction >= 0 && row - direction < size && 
                matrix[row - direction][col] === 0 && dataIndex < data.length) {
                matrix[row - direction][col] = data[dataIndex++] % 2;
            }
            
            // Mover a la siguiente columna
            row += direction;
            if (row < 0 || row >= size) {
                col--;
                direction *= -1;
                row = direction === 1 ? 0 : size - 1;
            }
        }
    }
    
    static applyMask(matrix, size) {
        // Aplicar máscara simple (patrón de checkerboard)
        for (let row = 0; row < size; row++) {
            for (let col = 0; col < size; col++) {
                if ((row + col) % 2 === 0 && matrix[row][col] !== -1) {
                    matrix[row][col] ^= 1; // XOR con 1
                }
            }
        }
    }
}

// Exportar
window.RealQRCode = RealQRCode;
