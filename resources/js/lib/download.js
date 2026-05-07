// Download primitive.
//
// Registers Alpine.magic('download'):
//   $download(blobOrText, filename, mime?)
//
// blobOrText can be a Blob, ArrayBuffer, Uint8Array, or string. If a string
// is passed and no mime is provided, defaults to 'text/plain'.

export function registerDownload(Alpine) {
    Alpine.magic('download', () => (data, filename, mime) => {
        if (data == null || !filename) return;

        let blob;
        if (data instanceof Blob) {
            blob = data;
        } else if (data instanceof ArrayBuffer || ArrayBuffer.isView(data)) {
            blob = new Blob([data], { type: mime || 'application/octet-stream' });
        } else {
            blob = new Blob([String(data)], { type: mime || 'text/plain' });
        }

        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = filename;
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        URL.revokeObjectURL(url);
    });
}
