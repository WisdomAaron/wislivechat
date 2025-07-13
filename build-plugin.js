#!/usr/bin/env node

/**
 * WisChat Plugin Builder
 * 
 * This script packages the WordPress plugin for distribution
 */

const fs = require('fs');
const path = require('path');
const archiver = require('archiver');
const { execSync } = require('child_process');

const PLUGIN_DIR = 'wordpress-plugin';
const BUILD_DIR = 'dist';
const PLUGIN_NAME = 'wischat';

console.log('🔨 Building WisChat WordPress Plugin...');

// Create build directory
if (!fs.existsSync(BUILD_DIR)) {
    fs.mkdirSync(BUILD_DIR, { recursive: true });
}

// Clean previous builds
const pluginZip = path.join(BUILD_DIR, `${PLUGIN_NAME}.zip`);
if (fs.existsSync(pluginZip)) {
    fs.unlinkSync(pluginZip);
}

// Create zip archive
const output = fs.createWriteStream(pluginZip);
const archive = archiver('zip', {
    zlib: { level: 9 } // Maximum compression
});

output.on('close', function() {
    console.log(`✅ Plugin packaged: ${archive.pointer()} total bytes`);
    console.log(`📦 File: ${pluginZip}`);
    console.log('');
    console.log('🚀 Distribution Instructions:');
    console.log('1. Upload to your website or marketplace');
    console.log('2. Users can install via WordPress Admin → Plugins → Upload');
    console.log('3. Or extract to wp-content/plugins/ directory');
    console.log('');
    console.log('💡 Next Steps:');
    console.log('- Set up license server for validation');
    console.log('- Create customer portal for downloads');
    console.log('- Set up payment processing');
});

archive.on('error', function(err) {
    throw err;
});

archive.pipe(output);

// Add plugin files
archive.directory(PLUGIN_DIR, PLUGIN_NAME);

// Finalize the archive
archive.finalize();

// Generate installation instructions
const instructions = `
# WisChat WordPress Plugin Installation

## Automatic Installation (Recommended)

1. Download the plugin zip file: wischat.zip
2. Log in to your WordPress admin panel
3. Go to Plugins → Add New → Upload Plugin
4. Choose the wischat.zip file and click "Install Now"
5. Activate the plugin
6. Go to WisChat → Settings to configure

## Manual Installation

1. Download and extract the plugin zip file
2. Upload the 'wischat' folder to your /wp-content/plugins/ directory
3. Activate the plugin through the 'Plugins' menu in WordPress
4. Go to WisChat → Settings to configure

## Configuration

1. **Get API Credentials**:
   - Deploy the WisChat backend server (see CLOUD_DEPLOYMENT.md)
   - Access the admin panel at: https://your-api-url.com/admin.html
   - Generate an API key for your WordPress site

2. **Configure Plugin**:
   - Go to WisChat → Settings in WordPress admin
   - Enter your API endpoint URL
   - Enter your API key
   - Test the connection
   - Customize widget appearance

3. **License Activation** (Commercial version):
   - Go to WisChat → License
   - Enter your license key
   - Click "Activate License"

## Support

- Documentation: https://docs.wischat.com
- Support: https://support.wischat.com
- Website: https://wischat.com

## Requirements

- WordPress 5.0 or higher
- PHP 7.4 or higher
- Active internet connection
- WisChat backend server (deployed separately)
`;

fs.writeFileSync(path.join(BUILD_DIR, 'INSTALLATION.md'), instructions);

console.log('📝 Installation instructions created: dist/INSTALLATION.md');
