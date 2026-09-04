# Urban Care Content Management Implementation Plan

<!-- markdownlint-disable MD024 MD032 -->

> **Status:** Approved for implementation.
>
> **Design source:**
> - `docs/superpowers/specs/2026-09-04-content-management-design.md`

## Objective

Scaffold a native WordPress editorial system for the Urban Care Project with seven managed content types, shared taxonomies, hybrid Gutenberg and structured-field editing, a seeded canonical Project draft, safe lifecycle behavior, and normalized published-content REST endpoints.

## Guardrails

- Work only in the independently versioned `backend/urbancareproject` repository.
- Preserve the current plugin bootstrap and Git metadata.
- Do not rerun `setup-plugin.sh`; it is an initial bootstrap artifact with overwrite protection.
- Do not connect or modify the Next.js frontend in this implementation.
- Do not import all client archive records or images.
- Never expose drafts, hidden email, secrets, or unverified coordinates publicly.
- Do not commit unless explicitly requested.
- Run PHP syntax and bootstrap checks after each implementation slice.

## Task 1: Register Content Types And Taxonomies

**Create:**
- `includes/content/class-urbancareproject-content-types.php`

**Modify:**
- `includes/class-urbancareproject.php`
- `includes/class-urbancareproject-activator.php`
- `includes/admin/class-urbancareproject-admin.php`

### Steps

1. Centralize registration for Project, Activities, Publications, Team Members, Partners, Study Sites, and Field Stories.
2. Register Research Themes, Research Methods, and Activity Types against the appropriate post types.
3. Enable Gutenberg, revisions, featured media, excerpts, authors, dates, archives, and core REST only where specified by the design.
4. Put all content types under the Urban Care menu.
5. Remove duplicate CPT registration from the admin class.
6. Register content before admin and REST hooks and during activation.
7. Run PHP syntax and bootstrap checks.

### Checkpoint

All seven post types and three taxonomies register from one owner without duplicate hooks.

## Task 2: Add Structured Metadata And Admin Editing

**Create:**
- `includes/content/class-urbancareproject-metadata.php`
- `includes/admin/class-urbancareproject-fields.php`

**Modify:**
- `includes/class-urbancareproject.php`
- `includes/admin/class-urbancareproject-admin.php`

### Steps

1. Define structured field schemas once, including field type, post types, defaults, REST schema, and sanitizer.
2. Register metadata with capability-aware authorization callbacks.
3. Implement sanitizers for text, text areas, rich text, URLs, email, dates, integers, booleans, coordinates, ID arrays, and string arrays.
4. Render accessible type-specific meta boxes for simple fields, media IDs, repeatable line arrays, and relationship IDs.
5. Save only fields belonging to the current post type after nonce, capability, autosave, and revision checks.
6. Preserve Gutenberg as the narrative editor.
7. Run PHP syntax, metadata sanitizer, and bootstrap checks.

### Checkpoint

Editors can manage every structured field, and invalid values are normalized before storage.

## Task 3: Add Singleton Project And Populated Draft

**Create:**
- `includes/content/class-urbancareproject-seeder.php`

**Modify:**
- `includes/class-urbancareproject-activator.php`
- `includes/class-urbancareproject.php`
- `includes/admin/class-urbancareproject-admin.php`
- `uninstall.php`

### Steps

1. Seed default research themes, methods, and activity types by stable slugs.
2. Create one draft Project from the official longer-version source when no canonical record exists.
3. Seed the short summary, funding statement, objectives, methodology, participation, study-area overview, and SEO description.
4. Store the canonical Project ID and seed marker as plugin options.
5. Make activation idempotent and preserve all editor changes on reactivation.
6. Add a Project Content submenu that routes to the canonical record.
7. Remove Add New affordances and reject additional Project creation while a canonical record exists.
8. Remove only plugin options on uninstall.
9. Run syntax, activation, seed-idempotency, and singleton checks.

### Checkpoint

First activation creates one populated draft; subsequent activation changes nothing.

## Task 4: Build Normalized Public REST API

**Create:**
- `includes/api/class-urbancareproject-serializer.php`

**Modify:**
- `includes/api/class-urbancareproject-rest-api.php`
- `includes/class-urbancareproject.php`

### Steps

1. Register collection routes for activities, publications, team, partners, study sites, and field stories.
2. Register item-by-slug routes for each collection and a singleton project route.
3. Add bounded page/per-page parameters and whitelisted taxonomy, type, date, featured, and relationship filters.
4. Serialize common fields, rendered narrative, excerpt, featured image, gallery, taxonomies, metadata, and compact relationships.
5. Omit hidden email and unverified coordinates.
6. Force published status and resolve published relationships only.
7. Return stable `400` and `404` REST errors.
8. Run PHP syntax, bootstrap, route-registration, privacy, coordinate, and pagination checks.

### Checkpoint

The public API exposes stable normalized records without WordPress internals or private data.

## Task 5: Final Verification

### Steps

1. Run `php -l` on every PHP file.
2. Run Bash syntax validation on `setup-plugin.sh`.
3. Load the plugin with WordPress API stubs to catch bootstrap and hook-order fatals.
4. Run focused stub checks for seven post types, three taxonomies, metadata schemas, seeded draft idempotency, and REST privacy rules.
5. Run `git diff --check` and inspect nested repository status.
6. Document the remaining real-WordPress activation walkthrough.

### Checkpoint

The scaffold is internally validated and ready to install in a WordPress test site.
