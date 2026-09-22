# Third-party notices

The editor's own code is MIT (see [LICENSE](LICENSE)). The files below are bundled in
`assets/vendor/` so the page works without any network access, and they keep their own
licences.

| Component | Version | Licence | Location |
| --- | --- | --- | --- |
| [Bootstrap](https://getbootstrap.com/) | 5.3.8 | MIT | `assets/vendor/bootstrap/` |
| [Bootstrap Icons](https://icons.getbootstrap.com/) | 1.13.1 | MIT | `assets/vendor/icons/` |
| [Tom Select](https://tom-select.js.org/) | 2.6.2 | Apache-2.0 | `assets/vendor/tom-select/` |
| [SimpleBar](https://grsmto.github.io/simplebar/) | 6.3.3 | MIT | `assets/vendor/simplebar/` |
| [NProgress](https://ricostacruz.com/nprogress/) | 0.2.0 | MIT | `assets/vendor/nprogress/` |
| [Inter](https://rsms.me/inter/) | variable | SIL Open Font License 1.1 | `assets/vendor/fonts/` |
| [JetBrains Mono](https://www.jetbrains.com/lp/mono/) | — | SIL Open Font License 1.1 | `assets/vendor/fonts/` |

## Geodata

`assets/vendor/geo/land.json` and `assets/vendor/geo/countries.json` are derived from
[Natural Earth](https://www.naturalearthdata.com/) — **public domain**, no restrictions
on use.

- `land.json` — Natural Earth 1:50m Physical / Land.
- `countries.json` — Natural Earth 1:110m Cultural / Admin 0.

Both were taken via the [world-atlas](https://github.com/topojson/world-atlas) package
(ISC, Mike Bostock) and reduced to a compact form: integer coordinates with a divisor
(100 for land, 50 for countries), delta encoding, simplification (0.05° and 0.12°
respectively) and islands under 0.04 square degrees dropped. For `countries.json` the
numeric ISO 3166-1 code was converted to alpha-3 to match `COUNTRY.CODE`. The format is
documented in `assets/vendor/geo/README.txt`.

## Game data

No SuperPower 2 game files are distributed with this project. `DATABASE.GDB` and
`StringTable.*.gst` are supplied by the user and are never committed — see
[.gitignore](.gitignore).

SuperPower 2 and its assets belong to their respective rights holders. This tool is
unofficial and unaffiliated.
