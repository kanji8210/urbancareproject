# Urban Care Content Management Design

## Purpose

Urban Care needs a WordPress-native editorial system that can manage the project's long-form narrative, activities, publications, people, institutions, places, and field stories. The plugin will be the authoritative content source for the Next.js frontend, but this release establishes the CMS and normalized public API only. Frontend integration and bulk archive import are separate follow-up tasks.

The source model is grounded in the supplied project archive, especially "General information on the project - Longer version," the activity documents, team biographies and publications, institutional partner profiles, study-site material, and "From the Plot to the Particle."

## Editorial Principles

- Use WordPress's native add, edit, trash, restore, and permanent-delete workflows.
- Use Gutenberg for substantial narrative and structured metadata for values the frontend must interpret consistently.
- Preserve drafts and editorial revisions inside authenticated WordPress administration.
- Publish normalized, stable response shapes rather than exposing raw WordPress records to the frontend.
- Keep editorial records on deactivation and uninstall by default.
- Never publish unverified study-site coordinates.
- Avoid paid or required third-party field plugins in the first release.

## Content Types

### Project

`ucp_project` represents the canonical Urban Care project. It behaves as a singleton.

Native fields:

- Title
- Gutenberg body for the longer general narrative
- Excerpt for the short project description
- Featured image for the project hero
- Revisions

Structured fields:

- Funding statement
- Objectives as an ordered list
- Methodology summary
- Participation summary
- Study-area overview
- SEO description

The Project submenu opens the canonical record directly. WordPress must prevent creation of a second Project record after the canonical record exists.

### Activities

`ucp_activity` records dated fieldwork, school collaborations, science-society dialogue, public-policy work, and artistic activity.

Native fields:

- Title
- Gutenberg body
- Excerpt
- Featured image
- Revisions

Structured fields:

- Activity date
- Location label
- Gallery attachment IDs
- Related Study Site IDs
- Related Partner IDs

Taxonomies:

- Activity Types
- Research Themes
- Research Methods

### Publications

`ucp_publication` records project and team research outputs.

Native fields:

- Citation title
- Abstract in the editor
- Revisions

Structured fields:

- Author display string
- Publication date
- Publication type
- Journal or publisher
- DOI URL
- PDF attachment ID or external PDF URL
- Related Team Member IDs
- Featured flag

Taxonomies:

- Research Themes
- Research Methods

### Team Members

`ucp_team` records project researchers, community collaborators, artists, and coordinators.

Native fields:

- Person name as title
- Biography in the editor
- Portrait as featured image
- Revisions

Structured fields:

- Role
- Related Partner or institution ID
- Public email
- Public-email visibility flag
- Profile URL
- Additional links
- Display order

Public API responses omit email unless the visibility flag is enabled.

### Partners

`ucp_partner` records institutions, public authorities, and community organizations.

Native fields:

- Institution name as title
- Profile in the editor
- Logo as featured image
- Revisions

Structured fields:

- Website URL
- Project role
- Primary contact name
- Display order

### Study Sites

`ucp_study_site` records Kitengela and its selected neighborhoods or peripheral sites.

Native fields:

- Site name as title
- Description in the editor
- Featured image
- Revisions

Structured fields:

- Site category
- Latitude
- Longitude
- Coordinate verification flag
- Gallery attachment IDs
- Related Activity IDs

Coordinates may be stored while research is in progress, but the public API returns them only when the verification flag is true and both values pass range validation.

### Field Stories

`ucp_field_story` records editorial research narratives such as "From the Plot to the Particle."

Native fields:

- Title
- Gutenberg narrative
- Excerpt
- Hero image as featured image
- Author and publication date
- Revisions

Structured fields:

- Gallery attachment IDs
- Photographer or creator credit
- Closing statement
- Related Study Site IDs
- Related Team Member IDs

Taxonomies:

- Research Themes
- Research Methods

## Shared Taxonomies

The plugin registers three reusable taxonomies:

- `ucp_theme`: Urbanization, Environment, Health, and future research themes.
- `ucp_method`: methods such as land-use analysis, household surveys, air monitoring, water analysis, biodiversity inventories, health surveys, photography, and sound.
- `ucp_activity_type`: Fieldwork, School Collaboration, Science-Society Dialogue, Public Policy, Artistic Practice, and future activity categories.

Activation seeds these terms only when their slugs do not already exist. Editors can add and edit terms through WordPress.

Publication type remains a controlled structured field because it drives presentation rather than cross-content navigation. Initial values are journal article, book chapter, report, working paper, policy brief, conference paper, and other.

## Administration

All content types appear beneath the Urban Care top-level menu. Each type uses its native WordPress list table, bulk actions, trash, revisions, media library, and Gutenberg editor.

A reusable meta-box layer renders type-specific structured fields. Field definitions are centralized so rendering, sanitization, registration, and REST serialization do not drift apart. Relationship fields select records from the relevant post type and persist arrays of integer post IDs.

Project singleton behavior includes:

- A direct Project Content submenu.
- Redirecting that submenu to the canonical Project edit screen.
- Removing or disabling Add New when a canonical Project exists.
- Rejecting additional Project creation at the server boundary.

## Activation and Seed Content

Activation performs these operations in order:

1. Register post types, taxonomies, and metadata.
2. Seed default taxonomy terms without overwriting existing terms.
3. Locate an existing canonical Project by a dedicated seed key.
4. If none exists, create one draft Project populated from the supplied longer version.
5. Store its post ID as the canonical Project option.
6. Flush rewrite rules once.

The populated draft includes the official project introduction, urbanization context, three themes, care concept, systemic approach, five objectives, methodology summary, participation and policy-dialogue text, dissemination approach, and Kitengela study-area overview. It does not include the source instruction to add unverified locations to the map.

Reactivation is idempotent. It never replaces the canonical record's title, body, excerpt, metadata, status, or editor changes.

Deactivation flushes rewrite rules and removes no content. Uninstall removes plugin settings and canonical-record pointers only. Editorial posts, terms, media, and metadata remain in the database.

## REST API

WordPress core REST support remains enabled for authenticated editorial use. The plugin also publishes normalized read endpoints under `/wp-json/urbancareproject/v1`:

- `GET /project`
- `GET /activities`
- `GET /activities/{slug}`
- `GET /publications`
- `GET /publications/{slug}`
- `GET /team`
- `GET /team/{slug}`
- `GET /partners`
- `GET /partners/{slug}`
- `GET /study-sites`
- `GET /study-sites/{slug}`
- `GET /field-stories`
- `GET /field-stories/{slug}`

Collection endpoints support bounded pagination. Relevant collections support taxonomy, publication type, featured, date, and relationship filters. Page size defaults to 10 and cannot exceed 100.

Normalized records contain:

- Numeric ID and stable slug
- Title
- Rendered narrative and excerpt
- Publication timestamps
- Featured image object with URL, dimensions, alt text, caption, and credit where available
- Gallery image objects
- Structured metadata
- Taxonomy terms as ID, slug, and name
- Relationships as compact referenced records

Only published records appear publicly. Responses omit WordPress internals, private settings, revalidation secrets, hidden team email, and unverified coordinates. Missing singleton or item records return a REST `404`; invalid filters return `400` with a stable error code.

## Validation and Security

Every structured field has one explicit sanitizer:

- Plain text: `sanitize_text_field`
- Multiline plain text: `sanitize_textarea_field`
- Limited HTML: `wp_kses_post`
- Email: `sanitize_email`
- URL: `esc_url_raw`
- Dates: strict `YYYY-MM-DD` validation
- Integer IDs and ordering: `absint`
- Coordinate values: numeric parsing plus latitude and longitude range checks
- Booleans: explicit boolean normalization
- Arrays: item-level sanitization, deduplication, and removal of invalid IDs

Meta-box saves require the correct nonce, post-type hook, edit capability, and non-autosave/non-revision context. Registered metadata includes type, single-value behavior, sanitization callback, authorization callback, default, and REST schema.

Public API queries force `post_status=publish`. Relationship resolution never exposes unpublished linked records. Collection queries cap page size and whitelist sortable and filterable fields.

## File Boundaries

The current all-in-one admin class will be divided along ownership lines:

- Content registration: post types and taxonomies
- Metadata schema and sanitization
- Admin fields and singleton navigation
- Seed content and activation lifecycle
- REST routes and normalized serializers
- Plugin orchestration and hook registration

The generator script remains a bootstrap artifact and is not rerun to apply feature updates.

## Verification

Automated and local checks will cover:

- PHP syntax for every PHP file
- Main plugin bootstrap with WordPress API stubs
- Registration of all seven post types and three taxonomies
- Registered metadata schemas and sanitizers
- Activation seed creation and reactivation idempotency
- Singleton Project enforcement
- Meta-box nonce, capability, autosave, and revision behavior
- REST collection and item response shapes
- Published-only filtering
- Hidden-email behavior
- Verified-coordinate behavior
- Pagination caps and invalid parameter errors
- Bash syntax and overwrite protection remain valid for the original generator

A real WordPress environment must complete the final integration check: activate the plugin, verify the Urban Care menu and editors, add/edit/trash/restore records, upload media, inspect the populated Project draft, and request each public endpoint.

## Out of Scope

- Connecting the Next.js frontend to WordPress
- Importing every archive document, person, publication, partner, activity, and image
- Authentication beyond WordPress's existing admin and REST authentication
- Next.js revalidation and Vercel deployment automation
- Public write endpoints
- Custom database tables
- A custom JavaScript admin application
- Publishing unverified study-site coordinates
