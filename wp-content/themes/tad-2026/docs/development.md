# tracyappsdesign 2026 Development Notes

## Architecture

This theme is a classic WordPress theme. It intentionally avoids block theme templates and the Site Editor, while still using modern WordPress features where they help custom development:

- `theme.json` defines editor-safe palettes, typography, layout widths, and spacing presets.
- PHP templates own page structure.
- ACF local JSON owns fields, Site Options, and ACF block fields.
- ACF blocks use `block.json` and PHP render templates.
- The editor is curated through `allowed_block_types_all`, theme support removal, and small editor JS cleanup.

## Build Commands

```bash
npm install
npm run build
npm run dev
npm run spinup
```

`npm run build` generates:

- `assets/dist/css/main.css`
- `assets/dist/css/editor.css`
- `assets/dist/js/main.js`
- `assets/dist/js/editor.js`
- `assets/dist/svg/icons.svg`

`npm run dev` runs the same build, watches source files, and starts BrowserSync using `starter.config.json`.

Update the local proxy URL in `starter.config.json`:

```json
{
  "localUrl": "http://start.local"
}
```

## CSS Strategy

The theme uses plain CSS source files in `assets/src/css`. PostCSS handles imports, nesting support, modern color syntax, and minification.

Sass is not required. Native CSS now handles most of what this starter needs: custom properties, `clamp()`, `min()`, `max()`, `color-mix()`, cascade layering patterns, and media queries. If a project truly needs Sass later, it can be added without changing the PHP architecture.

Editor styles are generated from the same source token/base/layout/block files as the frontend. This avoids the old problem where the editor quietly drifts away from the public site.

## theme.json

Use `theme.json` as the contract between design and editing:

- Keep custom colors disabled unless a project needs them.
- Add only client-safe palette values.
- Add only spacing sizes you want clients to use.
- Keep typography choices intentionally narrow.
- Use CSS custom properties in `assets/src/css/tokens.css` to map `theme.json` presets into the frontend system.

This gives clients useful controls without handing them a full design system cockpit.

## ACF and ACFE

ACF local JSON is saved and loaded from `acf-json/`.

Included starter groups:

- `group_start_site_options.json`
- `group_start_page_header.json`
- `group_start_block_hero.json`

With ACFE Pro active, you can continue using ACFE's PHP or JSON sync tools. This starter defaults to JSON because it is easy to review, diff, and sync from the WordPress admin. If you prefer ACFE PHP AutoSync for a project, enable it in ACFE and point it at a versioned theme folder.

The theme checks that ACF exists before loading options or blocks, so missing plugins should not cause a white screen.

## Site Options

The Site Options page is registered in `inc/acf.php`.

Helpers live in `inc/site-options.php`, including:

- copyright text with `[year]`
- contact info
- social links
- excerpt settings
- default thumbnail fallback
- default light/dark/system theme mode

Add project-specific fields to the local JSON group in the admin, then sync the generated JSON file into version control.

## Blocks

ACF blocks live in `blocks/{block-name}`.

Each block should have:

- `block.json`
- a PHP render template
- optional block-specific CSS in `assets/src/css/blocks.css` or a dedicated imported file
- an ACF JSON field group located by block name

The included Hero block demonstrates the pattern.

## Spinup

Run:

```bash
npm run spinup
```

The script updates:

- `starter.config.json`
- `inc/starter-config.php`

Feature flags control comments, widgets, patterns, ACF blocks, Site Options, dark mode, and optional CPTs.

Optional CPTs are registered in `inc/post-types.php`. The starter includes placeholders for:

- People
- Work
- Events

Add more CPT definitions there, then expose them through `starter.config.json` and `scripts/spinup.mjs`.

## Editor Curation

The theme disables remote patterns and removes core pattern support when the `patterns` feature is false. The core block list in `inc/editor.php` stays curated, including Group (and its Row/Stack variations), Columns, Media & Text, and Cover. Registered plugin blocks are admitted automatically. Earlier filters returning a restricted array or `false` are respected. Accent Section inherits this editor policy instead of maintaining a second child-block list.

This does not disable the block editor. It keeps the useful editing canvas while hiding the clutter that usually confuses non-technical clients.

## Light and Dark Mode

Light/dark mode uses CSS custom properties and the `color-scheme` property. The visitor toggle stores a preference in `localStorage`; if no preference exists, the site respects the Site Options default and then system preference.

Most projects only need to edit tokens in `assets/src/css/tokens.css`.

## SVG Icons

Place original SVGs in:

```text
assets/src/svg/originals
```

The build script optimizes them and creates:

```text
assets/dist/svg/icons.svg
```

Use an icon like:

```html
<svg aria-hidden="true" focusable="false" class="icon"><use href="#icon-instagram"></use></svg>
```

Always pair icon-only links/buttons with accessible text.

## Accessibility Defaults

The starter includes:

- skip link
- semantic landmarks
- accessible menu toggle state
- visible focus styles
- reduced-motion handling
- alt text through WordPress image functions
- screen-reader text utility
- conservative color token defaults

Project-specific blocks should preserve heading order, button/link semantics, keyboard access, and sufficient contrast.

## Inside pages and spacing

On pages using the default template, the **Page layout** ACF sidebar contains:

- **Content width:** Standard (existing site width), Reading (56rem), or Compact (44rem).
- **Page spacing:** Standard (existing rhythm), Compact, or Generous. This controls the title area and space before the footer.

Existing pages keep their current appearance until a choice is saved. ACF local JSON provides the new fields; sync `Page layout` under ACF if the site asks for a sync.

In the block sidebar, use **Dimensions** for padding and margins on supported blocks. Presets include None through XL, and custom values are enabled. Group blocks also provide **Inset panel** (rounded surface with responsive padding) and **Reading width** styles. Saved padding/margins override the panel defaults. Meet With Me blocks can be wrapped in a Group for spacing because the plugin does not currently declare its own spacing supports.

Accent Section supports padding and top/bottom margins. Its wavy edges remain separate; use its existing glass transition settings to change wave height. The ACF editor supplies native support attributes on its own wrapper, while frontend rendering merges those attributes with the glass variables.

For a small discovery-call page, try Reading width + Generous page spacing, place the introduction in a Reading width Group, and wrap the booking form in an Inset panel Group with MD or LG padding. These are editor choices, not changes to existing page content.

## Focused editor verification

With the LocalWP database socket configured:

```bash
wp eval-file wp-content/themes/tad-2026/tests/editor-integration.php
```

This runs against WordPress and ACF, checking plugin/layout availability, upstream restrictions, native spacing output, Group styles, and saved page choices. It creates and removes one isolated draft fixture. It does not contact booking providers.
