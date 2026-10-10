# Short Slug for Flarum 2.x

Rewrite discussion URLs from `/d/{id}-pinyin-slug` to the bare `/d/{id}` form.

Flarum builds every discussion URL out of its title — `Str::slug($title, '-', default_locale)` — which is fine for English titles but produces long, unreadable URLs for languages that are not written in Latin characters. A Chinese title such as *Qoder 默认设置* becomes `/d/248-qoder-mo-ren-she-zhi`.

This extension drops that slug right before the discussion is saved, so the URL stays short: `/d/248`. Nothing else changes, and **no core or vendor file is touched** — it is a pure event listener.

Existing links keep working: Flarum resolves `/d/{id}` by the numeric ID first, so old URLs that still carry a slug continue to redirect correctly.

## Features

- **Short URLs** — `/d/{id}` for every discussion, on old and new ones alike.
- **Admin toggles** — turn the rewrite on/off and decide whether custom slugs are allowed.
- **Custom slugs (optional)** — keep a hand-written slug for individual discussions. See below.
- **CLI tools** — clean up existing discussions in one command.
- **Translations** — English and Simplified Chinese included.

## Installation

```bash
composer require liufencn/flarum-short-slug
```

Then enable it in the admin panel, or run:

```bash
php flarum extension:enable liufencn-short-slug
```

Finally, clean up the discussions that already exist:

```bash
php flarum short-slug:clear
```

> The cleanup command is safe to run at any time. Old URLs containing a slug keep resolving, because the numeric ID is always enough.

## Usage

### Rewriting existing discussions

```bash
# Preview what would change
php flarum short-slug:clear --dry-run

# Clear every auto-generated slug
php flarum short-slug:clear

# Only specific discussions
php flarum short-slug:clear --id=12 --id=248

# Keep slugs that look hand-written instead of auto-generated
php flarum short-slug:clear --keep-custom
```

### Custom slugs

A discussion can keep a readable slug if you want one — useful for pinned or
documentation-style discussions:

```bash
# Set a custom slug -> /d/12-release-notes
php flarum short-slug:set 12 release-notes

# Go back to the short form
php flarum short-slug:set 12
```

Users who are allowed to *rename* a discussion can also do it over the API,
by sending a `slug` attribute in the request body:

```http
PATCH /api/discussions/12
Content-Type: application/vnd.api+json

{
  "data": {
    "type": "discussions",
    "id": "12",
    "attributes": { "slug": "release-notes" }
  }
}
```

Send an empty string to force the short form for that discussion. The `slug`
attribute is read-only in Flarum's JSON:API schema, so this extension reads it
from the raw request body — permission to rename the discussion is required.

When the *Allow custom slugs* toggle is off, every discussion — including ones
with a hand-written slug — is pushed back to `/d/{id}`.

## Comments on slugs

- Only letters, digits, `.`, `_`, `~` and `-` survive; everything else collapses
  into a single dash. Non-Latin letters (e.g. Chinese characters) are preserved.
- Editing a discussion title regenerates the URL. Auto-generated slugs are
  dropped again; hand-written ones are replaced too, since the title changed.
- Turning the admin toggle off restores Flarum's default behaviour for all
  *subsequent* saves. Existing slugs stay as they are — use `short-slug:clear`
  after re-enabling the extension to shorten them again.

## License

`AGPL-3.0-or-later` — see [LICENSE](LICENSE).

You are free to use, modify and self-host this extension, including commercially,
as long as any modified version you make available to others — including as a
network service — is released under the same license. If you need to ship a
modified, closed-source version, contact the author for a commercial license.

---

## 中文说明

把讨论链接从 `/d/248-qoder-mo-ren-she-zhi` 缩短为 `/d/248`。

Flarum 核心会自动用标题生成 slug（中文标题会变成拼音），本扩展在保存前把自动生成的
slug 清空，因此链接只剩数字 ID。**不修改任何核心或 vendor 文件**，纯事件监听。

装好后执行一次 `php flarum short-slug:clear` 即可把已有讨论一并变短。旧链接不会失效，
因为 Flarum 解析讨论地址时只依赖数字 ID。

需要个别讨论保留可读地址时，用 `php flarum short-slug:set <ID> <slug>` 单独设置，
或在后台关闭「允许自定义 slug」以强制全站使用 `/d/{id}`。

许可为 `AGPL-3.0-or-later`：可自由使用、修改、商用；但若你修改后对外提供服务，
必须以相同许可开源你的改动。需要闭源商用请联系作者获取商业授权。
