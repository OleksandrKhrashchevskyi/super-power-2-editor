# SP2 DB Editor

A browser-based editor for the **SuperPower 2** game database (`DATABASE.GDB`) and its
language files (`StringTable.*.gst`).

Pure PHP. No Firebird, no PHP extensions, no external programs. It runs on any shared
host — Linux, Windows, PHP 7.4+.

**Guide and screenshots:** <https://oleksandrkhrashchevskyi.github.io/super-power-2-editor/>

**Run it in your browser, no server:** <https://oleksandrkhrashchevskyi.github.io/super-power-2-editor/app/>

---

## Why this exists

The SuperPower 2 database is a **Firebird 1.5** file (ODS 10.1, dialect 1, created
2005-01-18). The Firebird 1.5 engine exists only for Windows, and only as a 32-bit
build — so it cannot be run on a Linux host at all.

So the `.gdb` file is parsed directly in PHP (`assets/php/gdb.php`):

- 4 KB pages, 16-byte header;
- data pages: slot table, records, fragment continuation;
- RLE decompression of records;
- the system tables `RDB$RELATIONS` / `RDB$RELATION_FIELDS` / `RDB$FIELDS` /
  `RDB$INDICES` are read from the file itself — no schema is hard-coded anywhere;
- field layout is computed from `field_id` with alignment (4 bytes max, as on i386),
  NULL bitmap at the start of the record.

Writing goes back into the same slots: data is re-packed into RLE so that no block is
split across fragments, then written over the previous bytes. If it no longer fits, the
record is moved to free space on the same page; if there is no room, the editor refuses
honestly and asks you to shorten the text. The slot table, the record count and the
indexes are never touched.

### Verified against the real engine

Everything was cross-checked against a real Firebird 1.5 instance running on the same
database:

| Check | Result |
| --- | --- |
| Reading | 28 tables, 62,800 rows, 669,369 values — 0 discrepancies |
| Writing | 473 rows across 27 tables, 1,382 values — the engine read back exactly what PHP wrote, nothing else was damaged |
| Single-field edit in `COUNTRY` | of 194 × 86 values, exactly one changed |

A merge of Ukraine and Belarus into Poland (492 rows across 9 tables) was then opened by
Firebird 1.5, which reported 80 regions and 419 units for Poland instead of 49 and 142,
with the total row count across all tables unchanged.

---

## Features

### Database tab

Upload `DATABASE.GDB`, edit values, download it back. Tables are paginated, filterable
and sortable. If a language file is open as well, real names are shown next to
`*_STID` columns instead of the raw numbers.

### Language tab

Upload `StringTable.<language>.gst` (UTF-16LE + index), edit the texts, download.
Any number of language files can be open at once; a switcher appears in the sidebar and
name substitution in the tables follows whichever one is active. English is activated by
default.

### World map

The database contains no region geometry — only the coordinates of 3,347 cities
(`CITIES.LONGITUDE` / `LATITUDE`) and region ownership (`REGION.COUNTRY_ID`,
`MILITARY_OWNER_ID`). The map is therefore built like this:

1. Land outlines come from `assets/vendor/geo/land.json` (Natural Earth 1:50m Land,
   public domain, bundled — no internet needed).
2. A 2560×1280 raster in equirectangular projection: for each land pixel the nearest
   city is found and the pixel is assigned to that city's region.
3. A second pass moves any pixel that falls inside a country's outline to the nearest
   city **of that same country**. Without it the whole of northern Canada stayed grey
   (all 48 Canadian cities in the database sit below 53.6° N) and Maine ended up in
   Quebec, because Montreal is closer than any American city. Outlines live in
   `assets/vendor/geo/countries.json`, matched by ISO code (`COUNTRY.CODE` holds AFG,
   ALB, DZA…). The 2004-era codes and dependent territories are mapped through an alias
   table: ROU→ROM, SRB/MNE→SCG, GRL→DNK, PRI→USA and so on.
4. Land further than 34° from any city of its own country stays neutral grey.

The outlines are only a constraint — who owns what is still decided solely by the
database. Merge two countries and the map recolours immediately.

> **Region borders are approximate.** 1,419 of 2,604 regions have no city at all, so
> their territory dissolves into neighbours. Country colouring is still correct.

**Colourings** (geometry is computed once, switching is instant):

- **Ownership** — owning country, military control.
- **Population** — dominant language and dominant religion per region (from `LANGUAGES`
  and `RELIGIONS`, which are bound to regions rather than countries, so this is exact),
  plus "share of one language" and "share of one religion" as a gradient.
- **Government** — `COUNTRY.GVT_TYPE`.
- **Geography** — continent and geo-group.
- **Relations of one country** — from the `RELATIONS` matrix (194×194, −90…+100), scaled
  by the 90th percentile so two outliers don't grey out the map.
- **Treaty members** — `TREATY` (114 real treaties: NATO, WEU, ANZUS…) and
  `TREATY_MEMBER`.
- **Military** — total units per country, or units of one type. The database has no
  branch names, so each type is labelled with samples from `DESIGN`, e.g.
  `#10 · A-1 Ching Kuo, CF-18 Hornet`. Nothing is invented.
- **Numbers** — region population, infrastructure, telecom. The scale is quantile-based;
  region populations differ by a factor of thousands and a linear scale would produce a
  single-colour map.

Category colours are assigned by rank, so the largest languages or countries get the
most widely separated hues and the legend reads at a glance.

**Interaction:** hover for region name, owner, military control, current layer value,
population breakdown by language and religion with shares, ID and population; click to
open the same `REGION` row editor as the pencil in the table; Shift-drag to rubber-band
select regions, Ctrl-click to add or remove one, then "Transfer to country…" with a
choice of what to change (ownership, military control, or both); layers for country
borders, region borders, coastline, cities, capitals, troops, missiles and dimming of
inactive countries; wheel to zoom, drag to pan, Esc to clear the selection.

Troops and missiles are drawn at **their own** coordinates from the database, not at the
nearest city: `UNIT_GROUPS` — 1,594 groups (marker size from the unit count in `UNITS`),
`MISSILE` — 1,938 positions with coordinates (another 1,812 missiles sit on submarines
and have none).

As a side effect the map exposes the game's own mistakes — the dominant language of
Brazil in the database is English, and of Ukraine, Russian. See it, click it, fix it.

### Tools

- **Find and replace** (`?a=replace`) — pick table, column, condition (equals, not
  equals, greater, less, contains, empty, null, any value) and a new value or null.
  *Preview* shows how many rows matched, how many will actually change, and the first 15
  `before → after` pairs. Indexed columns and BLOBs cannot be edited.
- **Database check** (`?a=check`) — only real breakage, nothing invented: references to
  non-existent rows (the relation is inferred from the name, `COUNTRY_ID` → table
  `COUNTRY`; the database has no foreign keys); `*_STID` values with no dictionary entry
  (if a dictionary is open); active countries with no region; a capital in the wrong
  country or missing; duplicate IDs where a unique index exists; regions with no cities
  (a note, not a fault — such a region is drawn approximately on the map). Every finding
  links straight into the table with a filter applied, or into find-and-replace.
- **Global search** (`?a=find`) — one field searches all 28 tables and the `.gst` texts
  at once. Text is case-insensitive, numbers match exactly. Results are grouped by table
  with the matching columns listed. For example "Poland" is found only in the game texts
  (tables store numeric STIDs), while "UKR" is in `COUNTRY.CODE`.
- **Log and undo** (`?a=log`) — every write (row edit, country merge, region transfer
  from the map, find-and-replace, dictionary edit) is recorded with its previous values,
  and *Undo* puts them back in the file. The log lives in the session and holds up to
  200 operations; edits over 40,000 cells are recorded but cannot be undone — that is
  what `backups/` is for.

### Merge countries

Pick a receiving country, tick the ones joining it, *Preview*, then *Merge*. Everything
that references the country is moved:

```
REGION.COUNTRY_ID          territory (and with it cities — they belong to regions)
REGION.MILITARY_OWNER_ID   military control (can be switched off)
UNITS / UNIT_GROUPS        troops
MISSILE                    missiles
PARTIES                    parties
DESIGN / DESIGN_FORMAT     designs and their owners
TREATY_MEMBER              treaty membership
COV_OPS_CELL               covert cells (owner and target)
LANGUAGES_STATUS / RELIGIONS_STATUS
```

Merged countries can optionally be flagged `ACTIVATED='F'`. The `RELATIONS` matrix
(194×194) is left alone — merging it requires recomputing both rows and columns and is a
separate job.

### Archive in, archive out

A single `.zip` can be uploaded with everything at once: the database goes to the
Database tab, every `StringTable.*.gst` found goes to the Languages tab. *Download all
(.zip)* packs the current database and all language files back into one archive. ZIP is
read and written by PHP itself — the `ZipArchive` extension is not required.

### Built-in guide

23 sections with 23 screenshots (WebP, ~0.9 MB total), in all nine interface languages.
Reachable from the book icon in the top bar, the "New here? Read the guide." link, or
directly at `/super-power-2/guide/`.

### Interface

Built on real Bootstrap 5.3.8 components — navbar, offcanvas, card, list-group,
nav-pills, nav-tabs, accordion, dropdown, modal, toast, tooltip, pagination, progress,
form-switch, badge, btn-group. Everything is bundled locally in `assets/vendor`; the page
makes **no network requests at all**.

**Appearance** (palette icon, top right): 10 themes (Midnight, Graphite, Nord, Dracula,
Solarized Dark, Forest, Amber, Crimson, Paper, Solarized Light — the last two light),
accent colour (8 presets or your own), text colour, 10 fonts, sizes 12–22 px and a
*Compact rows* switch. A theme defines a small set of variables which are mapped onto
Bootstrap's own (`--bs-body-bg`, `--bs-card-bg`, `--bs-btn-*`, …), so every component
recolours, not just the custom blocks. The choice is stored in `localStorage` and applied
before the first paint, so nothing flashes.

**Responsive:** from 992 px the sidebar is static (`offcanvas-lg`), sticky under the
header and scrolls separately from the table; below 992 px the same panel works as an
offcanvas. The table scrolls horizontally on its own, the header row sticks to the top
and the pencil column sticks to the left, so the edit button is always visible.

### Interface languages

Nine, in menu order: English, Українська, Deutsch, Español, Français, Italiano,
Nederlands, Português, Русский. The globe icon switches them; the choice is remembered in
a cookie and in the session. With no choice made, a suitable language is taken from the
browser's `Accept-Language`, otherwise English.

Translations live in `assets/lang/<code>.json` — plain JSON, editable without touching
PHP. Only the selected language is loaded. Each file has 312 strings: 286 for the pages
and 26 for JavaScript (map, file upload), which the page hands to the script in one
block, so there is no second translation list in the code.

Country, region, language and religion names are **not** translated by the interface —
they come from the game's own dictionary, `StringTable.<language>.gst`.

---

## Running without a server

The same PHP is also compiled to WebAssembly and shipped as a static page, so the editor
runs inside the browser tab with no host at all:

<https://oleksandrkhrashchevskyi.github.io/super-power-2-editor/app/>

A service worker hands every request under `app/run/` to that in-browser PHP, which serves
the editor from a virtual filesystem. The PHP source is used **unchanged** &mdash; the build
only packs it up.

Measured on the vanilla 9.5 MB `DATABASE.GDB` in Chromium:

| | |
| --- | --- |
| PHP interpreter | 17.5 MB, about 7 MB over the wire, cached after the first visit |
| Interpreter start | 0.3 s |
| Parse the whole database | 1.4 s, 8 MB peak |
| Page render | 15&ndash;70 ms |
| Reading | same fingerprint over all 669,369 values as native PHP |
| Writing | downloaded file byte-identical to the one native PHP produces |

Limits worth knowing:

- **Nothing survives a reload.** The virtual filesystem lives in the tab, so download your
  files before closing it. The hosted instance is the one to use for long sessions.
- Needs a service worker, so it will not run in browsers or private windows that block them.
- Untested on iOS, where per-tab memory is tight; a 40 MB modded database may not fit.

To rebuild it:

```bash
npm install @php-wasm/universal @php-wasm/web @php-wasm/web-8-3 esbuild
node tools/build-wasm.mjs
```

---

## Installation

Copy the contents of this repository into a folder on your host, for example
`public_html/super-power-2/`, and open it in a browser. That is all.

```
git clone https://github.com/<your-account>/super-power-2-editor.git super-power-2
```

The editor itself is `index.php`, `assets/` and `guide/` — about 3.4 MB. The `docs/` and
`tools/` folders only build the documentation site and the in-browser edition, so they can
be left off a PHP host:

```
rm -rf super-power-2/docs super-power-2/tools
```

Requirements:

- PHP 7.4 or newer, no extensions needed;
- write access to the `work/` subfolder — the editor creates everything else itself.

### Upload limits

A modded database can be up to 40 MB, so `php.ini` needs:

```ini
upload_max_filesize = 256M
post_max_size = 256M
```

If the limit is too low, the editor says so directly on the page.

### nginx

The `work/` folder gets a `.htaccess` (Apache, LiteSpeed), a `web.config` (IIS) and an
empty `index.html`, so files are not served by direct URL. nginx reads neither of the
first two, so it needs one line in the site config:

```nginx
location ^~ /super-power-2/work/ { deny all; }
```

(substitute your own path). On Apache and LiteSpeed it works out of the box.

---

## Multi-user by design

The editor expects different people to use one installation at the same time. There is no
registration.

Everyone who opens the page gets a short project code — something like `k7f3q29xm4`.
Everything they upload lives only in their own folder:

```
work/k7f3q29xm4/                 database and language files
work/k7f3q29xm4/backups/         a copy before every edit
work/k7f3q29xm4/config.json      that project's settings
```

Other people's files are neither visible nor overwritten: two people can upload their own
`DATABASE.GDB` in the same minute without getting in each other's way.

The code lives in a cookie, so normally you don't have to think about it. It is also in
the link:

```
https://your-site/super-power-2/index.php?p=k7f3q29xm4
```

That link is worth saving — it opens the project from another browser, from a phone, or
after the cookie is cleared. Opening it makes the editor pick up whatever files are in
the project folder, so work is not lost even if the PHP session has expired.

**Lifetime:** a project is deleted 7 days after it was last touched
(`PROJECT_TTL_DAYS`). Sweeping happens at most every 6 hours, on a normal page request —
no cron needed. Per-project size limit: 600 MB (`PROJECT_MAX_BYTES`).

---

## Security notes

- Project folder names are random (10 characters from an alphabet of 32, ~10¹⁵
  combinations), so someone else's project cannot be guessed.
- Uploaded file names are sanitised, extensions are checked, and the editor never steps
  outside the project folder.
- Direct filesystem access is **off** by default:

  ```php
  const LOCAL_MODE = false;
  ```

  While it is `false`, the "Game folder" field, the "open from game" links and writing
  back into the game are unavailable — on shared hosting they would let one visitor read
  another's files. Set it to `true` **only** when you run the editor at home, for
  yourself.

---

## Limitations

- Fields that are part of an index (in the SP2 database that is `ID`) cannot be edited —
  the index would stop matching the data and the game would find the wrong rows. They are
  marked `IDX` and locked in the interface.
- Adding and deleting rows is not supported: that requires rebuilding the index B-trees.
  Editing values is fully supported.
- BLOB fields are shown as `[BLOB]` and cannot be edited.

---

## Page weight

Tables such as `COUNTRY` and `REGION` have 84–86 columns, and at 200–500 rows a page used
to reach 2–6 MB — some hosts cut a response that large off (`ERR_CONNECTION_CLOSED`).
What was done: row data for the edit dialog is no longer duplicated in the HTML (it is
fetched when you click the pencil); cell markup is compressed; the response is gzipped if
the server doesn't do it already; and the number of cells per page is capped at 24,000,
which is stated under the table.

Result: `COUNTRY` at 200 rows is 0.5 MB instead of 2.4 MB, `REGION` at 500 rows is
0.77 MB instead of 5.9 MB, peak memory 10–12 MB instead of 40 MB.

---

## Project layout

```
index.php                  page assembly
guide/index.php            short URL for /guide
assets/css/style.css       styling (Bootstrap 5.3.8 sits alongside, no CDN)
assets/css/themes.css      themes
assets/js/app.js           upload with progress, search, modal
assets/js/map.js           world map
assets/php/gdb.php         Firebird ODS 10.x parsing and writing
assets/php/db.php          selection, filters, sorting, pages
assets/php/gst.php         language files .gst (UTF-16LE + index)
assets/php/schema.php      types and formatting
assets/php/helpers.php     utilities, uploads, backups
assets/php/router.php      actions
assets/php/changes.php     edit log and undo
assets/php/check.php       database check and global search
assets/php/i18n.php        interface languages (assets/lang/*.json)
assets/php/zip.php         ZIP read/write in pure PHP
assets/php/config.php      paths, settings, project folders
assets/php/views/          templates (guide.php — the guide layout)
assets/guide/              guide text, one file per language (9)
assets/img/guide/          guide screenshots (WebP)
assets/lang/               interface translations (JSON)
assets/vendor/             Bootstrap, icons, fonts, geodata
tools/build-pages.php      generates the static site in docs/
tools/build-wasm.mjs       packs the editor to run in the browser (docs/app/)
docs/                      the GitHub Pages site (generated - do not edit by hand)
work/                      projects: work/<code>/ and work/<code>/backups/
```

`docs/` holds the landing page and the guide rendered as static HTML in all nine
languages, published at the address above. It is built from the same
`assets/guide/*.php` and screenshots the editor itself uses, so after changing either,
regenerate it:

```bash
php tools/build-pages.php
```

---

## Contributing

Bug reports and pull requests are welcome — see [CONTRIBUTING.md](CONTRIBUTING.md).
Adding a new interface language takes one JSON file and one line of PHP.

## Licence

[MIT](LICENSE) for the editor's own code. Bundled third-party assets keep their own
licences — see [THIRD-PARTY-NOTICES.md](THIRD-PARTY-NOTICES.md).

## Disclaimer

This is an unofficial fan-made tool. It is not affiliated with, endorsed by or connected
to GolemLabs, DreamCatcher Interactive or any current rights holder of SuperPower 2. No
game data is distributed with it — you supply your own `DATABASE.GDB` and
`StringTable.*.gst` files.

Made in Ukraine.
