# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

This is a Kirby CMS-based website for "Leihlokal" (a community lending/borrowing service), built with PHP, Tailwind CSS, and modern JavaScript. The site uses Kirby 4.x as the CMS backend with custom templates, blueprints, and controllers.

## Development Commands

### CSS/Tailwind
- **Build CSS**: `npm run build` - Compiles Tailwind CSS with minification
- **Watch CSS**: `npm run watch` - Watches for changes and recompiles Tailwind CSS
- **Input**: `./assets/css/processing.css`
- **Output**: `./assets/css/tailwind.css`

### PHP/Kirby
- **Start development server**: `composer start` - Starts PHP built-in server at `localhost:8000`
- **Install dependencies**: `composer install`

### PHP Requirements
- PHP 8.1.0 or higher (compatible with 8.1, 8.2, 8.3)
- Required extensions: SimpleXML, ctype, curl, dom, filter, hash, iconv, json, libxml, mbstring, openssl

## Architecture

### Kirby CMS Structure

**Content Management**:
- Content is file-based in `content/` directory
- Each page has a numbered folder (e.g., `1_ll`, `2_frei-raume`, `3_cal`, `4_about`)
- Page data stored in `.txt` files with field: value format
- Site-wide data (like events) stored in `content/site.txt` as structured fields

**Blueprints** (`site/blueprints/pages/`):
- Define content structure for the Panel (Kirby's admin interface)
- Each template has a corresponding blueprint YAML file
- Templates available: default, events, home, leihlokal, multipurpose

**Templates** (`site/templates/`):
- PHP files that render page content
- Access page data via `$page`, site data via `$site`, Kirby instance via `$kirby`
- Template is selected based on page blueprint/intendedTemplate

**Controllers** (`site/controllers/`):
- PHP files that process data before passing to templates
- Currently implements: `events.php` (handles iCal generation from form submissions)
- Controllers return associationally-indexed arrays that become variables in templates

**Snippets** (`site/snippets/`):
- Reusable template partials (header.php, footer.php, blocks/)
- Included via `<?php snippet('name') ?>`

### Frontend Stack

**Tailwind CSS**:
- Custom color palette: `leihlokal` (50-950 shades in oklch format)
- Custom fonts: Univers (multiple weights and condensed variants)
- Typography plugin enabled
- Utility class `.uni-cd` for condensed Univers font

**JavaScript Libraries**:
- jQuery (3.7.1)
- Fancybox UI (5.0.36) - for lightboxes/modals
- Lightbox2 (via CDN) - image galleries
- anime.js (via CDN) - animations on home page

**Assets Structure**:
- `assets/css/` - Tailwind input/output files, page-specific CSS
- `assets/fonts/` - Univers font family (woff2 format)
- `assets/js/` - JavaScript files

### Key Features

**Events System**:
- Events stored as structured data in `content/site.txt`
- Fields: title, date_start, date_end, location, address, description, registration_required, registration_link, featured
- Events controller (`site/controllers/events.php`) generates downloadable .ics (iCal) files from POST requests
- iCal generation includes validation, sanitization, and error handling
- **iCal Import**: Events page blueprint includes `ical_url` field for importing events from external iCal (.ics) sources
- Events template (`site/templates/events.php`) merges events from both Kirby structure and external iCal sources
- iCal parser handles standard date formats (YYYYMMDD and YYYYMMDDTHHMMSSZ)
- Merged events are sorted by date and categorized as upcoming/past and featured/regular

**Home Page**:
- Dynamic image grid with random rotation from `immies` field
- Animated statistics counter using anime.js
- Stats defined as structured data in page content
- Responsive layout with custom Tailwind breakpoints

**Content Types**:
- Page titles broken into three words (firstword, secondword, thirdword) for stylistic layout
- Image galleries with lightbox functionality
- Structured data support for repeatable content blocks

## Configuration

**Kirby Config** (`site/config/config.php`):
- Debug mode enabled
- Panel installation allowed

**Git Ignored**:
- `/site/accounts/*` - User accounts
- `/site/cache/*` - Page cache
- `/site/sessions/*` - Session data
- `/media/*` - Generated thumbnails/responsive images

## Important Notes

- Kirby uses file-based content management - no database required
- The Panel admin interface is accessible at `/panel` (requires installation on first run)
- Template selection is automatic based on page's intended template
- Page-specific CSS loaded via header snippet: `assets/css/pages/{template}.css`
- Recent commits show reservation logic was added, then authentication removed
