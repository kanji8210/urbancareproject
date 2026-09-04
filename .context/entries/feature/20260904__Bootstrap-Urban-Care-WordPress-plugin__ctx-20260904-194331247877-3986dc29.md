---
entry_id: "ctx-20260904-194331247877-3986dc29"
title: "Bootstrap Urban Care WordPress plugin"
category: "feature"
tags: ["wordpress", "plugin", "boilerplate", "admin", "rest-api", "custom-post-types"]
files: ["setup-plugin.sh", "urbancareproject.php", "uninstall.php", "includes/class-urbancareproject.php", "includes/class-urbancareproject-loader.php", "includes/class-urbancareproject-activator.php", "includes/class-urbancareproject-deactivator.php", "includes/admin/class-urbancareproject-admin.php", "includes/admin/partials/urbancareproject-admin-display.php", "includes/admin/partials/urbancareproject-stats-display.php", "includes/api/class-urbancareproject-rest-api.php", "includes/public/class-urbancareproject-public.php", "languages/.gitkeep"]
commits: []
status: "active"
importance: "high"
created_at: "2026-09-04T19:43:31Z"
updated_at: "2026-09-04T19:43:31Z"
summary: "Corrected and ran the flattened Bash boilerplate generator to create the initial Urban Care Project WordPress plugin with lifecycle hooks, admin settings, Activities and Team post types, and a read-only activities REST endpoint."
retrieval_hints: "Urban Care WordPress plugin bootstrap setup-plugin Bash admin menu Activities Team CPT REST API"
---

## What
Reconstructed a one-line invalid pasted script into a valid repository-relative Bash generator with overwrite protection. Generated the main plugin, uninstall lifecycle, hook loader, orchestrator, admin settings pages, two CPTs, activity metadata, and urbancareproject/v1/activities endpoint.

## Why
The original script lost all line breaks, used hardcoded paths, lacked lifecycle hooks and several WordPress security checks, and could not run because Bash was not on PowerShell PATH.

## Impact
The nested plugin repository now has a valid initial codebase. All 11 PHP files pass PHP 8.2 syntax checks, the bootstrap loads under WordPress API stubs, Bash syntax passes, and rerunning the generator safely refuses to overwrite files.

## Notes
Run the generator with C:/Program Files/Git/bin/bash.exe because bash is not on PATH. No WordPress-to-Next.js sync logic is implemented yet.
