# Contributing

Thanks for looking. This is a small, dependency-free project — everything you need is in
this repository.

## Running it locally

```bash
git clone https://github.com/<your-account>/super-power-2-editor.git
cd super-power-2-editor
php -S 127.0.0.1:8000
```

Open <http://127.0.0.1:8000/>. PHP 7.4+ is enough; no extensions, no Composer, no build
step. The `work/` folder is created on first request and is git-ignored.

If you run it only for yourself, you can set `LOCAL_MODE = true` in
`assets/php/config.php` to enable the "Game folder" field and open files straight from
the game install. **Never** do that on a public host — on shared hosting it would let one
visitor read another's files.

## Reporting a bug

Please include:

- what you did, what you expected, what happened;
- whether the database is vanilla or from a mod, and its size;
- the table and column, if it is about a specific value;
- browser and PHP version;
- for a map problem, a screenshot helps a lot.

Do **not** attach `DATABASE.GDB` or `StringTable.*.gst` files to an issue — they are game
data. Describe the case instead, or link to the mod.

## Code style

The existing code is plain, procedural PHP with `declare(strict_types=1)`, four-space
indentation and no framework. Please match it rather than introducing a new style.

- No Composer dependencies and no build step. Third-party front-end assets are vendored
  into `assets/vendor/` so the page makes zero network requests — keep it that way.
- No PHP extension requirements. ZIP, Firebird parsing and `.gst` handling are all done
  in pure PHP on purpose, because the target is cheap shared hosting.
- User-visible strings go through `t()` so they can be translated.
- Anything that writes to the database must go through the change log
  (`assets/php/changes.php`) so it can be undone.

## Touching the Firebird parser

`assets/php/gdb.php` reads and writes a real Firebird 1.5 file (ODS 10.1). It is the one
place where a mistake silently corrupts someone's save. Rules:

- Never touch the slot table, the record count or the indexes.
- Never widen a record past its slot. If it does not fit, move it to free space on the
  same page; if there is no room, fail loudly.
- Indexed fields (`ID`) stay read-only. Row insertion and deletion stay unsupported until
  someone implements B-tree rebuilding properly.
- Verify any change against a real Firebird 1.5 engine before opening a PR, and say in
  the PR what you compared: table count, row count, value count, discrepancies.

## Adding an interface language

1. Copy `assets/lang/en.json` to `assets/lang/<code>.json` (two-letter code: `pl`, `cs`,
   `tr`…).
2. Translate the values. **Do not change the keys** — the key is the English string and
   is also the fallback.
3. Add one line to the `LANGS` array in `assets/php/i18n.php`:

   ```php
   'pl' => 'Polski',
   ```

4. Reload the page; the language appears under the globe icon.

`%s` and `%d` are substitutions. A file must have the same number of them, in the same
order, as the key — if the order is broken the string falls back to English rather than
crashing.

## Adding a guide translation

Copy `assets/guide/en.php` to `<code>.php` and translate the text. The entry appears in
the list by itself; the order follows the interface language list. Layout is not repeated
in these files — only text. A section looks like:

```php
['id' => 'start', 'icon' => 'rocket-takeoff', 'h' => 'Heading', 'body' => [
  '<p>An ordinary paragraph with markup.</p>',
  ['fig', '01-upload.webp', 'alt text', 'caption under the image'],
  ['note', 'info-circle', 'A call-out with an explanation.'],
]],
```

Screenshots live in `assets/img/guide/`, named in order (`01-upload.webp`,
`02-overview.webp`, …). To replace one, drop in a file with the same name — dimensions
are picked up automatically. Page layout and the size list are in
`assets/php/views/guide.php`. If a language file is missing or broken, the editor quietly
falls back to English instead of failing.

## The documentation site

`docs/` is generated, never edited by hand. It holds the landing page and the guide as
static HTML in all nine languages, and GitHub Pages serves it. After changing a guide
file or a screenshot, regenerate it and commit the result:

```bash
php tools/build-pages.php
```

The generator rebuilds `docs/` from scratch, copies the screenshots and emits an icon
stylesheet containing only the glyphs the site actually uses.

## Pull requests

- One topic per PR.
- Say what you tested and on what: PHP version, browser, vanilla or modded database.
- Run `php -l` over any file you changed.
- Keep the diff to what the change needs — no reformatting sweeps.

## Licence

By contributing you agree that your contribution is licensed under the
[MIT Licence](LICENSE), the same as the rest of the project.
