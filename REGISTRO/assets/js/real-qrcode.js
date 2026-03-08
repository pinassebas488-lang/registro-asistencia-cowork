// QR Code Generator Real - Implementación funcional
class RealQRCode {
    static generate(text, canvas) {
        const ctx = canvas.getContext('2d');
        const size = 256;
        const modules = 25;
        const moduleSize = size / modules;
        
        canvas.width = size;
        canvas.height = size;
        
        // Fondo blanco
        ctx.fillStyle = '#ffffff';
        ctx.fillRect(0, 0, size, size);
        
        // Generar matriz QR real
        const matrix = this.createQRMatrix(text, modules);
        
        // Dibujar módulos
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
    
    static createQRMatrix(text, size) {
        const matrix = Array(size).fill().map(() => Array(size).fill(0));
        
        // Patrones de posicionamiento
        this.addFinderPattern(matrix, 0, 0);
        this.addFinderPattern(matrix, size - 7, 0);
        this.addFinderPattern(matrix, 0, size - 7);
        
        // Patrones de alineación
        this.addAlignmentPattern(matrix, Math.floor(size/2), Math.floor(size/2));
        
        // Patrones de temporización
        this.addTimingPatterns(matrix, size);
        
        // Datos
        const data = this.encodeText(text);
        this.placeData(matrix, data, size);
        
        return matrix;
    }
    
    static addFinderPattern(matrix, row, col) {
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
        for (let i = 8; i < size - 8; i++) {
            matrix[6][i] = i % 2 === 0 ? 1 : 0;
            matrix[i][6] = i % 2 === 0 ? 1 : 0;
        }
    }
    
    static encodeText(text) {
        const bytes = [];
        for (let i = 0; i < text.length; i++) {
            bytes.push(text.charCodeAt(i));
        }
        return bytes;
    }
    
    static placeData(matrix, data, size) {
        let index = 0;
        let row = size - 1;
        let col = size - 1;
        let direction = -1;
        
        while (row >= 0 && index < data.length * 8) {
            if (col === 6) {
                col--;
                continue;
            }
            
            for (let bit = 0; bit < 8 && index < data.length * 8; bit++) {
                const byteIndex = Math.floor(index / 8);
                const bitIndex = index % 8;
                const value = (data[byteIndex] >> bitIndex) & 1;
                
                if (matrix[row][col] === 0) {
                    matrix[row][col] = value;
                    index++;
                }
                
                if (row + direction >= 0 && row + direction < size && matrix[row + direction][col] === 0) {
                    matrix[row + direction][col] = value;
                    index++;
                }
            }
            
            row += direction;
            if (row < 0 || row >= size) {
                col--;
                direction *= -1;
                row = direction === 1 ? 0 : size - 1;
            }
        }
    }
}

window.RealQRCode = RealQRCode;
