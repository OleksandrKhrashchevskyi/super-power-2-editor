Interface translations.

One language — one file <code>.json, plain JSON in UTF-8:
  key    — the English string (which is also the default text);
  value  — the translation.

en.json is only there as a sample: if a key is missing from every file,
the key itself is shown, i.e. the English text.

How to add your own language:
  1. copy en.json to <code>.json (two-letter code: pl, cs, tr...);
  2. translate the values, do not touch the keys;
  3. add a line to the LANGS array in assets/php/i18n.php:
         'pl' => 'Polski',
  4. reload the page — the language appears in the globe menu.

Rules:
  * %s and %d are substitutions. A value must contain the same number of
    them, in the same order, as the key. If the order is broken the string
    is shown in English (t() catches this and does not fail).
  * Technical words are not translated: null, BLOB, IDX, JSON, .gdb, .gst,
    Firebird, RLE, ODS, NATO.
  * Country, region, language and religion names do not come from here but
    from the game dictionary StringTable.<language>.gst — the game itself
    translates those.

Only the selected language is loaded; the other files are not read.
