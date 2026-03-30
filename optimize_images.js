const fs = require('fs');
const path = require('path');

// Check if sharp is installed, if not, provide a helpful message
let sharp;
try {
    sharp = require('sharp');
} catch (e) {
    console.error("Error: 'sharp' library is not installed. Please run 'npm install sharp' to use this script.");
    process.exit(1);
}

const tasks = [
    // Hero Poster
    { input: 'assets/hero-poster.png', output: 'assets/hero-poster.webp', width: 1024 },

    // Scrapbook Images
    { input: 'assets/images/1.png', output: 'assets/images/1.webp', width: 600 },
    { input: 'assets/images/2.png', output: 'assets/images/2.webp', width: 600 },
    { input: 'assets/images/3.png', output: 'assets/images/3.webp', width: 600 },
    { input: 'assets/images/4.png', output: 'assets/images/4.webp', width: 600 },
    { input: 'assets/images/5.png', output: 'assets/images/5.webp', width: 600 },
    { input: 'assets/images/6.png', output: 'assets/images/6.webp', width: 600 },
    { input: 'assets/images/7.png', output: 'assets/images/7.webp', width: 600 },

    // Icons
    { input: 'assets/food-truck-top-view.png', output: 'assets/food-truck-top-view.webp', width: 300 }
];

async function optimizeImages() {
    console.log("Starting image optimization...");

    for (const task of tasks) {
        const inputPath = path.join(__dirname, task.input);
        const outputPath = path.join(__dirname, task.output);

        if (fs.existsSync(inputPath)) {
            try {
                const image = sharp(inputPath);
                const metadata = await image.metadata();

                // Resize only if wider than target
                if (metadata.width > task.width) {
                    image.resize(task.width);
                }

                // Convert to WebP
                await image
                    .webp({ quality: 80 })
                    .toFile(outputPath);

                const statsIn = fs.statSync(inputPath);
                const statsOut = fs.statSync(outputPath);

                console.log(`Optimized ${task.input} -> ${task.output} (${(statsIn.size / 1024).toFixed(2)}KB -> ${(statsOut.size / 1024).toFixed(2)}KB)`);
            } catch (err) {
                console.error(`Error processing ${task.input}:`, err);
            }
        } else {
            console.log(`Skipping ${task.input}, file not found.`);
        }
    }
    console.log("Optimization complete.");
}

optimizeImages();
