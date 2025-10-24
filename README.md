# Leihlokal Website

A modern, file-based CMS website for Leihlokal - a community lending and borrowing service. Built with Kirby CMS 4.x, Tailwind CSS, and modern JavaScript.

## Prerequisites

- **PHP**: 8.1.0 or higher (tested with 8.1, 8.2, 8.3)
- **PHP Extensions**: SimpleXML, ctype, curl, dom, filter, hash, iconv, json, libxml, mbstring, openssl
- **Node.js**: For building Tailwind CSS
- **Composer**: For PHP dependency management

## Installation

1. **Clone the repository**
   ```bash
   git clone <repository-url>
   cd leihfrontend
   ```

2. **Install PHP dependencies**
   ```bash
   composer install
   ```

3. **Install Node dependencies**
   ```bash
   npm install
   ```

4. **Build CSS**
   ```bash
   npm run build
   ```

5. **Start development server**
   ```bash
   composer start
   ```
   The site will be available at `http://localhost:8000`

6. **Access the admin panel**
   Navigate to `http://localhost:8000/panel` to set up your first admin account

## Development Workflow

### CSS Development
```bash
# Build Tailwind CSS (production)
npm run build

# Watch for changes during development
npm run watch
```

Tailwind processes `./assets/css/processing.css` and outputs to `./assets/css/tailwind.css`

### PHP Development
```bash
# Start local development server
composer start
```

The built-in PHP server runs on port 8000 by default.

## Project Structure

```
leihfrontend/
├── assets/
│   ├── css/          # Tailwind CSS files
│   ├── fonts/        # Univers font family
│   └── js/           # JavaScript files
├── content/          # File-based content (Kirby pages)
├── kirby/            # Kirby CMS core. Please update if possible.
├── media/            # Generated thumbnails/responsive images
├── site/
│   ├── blueprints/   # Content structure definitions
│   ├── controllers/  # Page logic/data processing
│   ├── snippets/     # Reusable template partials
│   ├── templates/    # Page rendering templates
│   └── config/       # Kirby configuration
└── vendor/           # PHP dependencies
```

## Key Features

### Events Management
- Dynamic events system with structured data storage
- **Local Events**: Managed through Kirby Panel
- **iCal Import**: Automatic import from external iCal (.ics) sources
- Downloadable .ics calendar files for individual events
- Featured event highlighting
- Automatic categorization (upcoming/past events)

### Custom Design
- **Tailwind CSS** with custom `leihlokal` color palette (50-950 shades in oklch format)
- **Univers Font Family** with multiple weights and condensed variants
- Typography plugin for rich text formatting
- Responsive design with custom breakpoints

### Interactive Elements
- Animated statistics counter (anime.js)
- Image lightbox galleries (Fancybox, Lightbox2)
- Dynamic homepage image grid with random rotation

### Content Management
- File-based content (no database required)
- Flexible blueprints for different page types
- Structured data fields for repeatable content
- User-friendly Kirby Panel interface

## Available Page Templates

- **home** - Landing page with dynamic image grid and animated statistics
- **events** - Events listing with iCal import and export
- **leihlokal** - Information about lending locations
- **multipurpose** - Flexible content page
- **default** - Standard page template

## Configuration

Main configuration file: `site/config/config.php`

Key settings:
- Debug mode: Enabled for development
- Panel installation: Allowed

## Content Management

Content is stored in the `content/` directory as plain text files with a simple field: value format. Each page is a numbered folder containing a `.txt` file.

Example content structure:
```
content/
├── 1_ll/
│   └── default.txt
├── 2_frei-raume/
│   └── default.txt
├── 3_cal/
│   └── events.txt
└── site.txt
```

Site-wide data (like events) is stored in `content/site.txt` as structured fields.

## JavaScript Libraries

- **jQuery** (3.7.1) - DOM manipulation
- **Fancybox UI** (5.0.36) - Modal dialogs and lightboxes
- **Lightbox2** - Image galleries
- **anime.js** - Animation engine for statistics counter

## Deployment

For production deployment:

1. Build optimized CSS: `npm run build`
2. Ensure PHP 8.1+ is available on the server
3. Set appropriate file permissions for `content/`, `site/cache/`, and `media/`
4. Disable debug mode in `site/config/config.php`
5. Configure web server (Apache/Nginx) to point to the project root

## Learn More

- [Kirby CMS Documentation](https://getkirby.com/docs)
- [Tailwind CSS Documentation](https://tailwindcss.com/docs)
- [Project-specific instructions](./CLAUDE.md)

## License

For licensing information, please refer to the project maintainer.
