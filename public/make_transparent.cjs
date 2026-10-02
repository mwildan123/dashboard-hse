const Jimp = require('jimp');

async function processImage(filename, outname) {
    try {
        const image = await Jimp.read(filename);
        
        // Get background color from top-left pixel (0,0)
        const bgColor = image.getPixelColor(0, 0);
        const bgR = Jimp.intToRGBA(bgColor).r;
        const bgG = Jimp.intToRGBA(bgColor).g;
        const bgB = Jimp.intToRGBA(bgColor).b;
        
        console.log(`Processing ${filename}. Background color: RGB(${bgR}, ${bgG}, ${bgB})`);

        image.scan(0, 0, image.bitmap.width, image.bitmap.height, function (x, y, idx) {
            const r = this.bitmap.data[idx + 0];
            const g = this.bitmap.data[idx + 1];
            const b = this.bitmap.data[idx + 2];
            
            // Calculate distance
            const distance = Math.sqrt(Math.pow(r - bgR, 2) + Math.pow(g - bgG, 2) + Math.pow(b - bgB, 2));
            
            if (distance < 45) { // If it's close to background
                // Make transparent
                this.bitmap.data[idx + 3] = 0; 
            } else if (distance >= 45 && distance < 80) {
                // Anti-aliasing / edge feathering
                const alpha = Math.floor(255 * ((distance - 45) / 35));
                this.bitmap.data[idx + 3] = alpha;
            }
        });
        
        await image.writeAsync(outname);
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
