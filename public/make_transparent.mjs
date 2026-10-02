import { Jimp } from 'jimp';

async function processImage(filename, outname) {
    try {
        const image = await Jimp.read(filename);
        
        // Get bg color from (0,0)
        const bgColor = image.getPixelColor(0, 0);
        // Extract RGBA from 32-bit int
        const bgR = (bgColor >> 24) & 255;
        const bgG = (bgColor >> 16) & 255;
        const bgB = (bgColor >> 8) & 255;
        
        image.scan(0, 0, image.bitmap.width, image.bitmap.height, function (x, y, idx) {
            const r = this.bitmap.data[idx + 0];
            const g = this.bitmap.data[idx + 1];
            const b = this.bitmap.data[idx + 2];
            
            const dist = Math.sqrt(Math.pow(r - bgR, 2) + Math.pow(g - bgG, 2) + Math.pow(b - bgB, 2));
            
            if (dist < 40) {
                this.bitmap.data[idx + 3] = 0; // Transparent
            } else if (dist >= 40 && dist < 100) {
                // Anti-alias edge
                const alpha = Math.floor(255 * ((dist - 40) / 60));
                this.bitmap.data[idx + 3] = alpha;
            }
        });
        
        await image.write(outname);
        console.log(`Saved transparent image to ${outname}`);
    } catch (err) {
        console.error('Error processing ' + filename, err);
    }
}

async function main() {
    await processImage('public/image/prysmian_transparent.png', 'public/image/prysmian_transparent_clean.png');
    await processImage('public/image/zero_beyond.png', 'public/image/zero_beyond_clean.png');
}

main();
