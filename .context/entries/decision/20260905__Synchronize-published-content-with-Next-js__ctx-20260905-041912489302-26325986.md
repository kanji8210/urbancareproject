---
entry_id: "ctx-20260905-041912489302-26325986"
title: "Synchronize published content with Next.js"
category: "decision"
tags: ["wordpress", "nextjs", "revalidation", "vercel", "settings"]
files: ["includes/integration/class-urbancareproject-frontend-sync.php", "includes/class-urbancareproject.php", "includes/admin/class-urbancareproject-admin.php", "includes/admin/partials/urbancareproject-admin-display.php", "urbancareproject.php", "tests/frontend-sync-test.php"]
commits: ["56fc14d"]
status: "active"
importance: "high"
created_at: "2026-09-05T04:19:12Z"
updated_at: "2026-09-05T04:19:12Z"
summary: "Added secure plugin settings behavior, automatic published-content revalidation, connection testing, and a manual Vercel deploy-hook fallback."
retrieval_hints: "WordPress publish save_post transition_post_status frontend revalidation deploy hook"
---

## What
Published saves notify Next.js after metadata is stored; unpublishing also invalidates public pages. Settings preserve and redact secrets, enforce HTTPS deploy hooks, expose a revalidation test, and keep full deployment manual.

## Why
Editors need immediate frontend updates without triggering expensive full deployments on every content edit.

## Impact
All seven Urban Care post types participate in revalidation. Draft-only changes remain private and do not notify the frontend.

## Notes
Plugin version is 1.1.0. The Vercel deploy hook is a manual fallback only.
