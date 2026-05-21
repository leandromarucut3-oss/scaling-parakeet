const fs = require('fs');
const path = require('path');
const puppeteer = require('puppeteer');

async function render(htmlPath, pdfPath, pngPath) {
  if (!fs.existsSync(htmlPath)) {
    console.error('HTML file not found:', htmlPath);
    process.exit(2);
  }

  const browser = await puppeteer.launch({ args: ['--no-sandbox', '--disable-setuid-sandbox'] });
  try {
    const page = await browser.newPage();
    const url = 'file://' + path.resolve(htmlPath);
    await page.goto(url, { waitUntil: 'networkidle0' });

    // PDF
    if (pdfPath) {
      await page.pdf({ path: pdfPath, format: 'A4', printBackground: true, landscape: true });
    }

    // PNG screenshot
    if (pngPath) {
      // set a high resolution to improve quality
      await page.setViewport({ width: 1400, height: 900, deviceScaleFactor: 2 });
      await page.screenshot({ path: pngPath, fullPage: true });
    }

    await browser.close();
    process.exit(0);
  } catch (err) {
    console.error('Renderer error:', err);
    await browser.close();
    process.exit(3);
  }
}

if (require.main === module) {
  const [,, htmlPath, pdfPath, pngPath] = process.argv;
  render(htmlPath, pdfPath, pngPath);
}
