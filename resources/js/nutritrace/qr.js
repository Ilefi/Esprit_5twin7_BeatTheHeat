import QRCode from 'qrcode';

// Renders a QR code as inline SVG using currentColor, so it is colored by token classes (e.g. text-foreground).
export default function registerQr(Alpine) {
    Alpine.data('ntQr', (text) => ({
        init() {
            const { modules } = QRCode.create(text, { errorCorrectionLevel: 'M' });
            const quiet = 2;
            const size = modules.size + quiet * 2;
            let path = '';

            for (let y = 0; y < modules.size; y++) {
                for (let x = 0; x < modules.size; x++) {
                    if (modules.data[y * modules.size + x]) {
                        path += `M${x + quiet} ${y + quiet}h1v1h-1z`;
                    }
                }
            }

            this.$el.innerHTML = `<svg viewBox="0 0 ${size} ${size}" class="h-full w-full" shape-rendering="crispEdges" role="img" aria-label="QR code : ${text}"><path fill="currentColor" d="${path}"/></svg>`;
        },
    }));
}
