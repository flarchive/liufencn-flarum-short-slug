# Changelog

## 1.0.1 — 2026-10-10

Fixed

- The admin bundle was never registered: `extend.php` was missing the
  `Frontend('admin')->js()` declaration, so `js/dist/admin.js` was not compiled
  into `/assets/admin.js` and the two settings toggles did not show up in the
  admin panel. They do now.

## 1.0.0 — 2026-10-10

Initial release.

- Drop the auto-generated slug on save so discussion URLs become `/d/{id}`.
- Admin toggles to enable/disable the rewrite and to allow custom slugs.
- Accept a custom `slug` attribute submitted through the API (requires the
  permission to rename the discussion).
- `short-slug:clear` command to shorten existing discussions, with `--id`,
  `--keep-custom` and `--dry-run` options.
- `short-slug:set` command to set or clear the slug of a single discussion.
- English and Simplified Chinese translations.
