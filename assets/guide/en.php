<?php

return [
'name' => 'English',
'lead' => 'Everything the editor can do, in order, with screenshots.',

'sections' => [

['id' => 'start', 'icon' => 'rocket-takeoff', 'h' => 'Quick start', 'body' => [
  '<p>The editor opens the game\'s own files. Nothing is installed and nothing is
    converted: it reads <code>DATABASE.GDB</code> — a Firebird 1.5 database — and
    the <code>StringTable.*.gst</code> text files byte by byte, in plain PHP.</p>',
  '<ol>
     <li>Find the game\'s files. A clean install keeps them under
       <code>SuperPower 2\\Extras\\</code>; a mod keeps its own copy under
       <code>SuperPower 2\\MODS\\&lt;mod name&gt;\\</code>.</li>
     <li>Drag <code>DATABASE.GDB</code> onto the upload box, or click it and pick the file.</li>
     <li>Switch to the <strong>Languages</strong> tab and upload a
       <code>StringTable.*.gst</code> as well. This one is optional, but without it
       countries and cities are only numbers.</li>
   </ol>',
  '<p>A <code>.zip</code> holding several of those files at once works too — the
    database goes to one tab and every language file to the other.</p>',
  ['fig', '01-upload.webp', 'Upload screen',
   'Drop the file or click to choose. The bar shows megabytes and speed; the page opens by itself when the upload finishes.'],
  ['note', 'info-circle',
   'Your files are copies. The game\'s originals are never touched — you download the edited copy when you are done.'],
]],

['id' => 'project', 'icon' => 'folder2-open', 'h' => 'Your project and its link', 'body' => [
  '<p>There is no registration. The moment you open the page you get a short project
    code, and everything you upload lives in a folder of its own. Two people can
    upload their own <code>DATABASE.GDB</code> in the same minute without touching
    each other\'s work.</p>',
  '<p>The code sits in a cookie, so normally you never think about it. It is also in
    the link — keep that link and the project opens on another computer, on a phone,
    or after the cookies are cleared. You will find it at the bottom of the left
    panel and on the front page under <em>Your project</em>.</p>',
  ['fig', '22-project.webp', 'Project window',
   'The project link, the code, how much space the project uses, and a button that starts an empty one.'],
  '<p>A project is deleted seven days after its last visit. <strong>Close</strong>
    removes a file from the project on purpose — a copy stays in the project\'s
    <code>backups</code> folder either way.</p>',
]],

['id' => 'overview', 'icon' => 'speedometer2', 'h' => 'What the database tab shows', 'body' => [
  '<p>With a database open you land on a summary: how many tables, rows and columns
    there are, which tables are the big ones, and shortcuts to everything worth doing.</p>',
  ['fig', '02-overview.webp', 'Database overview',
   'SuperPower 2 comes with 28 tables and about 62 800 rows. The green badge on the left means a language file is attached, so numbers are shown as names.'],
]],

['id' => 'table', 'icon' => 'table', 'h' => 'Browsing a table', 'body' => [
  '<p>Pick a table on the left. Click a column header to sort. The
    <strong>Filter</strong> button opens a row of boxes under the headers — type
    into one and only the matching rows stay.</p>',
  ['fig', '03-table.webp', 'COUNTRY table',
   'COUNTRY, 194 rows and 86 columns. Under NAME_STID the editor prints the real name from the language file — the database itself only stores 2136.'],
  '<ul>
     <li><span class="badge text-bg-secondary">IDX</span> marks a column that indexes
       use. Those are read-only, and the next section explains why.</li>
     <li><span class="badge text-bg-secondary">TXT</span> marks a column that points
       into the language file. The name underneath the number comes from there.</li>
     <li>Big tables are paged; the page size is yours to choose at the bottom.</li>
   </ul>',
]],

['id' => 'edit', 'icon' => 'pencil-square', 'h' => 'Editing a row', 'body' => [
  '<p>The pencil at the start of a row opens every field of that row in one window.
    There is a search box, because some tables have more than two hundred columns.
    <strong>Save</strong> writes the values straight into the <code>.gdb</code>.</p>',
  ['fig', '04-edit-row.webp', 'Row editor',
   'Each field shows its type and, for text columns, the line from the language file. NULL has its own switch, because empty and "not set" are different things to the game.'],
  ['note', 'exclamation-triangle',
   'Fields that indexes are built on — <code>ID</code> in nearly every SP2 table — cannot be changed. An index is a separate tree of keys inside the file; change the value without rebuilding the tree and the game starts reading the wrong rows. Adding and deleting rows is blocked for the same reason.'],
]],

['id' => 'struct', 'icon' => 'diagram-3', 'h' => 'Table structure', 'body' => [
  '<p><strong>Structure</strong> lists the columns of a table with their real Firebird
    types, offsets and domains, and points out which ones look like links to other
    tables. Useful when you are working out what a column actually means.</p>',
  ['fig', '05-structure.webp', 'Structure of COUNTRY',
   'Types as Firebird itself stores them. The Notes column flags likely links, index membership and text pointers.'],
  '<p>The same list is available as a file: <em>Schema JSON</em> in the left panel
    hands you the whole schema for your own scripts.</p>',
]],

['id' => 'map', 'icon' => 'globe-americas', 'h' => 'The world map', 'body' => [
  '<p>The database has no map in it. SuperPower 2 stores 2 604 regions and 3 347
    cities, each city with a latitude and a longitude — so the editor draws a region
    as the ground that is nearer to its own cities than to anyone else\'s, then
    trims the result against real coastlines and borders.</p>',
  '<p>That is why the shapes are close but not exact, and why the map is honest about
    it in the line under the picture. What it gets exactly right is the part that
    matters here: <em>which region belongs to whom</em>, straight out of the table
    you are editing.</p>',
]],

['id' => 'map-who', 'icon' => 'flag', 'h' => 'Who owns what', 'body' => [
  ['fig', '06-map-country.webp', 'Map coloured by country',
   'Every region painted in the colour of its owner. The legend lists the largest owners; the rest fold into “and 180 more”.'],
  '<p>Hover a region and the tooltip names the region, its owner and its capital.
    Drag to pan, wheel to zoom, and the box next to the layer list highlights a
    single country so you can see its territory in one glance — including the bits
    of it that sit on other continents.</p>',
]],

['id' => 'map-lang', 'icon' => 'translate', 'h' => 'Language and religion', 'body' => [
  '<p>The <strong>Colour by</strong> list at the top left changes what the colours mean. Language and
    religion come from the <code>LANGUAGES</code> and <code>RELIGIONS</code> tables,
    which hold a share per region for every language and every faith — the map paints
    the one with the biggest share.</p>',
  ['fig', '07-map-language.webp', 'Dominant language', 'Dominant language per region.'],
  ['fig', '08-map-religion.webp', 'Dominant religion', 'Dominant religion per region.'],
  '<p>Two more layers show the <em>share</em> of one chosen language or faith as a
    gradient, which is the quickest way to find the regions a mod forgot about.</p>',
  ['note', 'info-circle',
   'These layers show the game\'s data, not the real world — and the game\'s data has its oddities. Brazil\'s dominant language comes out as English, Ukraine\'s as Russian. That is what is in <code>DATABASE.GDB</code>, and now you can see it and fix it.'],
]],

['id' => 'map-num', 'icon' => 'bar-chart', 'h' => 'Government and numbers', 'body' => [
  ['fig', '09-map-government.webp', 'Government type', 'Government type per country, from GVT_TYPE.'],
  '<p>Numeric layers — population, infrastructure, telecommunication level — use one
    gradient with the smallest, middle and largest value written on the legend.
    The scale is by rank rather than by value, otherwise China and India flatten
    everything else into a single shade.</p>',
  ['fig', '10-map-population.webp', 'Population',
   'Population by region. The legend gives the smallest, the middle and the largest value.'],
]],

['id' => 'map-rel', 'icon' => 'arrow-left-right', 'h' => 'Relations and treaties', 'body' => [
  '<p>Pick <em>Relations of one country</em> and choose a country: every other country
    is painted by how it feels about that one, hostile through neutral to allied,
    out of the <code>RELATIONS</code> table.</p>',
  ['fig', '11-map-relations.webp', 'Relations towards the United States',
   'How the world feels about the United States. The chosen country keeps its own colour.'],
  '<p><em>Treaty members</em> does the same: pick a treaty from
    <code>TREATY</code> and its members light up.</p>',
  ['fig', '12-map-treaty.webp', 'NATO members',
   'Members of a treaty — here NATO — against everyone else.'],
]],

['id' => 'map-mil', 'icon' => 'shield', 'h' => 'Armies, missiles, strength', 'body' => [
  '<p>The <strong>Layers</strong> menu on the right switches on two sets of points:
    army units from <code>UNITS</code> and missiles from <code>MISSILE</code>, each
    drawn where the game says it stands.</p>',
  ['fig', '13-map-layers-menu.webp', 'Layers menu',
   'Troops and missiles as separate switches, on top of whatever the colours are showing.'],
  '<p><em>Total units</em> sums what each country actually owns and
    shades the countries by the total, so you can see at a glance who the heavy
    players in this database are.</p>',
  ['fig', '14-map-force.webp', 'Total units',
   'Countries shaded by the strength of the forces they own, with unit and missile positions on top.'],
  ['note', 'info-circle',
   'SuperPower 2 has no table of unit type names, so the editor labels a type with an actual design that uses it — a real sample out of <code>DESIGN</code>, not a guess.'],
]],

['id' => 'map-edit', 'icon' => 'cursor', 'h' => 'Editing from the map', 'body' => [
  '<p>The map is not a picture — it is a second way into the same rows.</p>',
  '<ul>
     <li><strong>Click a region</strong> and its row from <code>REGION</code> opens in
       the usual editing window.</li>
     <li><strong>The owner switch</strong> in that window hands the region to another
       country on the spot.</li>
     <li><strong>Shift + drag</strong> draws a box over several regions and transfers
       all of them to one country in a single step. The map redraws immediately, so a
       mistake is obvious at once — and the change log will undo it.</li>
   </ul>',
]],

['id' => 'merge', 'icon' => 'diagram-2', 'h' => 'Merging countries', 'body' => [
  '<p>Merging is the heavy one: everything belonging to one country — regions, cities,
    units, missiles, parties, treaties — moves to another, and the source country is
    deactivated. The relations matrix is left alone. It is how you build a mod where Yugoslavia never broke up, or where
    there are twelve countries on the map instead of a hundred and ninety-four.</p>',
  ['fig', '15-merge.webp', 'Merge countries',
   'Name the target country and the countries to merge in; the editor lists what is going to move before anything happens.'],
  '<p>Nothing moves until you confirm, and the whole merge lands in the change log as
    one step you can undo.</p>',
]],

['id' => 'replace', 'icon' => 'arrow-repeat', 'h' => 'Find and replace', 'body' => [
  '<p>One column at a time, with a preview. Choose a table and a column, say what to
    match — equals, contains, greater than, empty — and what to put there instead.
    <strong>Preview</strong> shows exactly which rows would change, and how many.</p>',
  ['fig', '16-replace.webp', 'Find and replace with preview',
   'Preview first: the rows that would change, before and after, with a count.'],
]],

['id' => 'check', 'icon' => 'clipboard-check', 'h' => 'Checking the database', 'body' => [
  '<p>The check looks for real breakage only — nothing is guessed:</p>',
  '<ul>
     <li>links pointing at rows that do not exist;</li>
     <li>text pointers missing from the attached language file;</li>
     <li>active countries with no regions at all;</li>
     <li>capitals sitting in another country;</li>
     <li>duplicate keys, and regions without a single city.</li>
   </ul>',
  ['fig', '17-check.webp', 'Database check',
   'Findings grouped by kind. Every line is a link straight into the row that needs fixing.'],
  '<p>This is worth running after a merge or a large replace, and again before you take
    the file back into the game.</p>',
]],

['id' => 'find', 'icon' => 'binoculars', 'h' => 'Global search', 'body' => [
  '<p>One box, every table, plus the language file. Type a name and you get the rows
    that mention it wherever they live.</p>',
  ['fig', '18-find.webp', 'Global search',
   'Searching for "Poland" across all tables and the language file at once.'],
]],

['id' => 'log', 'icon' => 'clock-history', 'h' => 'Change log and undo', 'body' => [
  '<p>Every change this session — a row, a replace, a merge, a transfer on the map —
    is recorded with the values it replaced. <strong>Undo</strong> puts the previous
    values back.</p>',
  ['fig', '19-log.webp', 'Change log',
   'What changed, where, how many cells, and undo next to each step.'],
  '<p>Besides that, a copy of the file is put in the project\'s <code>backups</code>
    folder before every single edit. The log covers the session; the copies stay for
    as long as the project does.</p>',
]],

['id' => 'gst', 'icon' => 'translate', 'h' => 'Language files (.gst)', 'body' => [
  '<p>A <code>.gst</code> is an index plus a block of UTF-16 text — it is where every
    name the game shows you actually lives. The Languages tab lists all of them with
    search, and a line is edited in place.</p>',
  ['fig', '20-languages.webp', 'Language file',
   'StringTable.english.gst — the line numbers here are the numbers the database stores in its *_STID columns.'],
  '<p>Several language files can be open at once; the one you pick is the one the
    database tables use when they print names under the numbers. Renaming a country
    for the whole game is a single line here — not an edit in the database.</p>',
]],

['id' => 'ui', 'icon' => 'palette', 'h' => 'Appearance', 'body' => [
  '<p>Ten themes, your own accent and text colour, ten fonts, a size and a density
    switch. It is all applied in the browser and kept there, so it costs nothing and
    follows you around the editor.</p>',
  ['fig', '21-appearance.webp', 'Appearance window',
   'Themes, accent, font and density, with a live preview at the bottom.'],
]],

['id' => 'mobile', 'icon' => 'phone', 'h' => 'On a phone', 'body' => [
  '<p>The layout folds: the table list slides in from the side, the toolbar wraps, and
    the map takes the full width with touch pan and pinch zoom.</p>',
  ['fig', '23-mobile.webp', 'The editor on a phone', 'The same editor at 414 pixels wide.'],
]],

['id' => 'save', 'icon' => 'download', 'h' => 'Getting your work back', 'body' => [
  '<p><strong>Download all (.zip)</strong> at the top packs the database and every
    language file you have open into one archive — the same names the game expects,
    ready to drop back into <code>Extras\\</code> or into your mod\'s folder. Single
    files can be downloaded on their own from the left panel.</p>',
  '<p>The editor never writes into your game by itself.</p>',
]],

['id' => 'safety', 'icon' => 'shield-check', 'h' => 'What it will not do', 'body' => [
  '<ul>
     <li><strong>Indexed fields stay read-only.</strong> Changing an <code>ID</code>
       without rebuilding the index tree would break the file quietly, and a quiet
       break in a database the game reads every turn is the worst kind.</li>
     <li><strong>Rows are not added or deleted</strong> — same reason.</li>
     <li><strong>BLOB columns</strong> are shown as <code>[BLOB]</code> and left alone.</li>
     <li><strong>The server\'s own folders are out of reach.</strong> Opening files
       straight from a game folder is switched off unless you run the editor at home
       and turn it on yourself.</li>
   </ul>',
  '<p class="text-body-secondary mb-0">Everything else is fair game. Have fun with it.</p>',
]],

]];
