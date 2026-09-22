<?php

return [
'name' => 'Deutsch',
'lead' => 'Alles, was der Editor kann, der Reihe nach und mit Screenshots.',

'sections' => [

['id' => 'start', 'icon' => 'rocket-takeoff', 'h' => 'Erste Schritte', 'body' => [
  '<p>Der Editor öffnet die Dateien des Spiels selbst. Es wird nichts installiert und
    nichts umgewandelt: Er liest <code>DATABASE.GDB</code> — eine Firebird-1.5-Datenbank —
    und die Textdateien <code>StringTable.*.gst</code> Byte für Byte, in reinem PHP.</p>',
  '<ol>
     <li>Suchen Sie die Dateien des Spiels. Eine saubere Installation bewahrt sie in
       <code>SuperPower 2\\Extras\\</code>; ein Mod legt seine eigene Kopie ab in
       <code>SuperPower 2\\MODS\\&lt;Name des Mods&gt;\\</code>.</li>
     <li>Ziehen Sie <code>DATABASE.GDB</code> auf das Upload-Feld, oder klicken Sie es an
       und wählen Sie die Datei aus.</li>
     <li>Wechseln Sie auf den Reiter <strong>Sprachen</strong> und laden Sie auch eine
       <code>StringTable.*.gst</code> hoch. Sie ist optional, aber ohne sie sind Länder
       und Städte nur Zahlen.</li>
   </ol>',
  '<p>Eine <code>.zip</code> mit mehreren dieser Dateien auf einmal geht ebenfalls: Die
    Datenbank landet im einen Reiter, jede Sprachdatei im anderen.</p>',
  ['fig', '01-upload.webp', 'Upload-Fenster',
   'Datei fallen lassen oder anklicken und auswählen. Der Balken zeigt Megabyte und Tempo; die Seite öffnet sich von selbst, sobald der Upload fertig ist.'],
  ['note', 'info-circle',
   'Ihre Dateien sind Kopien. Die Originale des Spiels werden nie angefasst — am Ende laden Sie die bearbeitete Kopie herunter.'],
]],

['id' => 'project', 'icon' => 'folder2-open', 'h' => 'Ihr Projekt und sein Link', 'body' => [
  '<p>Es gibt keine Anmeldung. In dem Moment, in dem Sie die Seite öffnen, bekommen Sie
    einen kurzen Projektcode, und alles, was Sie hochladen, landet in einem eigenen Ordner.
    Zwei Leute können in derselben Minute ihre eigene <code>DATABASE.GDB</code> hochladen,
    ohne sich gegenseitig ins Gehege zu kommen.</p>',
  '<p>Der Code steckt in einem Cookie, also denkt man im Normalfall nie an ihn. Er steht
    auch im Link: Bewahren Sie diesen Link auf, und das Projekt öffnet sich auf einem
    anderen Rechner, auf dem Handy oder nachdem die Cookies gelöscht wurden. Sie finden
    ihn unten in der linken Leiste und auf der Startseite unter <em>Ihr Projekt</em>.</p>',
  ['fig', '22-project.webp', 'Projektfenster',
   'Der Link zum Projekt, der Code, wie viel Platz es belegt, und eine Schaltfläche, die ein leeres Projekt beginnt.'],
  '<p>Ein Projekt wird sieben Tage nach dem letzten Besuch gelöscht. <strong>Schließen</strong>
    nimmt eine Datei absichtlich aus dem Projekt heraus — eine Kopie bleibt ohnehin im
    Ordner <code>backups</code> des Projekts.</p>',
]],

['id' => 'overview', 'icon' => 'speedometer2', 'h' => 'Was der Datenbank-Reiter zeigt', 'body' => [
  '<p>Mit geöffneter Datenbank landen Sie auf einer Übersicht: wie viele Tabellen, Zeilen
    und Spalten es gibt, welche Tabellen die großen sind, und Schnellzugriffe auf alles, was
    sich zu tun lohnt.</p>',
  ['fig', '02-overview.webp', 'Übersicht der Datenbank',
   'SuperPower 2 liefert 28 Tabellen und rund 62 800 Zeilen mit. Die grüne Markierung links heißt, dass eine Sprachdatei angehängt ist — die Zahlen erscheinen also als Namen.'],
]],

['id' => 'table', 'icon' => 'table', 'h' => 'Eine Tabelle durchsehen', 'body' => [
  '<p>Wählen Sie links eine Tabelle. Ein Klick auf eine Spaltenüberschrift sortiert. Die
    Schaltfläche <strong>Filtern</strong> öffnet eine Reihe Felder unter den Überschriften:
    Tippen Sie in eins hinein, und nur die passenden Zeilen bleiben stehen.</p>',
  ['fig', '03-table.webp', 'Tabelle COUNTRY',
   'COUNTRY, 194 Zeilen und 86 Spalten. Unter NAME_STID schreibt der Editor den echten Namen aus der Sprachdatei — die Datenbank selbst hält nur 2136 fest.'],
  '<ul>
     <li><span class="badge text-bg-secondary">IDX</span> kennzeichnet eine Spalte, die
       Indizes benutzen. Die sind schreibgeschützt, und der nächste Abschnitt erklärt,
       warum.</li>
     <li><span class="badge text-bg-secondary">TXT</span> kennzeichnet eine Spalte, die in
       die Sprachdatei zeigt. Der Name unter der Zahl kommt von dort.</li>
     <li>Große Tabellen werden auf Seiten verteilt; die Seitengröße wählen Sie unten selbst.</li>
   </ul>',
]],

['id' => 'edit', 'icon' => 'pencil-square', 'h' => 'Eine Zeile bearbeiten', 'body' => [
  '<p>Der Stift am Anfang einer Zeile öffnet alle ihre Felder in einem Fenster. Es gibt ein
    Suchfeld, denn manche Tabellen haben über zweihundert Spalten. <strong>Speichern</strong>
    schreibt die Werte unmittelbar in die <code>.gdb</code>.</p>',
  ['fig', '04-edit-row.webp', 'Zeileneditor',
   'Jedes Feld zeigt seinen Typ und, bei Textspalten, die Zeile aus der Sprachdatei. NULL hat einen eigenen Schalter, denn leer und „nicht gesetzt“ sind für das Spiel zweierlei.'],
  ['note', 'exclamation-triangle',
   'Felder, auf denen Indizes aufbauen — <code>ID</code> in fast jeder SP2-Tabelle — lassen sich nicht ändern. Ein Index ist ein eigener Schlüsselbaum in der Datei: Ändern Sie den Wert, ohne den Baum neu zu bauen, liest das Spiel die falschen Zeilen. Zeilen hinzufügen und löschen ist aus demselben Grund gesperrt.'],
]],

['id' => 'struct', 'icon' => 'diagram-3', 'h' => 'Aufbau der Tabelle', 'body' => [
  '<p><strong>Struktur</strong> listet die Spalten einer Tabelle mit ihren echten
    Firebird-Typen, Offsets und Domänen auf und weist auf die hin, die nach Verweisen auf
    andere Tabellen aussehen. Nützlich, wenn Sie herausfinden wollen, was eine Spalte
    eigentlich bedeutet.</p>',
  ['fig', '05-structure.webp', 'Aufbau von COUNTRY',
   'Die Typen so, wie Firebird selbst sie ablegt. Die Spalte Hinweise markiert wahrscheinliche Verweise, Indexzugehörigkeit und Textzeiger.'],
  '<p>Dieselbe Liste gibt es als Datei: <em>Schema-JSON</em> in der linken Leiste
    übergibt Ihnen das ganze Schema für Ihre eigenen Skripte.</p>',
]],

['id' => 'map', 'icon' => 'globe-americas', 'h' => 'Die Weltkarte', 'body' => [
  '<p>In der Datenbank steckt keine Karte. SuperPower 2 hält 2 604 Regionen und 3 347
    Städte fest, jede Stadt mit Breiten- und Längengrad — der Editor zeichnet eine Region
    also als den Boden, der ihren eigenen Städten näher ist als denen aller anderen, und
    beschneidet das Ergebnis dann an echten Küsten und Grenzen.</p>',
  '<p>Darum sind die Formen nah dran, aber nicht exakt, und darum sagt die Karte das in der
    Zeile unter dem Bild auch offen. Ganz genau richtig ist, worauf es hier ankommt:
    <em>welche Region wem gehört</em>, direkt aus der Tabelle, die Sie gerade bearbeiten.</p>',
]],

['id' => 'map-who', 'icon' => 'flag', 'h' => 'Wem was gehört', 'body' => [
  ['fig', '06-map-country.webp', 'Karte nach Ländern eingefärbt',
   'Jede Region in der Farbe ihres Besitzers. Die Legende nennt die größten; der Rest klappt sich zu „und 180 weitere“ zusammen.'],
  '<p>Fahren Sie über eine Region, und die Kurzinfo nennt Region, Besitzer und Hauptstadt.
    Ziehen verschiebt, das Mausrad zoomt, und das Feld neben der Ebenenliste hebt ein
    einzelnes Land hervor: So sehen Sie sein Gebiet auf einen Blick, samt der Stücke, die
    auf anderen Kontinenten liegen.</p>',
]],

['id' => 'map-lang', 'icon' => 'translate', 'h' => 'Sprache und Religion', 'body' => [
  '<p>Die Liste <strong>Einfärben nach</strong> links oben ändert, was die Farben bedeuten. Sprache und Religion
    kommen aus den Tabellen <code>LANGUAGES</code> und <code>RELIGIONS</code>, die je
    Region einen Anteil für jede Sprache und jedes Bekenntnis halten — die Karte malt das
    mit dem größten Anteil.</p>',
  ['fig', '07-map-language.webp', 'Vorherrschende Sprache', 'Vorherrschende Sprache je Region.'],
  ['fig', '08-map-religion.webp', 'Vorherrschende Religion', 'Vorherrschende Religion je Region.'],
  '<p>Zwei weitere Ebenen zeigen den <em>Anteil</em> einer ausgewählten Sprache oder eines
    Bekenntnisses als Verlauf — der schnellste Weg, die Regionen zu finden, die ein Mod
    vergessen hat.</p>',
  ['note', 'info-circle',
   'Diese Ebenen zeigen die Daten des Spiels, nicht die wirkliche Welt — und die Daten des Spiels haben ihre Eigenheiten. Als vorherrschende Sprache Brasiliens kommt Englisch heraus, für die Ukraine Russisch. Genau das steht in <code>DATABASE.GDB</code>, und nun können Sie es sehen und richtigstellen.'],
]],

['id' => 'map-num', 'icon' => 'bar-chart', 'h' => 'Regierung und Zahlen', 'body' => [
  ['fig', '09-map-government.webp', 'Regierungsform', 'Regierungsform je Land, aus GVT_TYPE.'],
  '<p>Zahlenebenen — Bevölkerung, Infrastruktur, Stand der Telekommunikation — verwenden
    einen einzigen Verlauf, mit dem kleinsten, dem mittleren und dem größten Wert in der
    Legende. Die Skala geht nach Rang und nicht nach Wert; sonst würden China und Indien
    alles andere in einen einzigen Ton drücken.</p>',
  ['fig', '10-map-population.webp', 'Bevölkerung',
   'Bevölkerung je Region. Die Legende nennt den kleinsten, den mittleren und den größten Wert.'],
]],

['id' => 'map-rel', 'icon' => 'arrow-left-right', 'h' => 'Beziehungen und Verträge', 'body' => [
  '<p>Nehmen Sie <em>Beziehungen eines Landes</em> und wählen Sie ein Land: Jedes andere
    wird danach eingefärbt, wie es zu diesem steht — von feindlich über neutral bis
    verbündet, aus der Tabelle <code>RELATIONS</code>.</p>',
  ['fig', '11-map-relations.webp', 'Beziehungen zu den Vereinigten Staaten',
   'Wie die Welt zu den Vereinigten Staaten steht. Das gewählte Land behält seine eigene Farbe.'],
  '<p><em>Vertragsmitglieder</em> macht dasselbe: Wählen Sie einen
    Vertrag aus <code>TREATY</code>, und seine Mitglieder leuchten auf.</p>',
  ['fig', '12-map-treaty.webp', 'Mitglieder der NATO',
   'Die Mitglieder eines Vertrags — hier der NATO — gegenüber allen anderen.'],
]],

['id' => 'map-mil', 'icon' => 'shield', 'h' => 'Armeen, Raketen, Stärke', 'body' => [
  '<p>Das Menü <strong>Ebenen</strong> rechts schaltet zwei Punktmengen an: die Einheiten aus
    <code>UNITS</code> und die Raketen aus <code>MISSILE</code>, jede dort gezeichnet, wo
    das Spiel sie stehen sieht.</p>',
  ['fig', '13-map-layers-menu.webp', 'Ebenenmenü',
   'Truppen und Raketen als getrennte Schalter, über dem, was die Farben gerade zeigen.'],
  '<p><em>Einheiten gesamt</em> zählt zusammen, was jedes Land wirklich besitzt,
    und schattiert die Länder nach der Summe: So sieht man auf einen Blick, welche Länder in dieser
    Datenbank die Schwergewichte sind.</p>',
  ['fig', '14-map-force.webp', 'Einheiten gesamt',
   'Länder nach der Stärke ihrer Streitkräfte schattiert, darüber die Standorte von Einheiten und Raketen.'],
  ['note', 'info-circle',
   'SuperPower 2 hat keine Tabelle mit Namen der Einheitentypen, also beschriftet der Editor einen Typ mit einem Entwurf, der ihn tatsächlich benutzt — eine echte Probe aus <code>DESIGN</code>, keine Vermutung.'],
]],

['id' => 'map-edit', 'icon' => 'cursor', 'h' => 'Von der Karte aus bearbeiten', 'body' => [
  '<p>Die Karte ist kein Bild — sie ist ein zweiter Weg zu denselben Zeilen.</p>',
  '<ul>
     <li><strong>Klicken Sie auf eine Region</strong>, und ihre Zeile aus <code>REGION</code>
       öffnet sich im gewohnten Bearbeitungsfenster.</li>
     <li><strong>Der Besitzerschalter</strong> in diesem Fenster gibt die Region auf der
       Stelle an ein anderes Land.</li>
     <li><strong>Umschalt + Ziehen</strong> zieht einen Rahmen über mehrere Regionen und
       überträgt sie alle in einem Schritt an ein Land. Die Karte zeichnet sich sofort neu,
       ein Fehler fällt also gleich auf — und das Änderungsprotokoll nimmt ihn zurück.</li>
   </ul>',
]],

['id' => 'merge', 'icon' => 'diagram-2', 'h' => 'Länder zusammenlegen', 'body' => [
  '<p>Das Zusammenlegen ist der schwere Brocken: Alles, was einem Land gehört — Regionen,
    Städte, Einheiten, Parteien, Verträge — geht an ein anderes über, und das
    Ausgangsland wird stillgelegt. Die Beziehungsmatrix bleibt unangetastet. So baut man einen Mod, in dem Jugoslawien nie zerfallen
    ist, oder einen, auf dessen Karte zwölf Länder statt hundertvierundneunzig stehen.</p>',
  ['fig', '15-merge.webp', 'Länder zusammenlegen',
   'Zielland und die Länder benennen, die aufgehen sollen; der Editor listet auf, was umziehen wird, bevor überhaupt etwas passiert.'],
  '<p>Nichts zieht um, ehe Sie bestätigen, und das ganze Zusammenlegen landet im
    Änderungsprotokoll als ein einziger Schritt, den Sie zurücknehmen können.</p>',
]],

['id' => 'replace', 'icon' => 'arrow-repeat', 'h' => 'Suchen und ersetzen', 'body' => [
  '<p>Eine Spalte auf einmal, mit Vorschau. Wählen Sie Tabelle und Spalte, sagen Sie, was
    zutreffen soll — gleich, enthält, größer als, leer — und was stattdessen hineinkommt.
    Die <strong>Vorschau</strong> zeigt genau, welche Zeilen sich ändern würden und wie
    viele.</p>',
  ['fig', '16-replace.webp', 'Suchen und ersetzen mit Vorschau',
   'Erst die Vorschau: die Zeilen, die sich ändern würden, vorher und nachher, mit Anzahl.'],
]],

['id' => 'check', 'icon' => 'clipboard-check', 'h' => 'Die Datenbank prüfen', 'body' => [
  '<p>Die Prüfung sucht nur echte Fehler; geraten wird nichts:</p>',
  '<ul>
     <li>Verweise, die auf Zeilen zeigen, die es nicht gibt;</li>
     <li>Textzeiger, die in der angehängten Sprachdatei fehlen;</li>
     <li>aktive Länder ganz ohne Regionen;</li>
     <li>Hauptstädte, die in einem anderen Land liegen;</li>
     <li>doppelte Schlüssel und Regionen ohne eine einzige Stadt.</li>
   </ul>',
  ['fig', '17-check.webp', 'Datenbankprüfung',
   'Die Funde nach Art gruppiert. Jeder Eintrag führt direkt zu der Zeile, die berichtigt werden muss.'],
  '<p>Sie lohnt sich nach einem Zusammenlegen oder einem großen Ersetzen — und noch einmal,
    bevor Sie die Datei zurück ins Spiel nehmen.</p>',
]],

['id' => 'find', 'icon' => 'binoculars', 'h' => 'Globale Suche', 'body' => [
  '<p>Ein Feld, alle Tabellen, dazu die Sprachdatei. Tippen Sie einen Namen, und Sie bekommen
    die Zeilen, die ihn erwähnen, wo immer sie auch stehen.</p>',
  ['fig', '18-find.webp', 'Globale Suche',
   'Suche nach „Poland“ über alle Tabellen und die Sprachdatei zugleich.'],
]],

['id' => 'log', 'icon' => 'clock-history', 'h' => 'Änderungsprotokoll und Rückgängig', 'body' => [
  '<p>Jede Änderung dieser Sitzung — eine Zeile, ein Ersetzen, ein Zusammenlegen, eine
    Übertragung auf der Karte — wird mit den Werten festgehalten, die sie verdrängt hat.
    <strong>Rückgängig</strong> stellt die vorigen Werte wieder her.</p>',
  ['fig', '19-log.webp', 'Änderungsprotokoll',
   'Was sich geändert hat, wo, wie viele Zellen, und neben jedem Schritt „Rückgängig“.'],
  '<p>Außerdem wird vor jeder einzelnen Änderung eine Kopie der Datei in den Ordner
    <code>backups</code> des Projekts gelegt. Das Protokoll deckt die Sitzung ab; die Kopien
    bleiben, solange das Projekt bleibt.</p>',
]],

['id' => 'gst', 'icon' => 'translate', 'h' => 'Sprachdateien (.gst)', 'body' => [
  '<p>Eine <code>.gst</code> ist ein Index plus ein Block UTF-16-Text — dort wohnt in
    Wahrheit jeder Name, den das Spiel Ihnen zeigt. Der Reiter Sprachen listet sie alle mit
    Suche auf, und eine Zeile wird an Ort und Stelle bearbeitet.</p>',
  ['fig', '20-languages.webp', 'Sprachdatei',
   'StringTable.english.gst — die Zeilennummern hier sind die Zahlen, die die Datenbank in ihren *_STID-Spalten hält.'],
  '<p>Mehrere Sprachdateien können gleichzeitig offen sein; die, die Sie wählen, benutzen die
    Tabellen der Datenbank, wenn sie die Namen unter die Zahlen schreiben. Ein Land für das
    ganze Spiel umzubenennen ist hier eine einzige Zeile — keine Änderung in der Datenbank.</p>',
]],

['id' => 'ui', 'icon' => 'palette', 'h' => 'Darstellung', 'body' => [
  '<p>Zehn Themen, Ihre eigene Akzent- und Textfarbe, zehn Schriften, eine Größe und ein
    Schalter für die Dichte. Alles wird im Browser angewandt und bleibt dort, kostet also
    nichts und begleitet Sie durch den ganzen Editor.</p>',
  ['fig', '21-appearance.webp', 'Fenster „Darstellung“',
   'Thema, Akzent, Schrift und Dichte, mit lebendiger Vorschau unten.'],
]],

['id' => 'mobile', 'icon' => 'phone', 'h' => 'Auf dem Handy', 'body' => [
  '<p>Das Layout klappt zusammen: Die Tabellenliste fährt von der Seite herein, die
    Werkzeugleiste bricht um, und die Karte nimmt die volle Breite, mit Schieben per Finger
    und Zoom mit zwei Fingern.</p>',
  ['fig', '23-mobile.webp', 'Der Editor auf einem Handy', 'Derselbe Editor bei 414 Pixeln Breite.'],
]],

['id' => 'save', 'icon' => 'download', 'h' => 'Ihre Arbeit zurückholen', 'body' => [
  '<p><strong>Alles herunterladen (.zip)</strong> oben packt die Datenbank und jede offene
    Sprachdatei in ein Archiv — mit genau den Namen, die das Spiel erwartet, bereit, wieder
    nach <code>Extras\\</code> oder in den Ordner Ihres Mods gelegt zu werden. Einzelne
    Dateien können Sie für sich aus der linken Leiste herunterladen.</p>',
  '<p>Der Editor schreibt von sich aus nie in Ihr Spiel.</p>',
]],

['id' => 'safety', 'icon' => 'shield-check', 'h' => 'Was er nicht tun wird', 'body' => [
  '<ul>
     <li><strong>Indizierte Felder bleiben schreibgeschützt.</strong> Eine <code>ID</code> zu
       ändern, ohne den Indexbaum neu zu bauen, würde die Datei unbemerkt beschädigen — und ein
       unbemerkter Schaden in einer Datenbank, die das Spiel jede Runde liest, ist die schlimmste Sorte.</li>
     <li><strong>Zeilen werden nicht hinzugefügt und nicht gelöscht</strong> — aus demselben
       Grund.</li>
     <li><strong>BLOB-Spalten</strong> erscheinen als <code>[BLOB]</code> und bleiben
       unangetastet.</li>
     <li><strong>Die Ordner des Servers selbst sind außer Reichweite.</strong> Dateien direkt
       aus einem Spielordner zu öffnen ist ausgeschaltet, außer Sie lassen den Editor zu
       Hause laufen und schalten es selbst ein.</li>
   </ul>',
  '<p class="text-body-secondary mb-0">Alles andere steht Ihnen offen. Viel Freude damit.</p>',
]],

]];
