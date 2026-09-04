---
entry_id: "ctx-20260904-221314319251-814e4102"
title: "Native WordPress content management scaffold"
category: "feature"
tags: ["wordpress", "cms", "custom-post-types", "metadata", "rest-api", "seeding"]
files: ["includes/content/class-urbancareproject-content-types.php", "includes/content/class-urbancareproject-metadata.php", "includes/content/class-urbancareproject-seeder.php", "includes/admin/class-urbancareproject-fields.php", "includes/api/class-urbancareproject-rest-api.php", "includes/api/class-urbancareproject-serializer.php", "includes/class-urbancareproject.php", "includes/class-urbancareproject-activator.php", "includes/admin/class-urbancareproject-admin.php", "uninstall.php"]
commits: []
status: "active"
importance: "high"
created_at: "2026-09-04T22:13:14Z"
updated_at: "2026-09-04T22:13:14Z"
summary: "Implemented a native WordPress editorial system with seven content types, three taxonomies, structured metadata editing, idempotent Project seeding, singleton enforcement, and normalized public REST endpoints."
retrieval_hints: "WordPress CMS CPT taxonomy metadata canonical project singleton seed REST serializer privacy"
---

## What
Centralized registration; added schema-driven metadata and secure admin fields; seeded terms and an official canonical Project draft; added normalized APIs with privacy controls.

## Why
Urban Care is replacing Sanity with an independently managed WordPress plugin while preserving a CMS-neutral frontend boundary.

## Impact
Editors can manage project content in WordPress; activation creates one populated Project draft; public APIs omit drafts, hidden email, and unverified coordinates.

## Notes
Validated PHP and Bash syntax, route registration, idempotent seeding, sanitizers, privacy behavior, and plugin bootstrap. Real WordPress activation remains to be exercised.
