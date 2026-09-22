land.json — world land outlines.

Source: Natural Earth (naturalearthdata.com), 1:50m Physical / Land.
Natural Earth is public domain, usable without restriction.
Taken via the world-atlas package (ISC, Mike Bostock) and reduced to a
compact form: coordinates in hundredths of a degree, delta encoding,
0.05 degree simplification, and islands smaller than 0.04 square degrees
dropped.

Format: {"s":100,"p":[ [ring, ring, ...], ... ]}
  s     — coordinate divisor;
  p     — polygons; the first ring is the outer one, the rest are holes;
  ring  — a flat array [dx,dy,dx,dy,...] where the first pair is absolute
          and the rest are deltas; divide by s to get degrees.

countries.json — world country outlines (used only as a map constraint).

Source: Natural Earth (naturalearthdata.com), 1:110m Cultural / Admin 0.
Natural Earth is public domain. Taken via the world-atlas package (ISC),
with the numeric ISO 3166-1 code converted to alpha-3 (as in COUNTRY.CODE),
0.12 degree simplification, coordinates scaled by 50 (see the format below)
and delta encoding.

Format: {"s":50,"c":[ [code, [ring, ring, ...]], ... ]}
  s     — coordinate divisor;
  c     — one entry per country: its alpha-3 code and its polygons;
  ring  — encoded exactly as in land.json.
