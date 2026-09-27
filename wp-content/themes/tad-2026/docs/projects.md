# Projects (portfolio)

Projects are a post type (**Projects** in the admin menu) with tags and categories.
The **Work Grid** block on the home page shows them as cards.

## Per-project settings
- **Screenshot / artwork** = the featured image. Its own shape sets the card's height.
- **Card tab:** image on top or side · size Standard / Wide / Feature (the "highlight") · placeholder art + shape (only used until you add an image).
- **Link tab:** live-project URL (empty = no link anywhere) · link text · year · role · client.
- Tags: the **Tags** box. Categories: the **Categories** box.

## The switch: Site Options › Projects › "Project detail pages & archives"
- **Off (default; use this at launch):** cards only. A card links straight to the live site (new tab, icon + "opens in a new tab" for screen readers) or has no link. `/work/`, project pages and tag/category archives return 404. Tag chips are plain text.
- **On:** every project gets a page (`/work/name/`), plus `/work/`, `/work/tag/x/`, `/work/category/x/`. Cards link to the project page; the live site becomes a separate "visit" link. Single pages show tags, categories, meta, prev/next and similar projects.
- Also there: archive layout (masonry grid or list/detail), optional visitor grid/list switch, projects per page, archive heading + intro, number of similar projects.
- Preview the other archive layout any time with `?view=list` or `?view=grid`.
- `define( 'TAD_PROJECT_DETAIL', true );` in wp-config.php overrides the switch (handy for local dev vs live).

## Work Grid block
Order (random / newest / manual), how many, only certain categories, or hand-picked projects. "Random" is re-shuffled on every page load unless a page cache holds the page.

## Masonry + tilt
`assets/src/js/modules/masonry.js` (row-spans from real card heights; plain grid without JS) and `tilt.js` (image frame tilts more than the card; mouse only; off for reduced motion and the pause-motion button). All styling: `assets/src/css/blocks/work.css`.

## Starter content
Tools › Seed Home Page › "Create starter projects" makes the design's ten cards as real projects (skips titles that exist).
