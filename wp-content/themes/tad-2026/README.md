# tracyappsdesign 2026

A classic, PHP-first WordPress starter theme for custom client builds.

This theme (built on the Start 2.0 starter) keeps template ownership in PHP, uses `theme.json` for shared design tokens and editor constraints, stores ACF field groups in local JSON, and replaces the old Gulp/Bower workflow with small Node scripts.

## Quick Start

```bash
cd app/public/wp-content/themes/tad-2026
npm install
npm run spinup
npm run build
npm run dev
```

`npm run dev` proxies the LocalWP site from `starter.config.json` and reloads when PHP, CSS, JS, SVG, `theme.json`, or ACF JSON files change.

## What This Theme Optimizes For

- Classic theme templates, not full-site editing.
- A curated block editor with fewer ways for clients to break layout systems.
- ACF Blocks through `block.json`, rendered in PHP.
- ACF local JSON instead of huge PHP imports.
- Shared frontend/editor styles from the same CSS source files.
- Feature flags for comments, widgets, patterns, dark mode, ACF blocks, Site Options, and optional CPTs.

Full development notes are in [docs/development.md](docs/development.md).
