---
entry_id: "ctx-20260905-090553513155-716730b9"
title: "Add Partner and Team Member custom forms"
category: "feature"
tags: ["wordpress", "admin", "partners", "team", "media-library"]
files: ["includes/admin/class-urbancareproject-fields.php", "includes/admin/js/urbancareproject-partner-fields.js", "includes/content/class-urbancareproject-metadata.php", "tests/admin-fields-test.php", "urbancareproject.php"]
commits: []
status: "active"
importance: "medium"
created_at: "2026-09-05T09:05:53Z"
updated_at: "2026-09-05T09:05:53Z"
summary: "Improved the WordPress admin authoring experience for Partners and Team Members with shared Media Library controls, a Team portrait stored as the featured image, and a Partner relationship selector."
retrieval_hints: "Partner Team Member custom admin form portrait media picker institution selector featured image"
---

## What
Team Member editors now include a portrait picker and Partner dropdown; Partner editors retain adaptive logo/profile copy through the shared media picker. Team portraits use WordPress featured-image storage and the duplicate core panel is hidden.

## Why
Editors should not enter raw Partner IDs or manage Team portraits through a disconnected side panel.

## Impact
Existing metadata and REST contracts remain compatible. Plugin version is 1.2.0 and focused form, sync, syntax, and editor diagnostics pass.

## Notes
No frontend files were changed or deployed.
