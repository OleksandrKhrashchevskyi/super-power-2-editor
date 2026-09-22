<?php

return [
'name' => 'Nederlands',
'lead' => 'Alles wat de editor kan, op volgorde en met schermafbeeldingen.',

'sections' => [

['id' => 'start', 'icon' => 'rocket-takeoff', 'h' => 'Om te beginnen', 'body' => [
  '<p>De editor opent de bestanden van het spel zelf. Er wordt niets geïnstalleerd en niets
    omgezet: hij leest <code>DATABASE.GDB</code> — een Firebird-1.5-database — en de
    tekstbestanden <code>StringTable.*.gst</code> byte voor byte, in kale PHP.</p>',
  '<ol>
     <li>Zoek de bestanden van het spel. Een schone installatie bewaart ze in
       <code>SuperPower 2\\Extras\\</code>; een mod bewaart zijn eigen kopie in
       <code>SuperPower 2\\MODS\\&lt;naam van de mod&gt;\\</code>.</li>
     <li>Sleep <code>DATABASE.GDB</code> op het uploadvak, of klik erop en kies het bestand.</li>
     <li>Ga naar het tabblad <strong>Talen</strong> en upload ook een
       <code>StringTable.*.gst</code>. Dat is niet verplicht, maar zonder dat bestand zijn
       landen en steden alleen maar getallen.</li>
   </ol>',
  '<p>Een <code>.zip</code> met meerdere van die bestanden tegelijk kan ook: de database gaat
    naar het ene tabblad en elk taalbestand naar het andere.</p>',
  ['fig', '01-upload.webp', 'Uploadscherm',
   'Sleep het bestand hierheen of klik om het te kiezen. De balk toont megabytes en snelheid; de pagina opent vanzelf zodra het uploaden klaar is.'],
  ['note', 'info-circle',
   'Jouw bestanden zijn kopieën. De originelen van het spel worden nooit aangeraakt — aan het eind download je de bewerkte kopie.'],
]],

['id' => 'project', 'icon' => 'folder2-open', 'h' => 'Jouw project en zijn koppeling', 'body' => [
  '<p>Er is geen registratie. Zodra je de pagina opent krijg je een korte projectcode, en
    alles wat je uploadt woont in een eigen map. Twee mensen kunnen in dezelfde minuut hun
    eigen <code>DATABASE.GDB</code> uploaden zonder aan elkaars werk te komen.</p>',
  '<p>De code zit in een cookie, dus normaal denk je er nooit aan. Hij staat ook in de
    koppeling: bewaar die koppeling en het project opent op een andere computer, op een
    telefoon, of nadat de cookies gewist zijn. Je vindt hem onderaan in het linkerpaneel en
    op de voorpagina, onder <em>Jouw project</em>.</p>',
  ['fig', '22-project.webp', 'Projectvenster',
   'De koppeling naar het project, de code, hoeveel ruimte het inneemt, en een knop die een leeg project begint.'],
  '<p>Een project wordt zeven dagen na het laatste bezoek verwijderd. <strong>Sluiten</strong>
    haalt een bestand met opzet uit het project — er blijft hoe dan ook een kopie in de map
    <code>backups</code> van het project.</p>',
]],

['id' => 'overview', 'icon' => 'speedometer2', 'h' => 'Wat het databasetabblad toont', 'body' => [
  '<p>Met een geopende database kom je uit op een overzicht: hoeveel tabellen, rijen en kolommen
    er zijn, welke tabellen de grote zijn, en snelkoppelingen naar alles wat de moeite waard
    is.</p>',
  ['fig', '02-overview.webp', 'Overzicht van de database',
   'SuperPower 2 komt met 28 tabellen en ongeveer 62 800 rijen. Het groene label links betekent dat er een taalbestand bij zit, dus de getallen verschijnen als namen.'],
]],

['id' => 'table', 'icon' => 'table', 'h' => 'Door een tabel bladeren', 'body' => [
  '<p>Kies links een tabel. Klik op een kolomkop om te sorteren. De knop
    <strong>Filteren</strong> opent een rij vakjes onder de koppen: typ in een ervan en alleen
    de passende rijen blijven staan.</p>',
  ['fig', '03-table.webp', 'Tabel COUNTRY',
   'COUNTRY, 194 rijen en 86 kolommen. Onder NAME_STID schrijft de editor de echte naam uit het taalbestand — de database zelf bewaart alleen 2136.'],
  '<ul>
     <li><span class="badge text-bg-secondary">IDX</span> markeert een kolom die indexen
       gebruiken. Die zijn alleen-lezen, en het volgende stuk legt uit waarom.</li>
     <li><span class="badge text-bg-secondary">TXT</span> markeert een kolom die naar het
       taalbestand wijst. De naam onder het getal komt daarvandaan.</li>
     <li>Grote tabellen worden over pagina’s verdeeld; de paginagrootte kies je zelf, onderaan.</li>
   </ul>',
]],

['id' => 'edit', 'icon' => 'pencil-square', 'h' => 'Een rij bewerken', 'body' => [
  '<p>Het potlood aan het begin van een rij opent al haar velden in één venster. Er is een
    zoekvakje, want sommige tabellen hebben meer dan tweehonderd kolommen.
    <strong>Opslaan</strong> schrijft de waarden rechtstreeks in de <code>.gdb</code>.</p>',
  ['fig', '04-edit-row.webp', 'Rij-editor',
   'Elk veld toont zijn type en, bij tekstkolommen, de regel uit het taalbestand. NULL heeft een eigen schakelaar, want leeg en «niet ingevuld» zijn voor het spel twee verschillende dingen.'],
  ['note', 'exclamation-triangle',
   'Velden waarop indexen rusten — <code>ID</code> in bijna elke SP2-tabel — kunnen niet gewijzigd worden. Een index is een aparte sleutelboom binnen het bestand: verander je de waarde zonder de boom opnieuw te bouwen, dan gaat het spel de verkeerde rijen lezen. Rijen toevoegen en verwijderen is om dezelfde reden geblokkeerd.'],
]],

['id' => 'struct', 'icon' => 'diagram-3', 'h' => 'Structuur van de tabel', 'body' => [
  '<p><strong>Structuur</strong> somt de kolommen van een tabel op met hun echte
    Firebird-types, offsets en domeinen, en wijst aan welke op koppelingen naar andere
    tabellen lijken. Handig wanneer je uitzoekt wat een kolom eigenlijk betekent.</p>',
  ['fig', '05-structure.webp', 'Structuur van COUNTRY',
   'De types zoals Firebird ze zelf bewaart. De kolom Opmerkingen markeert waarschijnlijke koppelingen, lidmaatschap van indexen en tekstverwijzingen.'],
  '<p>Dezelfde lijst bestaat als bestand: <em>Schema-JSON</em> in het linkerpaneel geeft je
    het hele schema voor je eigen scripts.</p>',
]],

['id' => 'map', 'icon' => 'globe-americas', 'h' => 'De wereldkaart', 'body' => [
  '<p>In de database zit geen kaart. SuperPower 2 bewaart 2 604 regio’s en 3 347 steden, elke
    stad met een breedte- en een lengtegraad — de editor tekent een regio dus als de grond die
    dichter bij haar eigen steden ligt dan bij die van wie dan ook, en snijdt het resultaat
    daarna bij op echte kustlijnen en grenzen.</p>',
  '<p>Daarom komen de vormen in de buurt maar zijn ze niet exact, en daarom zegt de kaart dat
    ook eerlijk in de regel onder het beeld. Wat ze helemaal goed heeft, is waar het hier om
    gaat: <em>welke regio’s van wie zijn</em>, rechtstreeks uit de tabel die je aan het bewerken
    bent.</p>',
]],

['id' => 'map-who', 'icon' => 'flag', 'h' => 'Wie wat bezit', 'body' => [
  ['fig', '06-map-country.webp', 'Kaart gekleurd per land',
   'Elke regio gekleurd in de kleur van haar eigenaar. De legenda noemt de grootste eigenaren; de rest vouwt zich samen tot «en nog 180».'],
  '<p>Ga over een regio met de muis en de tekstballon noemt de regio, haar eigenaar en haar
    hoofdstad. Slepen verschuift, het wiel zoomt, en het vakje naast de lagenlijst licht één
    land uit: zo zie je zijn gebied in één oogopslag, inclusief de stukken die op andere
    werelddelen liggen.</p>',
]],

['id' => 'map-lang', 'icon' => 'translate', 'h' => 'Taal en religie', 'body' => [
  '<p>De lijst <strong>Kleuren op</strong> linksboven verandert wat de kleuren betekenen. Taal en religie komen uit
    de tabellen <code>LANGUAGES</code> en <code>RELIGIONS</code>, die per regio een aandeel
    bewaren voor elke taal en elk geloof — de kaart kleurt dat met het grootste aandeel.</p>',
  ['fig', '07-map-language.webp', 'Dominante taal', 'Dominante taal per regio.'],
  ['fig', '08-map-religion.webp', 'Dominante religie', 'Dominante religie per regio.'],
  '<p>Twee andere lagen tonen het <em>aandeel</em> van één gekozen taal of geloof als verloop —
    de snelste manier om de regio’s te vinden die een mod vergeten is.</p>',
  ['note', 'info-circle',
   'Deze lagen tonen de gegevens van het spel, niet de echte wereld — en de gegevens van het spel hebben hun eigenaardigheden. Als overheersende taal van Brazilië komt Engels eruit, voor Oekraïne Russisch. Dat staat nu eenmaal in <code>DATABASE.GDB</code>, en nu kun je het zien en rechtzetten.'],
]],

['id' => 'map-num', 'icon' => 'bar-chart', 'h' => 'Bestuur en getallen', 'body' => [
  ['fig', '09-map-government.webp', 'Regeringsvorm', 'Regeringsvorm per land, uit GVT_TYPE.'],
  '<p>Getalslagen — bevolking, infrastructuur, niveau van de telecommunicatie — gebruiken één
    verloop, met de kleinste, de middelste en de grootste waarde in de legenda. De schaal gaat
    op rangorde en niet op waarde; anders drukken China en India al het andere in één tint.</p>',
  ['fig', '10-map-population.webp', 'Bevolking',
   'Bevolking per regio. De legenda geeft de kleinste, de middelste en de grootste waarde.'],
]],

['id' => 'map-rel', 'icon' => 'arrow-left-right', 'h' => 'Betrekkingen en verdragen', 'body' => [
  '<p>Kies <em>Betrekkingen van één land</em> en daarna een land: alle andere worden gekleurd
    naar hoe ze tegenover dat land staan, van vijandig via neutraal tot bondgenoot, volgens de
    tabel <code>RELATIONS</code>.</p>',
  ['fig', '11-map-relations.webp', 'Betrekkingen tegenover de Verenigde Staten',
   'Hoe de wereld tegenover de Verenigde Staten staat. Het gekozen land houdt zijn eigen kleur.'],
  '<p><em>Verdragsleden</em> doet hetzelfde: kies een verdrag uit
    <code>TREATY</code> en zijn leden lichten op.</p>',
  ['fig', '12-map-treaty.webp', 'Leden van de NAVO',
   'De leden van een verdrag — hier de NAVO — tegenover alle anderen.'],
]],

['id' => 'map-mil', 'icon' => 'shield', 'h' => 'Legers, raketten, sterkte', 'body' => [
  '<p>Het menu <strong>Lagen</strong> rechts zet twee verzamelingen punten aan: de eenheden uit
    <code>UNITS</code> en de raketten uit <code>MISSILE</code>, elk getekend waar het spel zegt
    dat ze staan.</p>',
  ['fig', '13-map-layers-menu.webp', 'Lagenmenu',
   'Troepen en raketten als aparte schakelaars, boven op wat de kleuren laten zien.'],
  '<p><em>Totaal aantal eenheden</em> telt op wat elk land werkelijk bezit en arceert de
    landen naar het totaal: zo zie je in één oogopslag wie in deze database de zwaargewichten
    zijn.</p>',
  ['fig', '14-map-force.webp', 'Totaal aantal eenheden',
   'Landen gearceerd naar de sterkte van hun strijdkrachten, met daarboven de posities van eenheden en raketten.'],
  ['note', 'info-circle',
   'SuperPower 2 heeft geen tabel met namen van eenheidstypes, dus geeft de editor een type het etiket van een ontwerp dat het echt gebruikt — een echt voorbeeld uit <code>DESIGN</code>, geen gok.'],
]],

['id' => 'map-edit', 'icon' => 'cursor', 'h' => 'Bewerken vanaf de kaart', 'body' => [
  '<p>De kaart is geen plaatje — ze is een tweede ingang naar dezelfde rijen.</p>',
  '<ul>
     <li><strong>Klik op een regio</strong> en haar rij uit <code>REGION</code> gaat open in
       het gewone bewerkingsvenster.</li>
     <li><strong>De eigenaarskeuze</strong> in dat venster geeft de regio ter plekke aan een
       ander land.</li>
     <li><strong>Shift + slepen</strong> trekt een kader over meerdere regio’s en draagt ze
       allemaal in één keer over aan één land. De kaart tekent zich meteen opnieuw, dus een
       vergissing valt direct op — en het wijzigingslogboek maakt haar ongedaan.</li>
   </ul>',
]],

['id' => 'merge', 'icon' => 'diagram-2', 'h' => 'Landen samenvoegen', 'body' => [
  '<p>Samenvoegen is de zwaarste bewerking: alles wat van één land is — regio’s, steden, eenheden, raketten,
    partijen, verdragen — gaat over naar een ander, en het bronland wordt uitgeschakeld. Zo
    bouw je een mod waarin Joegoslavië nooit uiteenviel, of waarin er twaalf landen op de kaart
    staan in plaats van honderdvierennegentig.</p>',
  ['fig', '15-merge.webp', 'Landen samenvoegen',
   'Noem het ontvangende land en de landen die erin opgaan; de editor somt op wat er gaat verhuizen voordat er ook maar iets gebeurt.'],
  '<p>Er verhuist niets totdat je bevestigt, en de hele samenvoeging belandt in het
    wijzigingslogboek als één stap, die je ongedaan kunt maken.</p>',
]],

['id' => 'replace', 'icon' => 'arrow-repeat', 'h' => 'Zoeken en vervangen', 'body' => [
  '<p>Eén kolom tegelijk, met voorbeeld. Kies een tabel en een kolom, zeg wat moet overeenkomen
    — gelijk aan, bevat, groter dan, leeg — en wat er in de plaats komt. Het
    <strong>voorbeeld</strong> laat precies zien welke rijen zouden veranderen, en hoeveel.</p>',
  ['fig', '16-replace.webp', 'Zoeken en vervangen met voorbeeld',
   'Eerst het voorbeeld: de rijen die zouden veranderen, voor en na, met het aantal.'],
]],

['id' => 'check', 'icon' => 'clipboard-check', 'h' => 'Databasecontrole', 'body' => [
  '<p>De controle zoekt alleen echte breuken; er wordt niets geraden:</p>',
  '<ul>
     <li>koppelingen die naar rijen wijzen die niet bestaan;</li>
     <li>tekstverwijzingen die in het bijgevoegde taalbestand ontbreken;</li>
     <li>actieve landen zonder ook maar één regio;</li>
     <li>hoofdsteden die in een ander land liggen;</li>
     <li>dubbele sleutels, en regio’s zonder één enkele stad.</li>
   </ul>',
  ['fig', '17-check.webp', 'Databasecontrole',
   'De bevindingen gegroepeerd naar soort. Elke regel is een koppeling recht naar de rij die gerepareerd moet worden.'],
  '<p>Het loont de moeite na een samenvoeging of een grote vervanging, en nog een keer voordat je het
    bestand terugzet in het spel.</p>',
]],

['id' => 'find', 'icon' => 'binoculars', 'h' => 'Globaal zoeken', 'body' => [
  '<p>Eén vakje, alle tabellen, en het taalbestand erbij. Typ een naam en je krijgt de rijen die
    hem noemen, waar ze ook wonen.</p>',
  ['fig', '18-find.webp', 'Globaal zoeken',
   'Zoeken naar «Poland» in alle tabellen en het taalbestand tegelijk.'],
]],

['id' => 'log', 'icon' => 'clock-history', 'h' => 'Logboek en ongedaan maken', 'body' => [
  '<p>Elke wijziging van deze sessie — een rij, een vervanging, een samenvoeging, een overdracht
    op de kaart — wordt genoteerd met de waarden die ze verving. <strong>Ongedaan maken</strong>
    zet de vorige waarden terug.</p>',
  ['fig', '19-log.webp', 'Wijzigingslogboek',
   'Wat er veranderde, waar, hoeveel cellen, en naast elke stap het ongedaan maken.'],
  '<p>Daarbovenop wordt vóór elke afzonderlijke bewerking een kopie van het bestand in de map
    <code>backups</code> van het project gelegd. Het logboek dekt de sessie; de kopieën blijven
    zolang het project blijft.</p>',
]],

['id' => 'gst', 'icon' => 'translate', 'h' => 'Taalbestanden (.gst)', 'body' => [
  '<p>Een <code>.gst</code> is een index plus een blok UTF-16-tekst — daar woont werkelijk elke
    naam die het spel je laat zien. Het tabblad Talen somt ze allemaal op met zoekfunctie, en
    een regel bewerk je ter plekke.</p>',
  ['fig', '20-languages.webp', 'Taalbestand',
   'StringTable.english.gst — de regelnummers hier zijn de getallen die de database in haar *_STID-kolommen bewaart.'],
  '<p>Er kunnen meerdere taalbestanden tegelijk open staan; degene die je kiest gebruiken de
    tabellen van de database wanneer ze de namen onder de getallen schrijven. Een land voor het
    hele spel hernoemen is hier één regel — geen bewerking in de database.</p>',
]],

['id' => 'ui', 'icon' => 'palette', 'h' => 'Weergave', 'body' => [
  '<p>Tien thema’s, je eigen accent- en tekstkleur, tien lettertypes, een grootte en een
    schakelaar voor de dichtheid. Alles wordt in de browser toegepast en blijft daar, dus het
    kost niets en volgt je door de hele editor.</p>',
  ['fig', '21-appearance.webp', 'Het venster Weergave',
   'Thema’s, accent, lettertype en dichtheid, met een levend voorbeeld onderaan.'],
]],

['id' => 'mobile', 'icon' => 'phone', 'h' => 'Op de telefoon', 'body' => [
  '<p>De indeling vouwt zich op: de tabellenlijst schuift van opzij binnen, de werkbalk loopt door
    op meerdere regels, en de kaart neemt de volle breedte, met schuiven met de vinger en zoomen met twee
    vingers.</p>',
  ['fig', '23-mobile.webp', 'De editor op een telefoon', 'Dezelfde editor bij 414 pixels breed.'],
]],

['id' => 'save', 'icon' => 'download', 'h' => 'Je werk terughalen', 'body' => [
  '<p><strong>Alles downloaden (.zip)</strong> bovenaan pakt de database en elk taalbestand dat
    je open hebt in één archief — met precies de namen die het spel verwacht, klaar om terug te
    zetten in <code>Extras\\</code> of in de map van je mod. Losse bestanden download je apart
    vanuit het linkerpaneel.</p>',
  '<p>De editor schrijft nooit uit zichzelf in je spel.</p>',
]],

['id' => 'safety', 'icon' => 'shield-check', 'h' => 'Wat hij niet zal doen', 'body' => [
  '<ul>
     <li><strong>Geïndexeerde velden blijven alleen-lezen.</strong> Een <code>ID</code>
       veranderen zonder de indexboom opnieuw te bouwen zou het bestand stilletjes breken — en
       een stille breuk in een database die het spel elke beurt leest is de ergste soort.</li>
     <li><strong>Rijen worden niet toegevoegd of verwijderd</strong> — om dezelfde reden.</li>
     <li><strong>BLOB-kolommen</strong> verschijnen als <code>[BLOB]</code> en blijven met rust.</li>
     <li><strong>De mappen van de server zelf zijn buiten bereik.</strong> Bestanden rechtstreeks
       uit een spelmap openen staat uit, tenzij je de editor thuis draait en het zelf aanzet.</li>
   </ul>',
  '<p class="text-body-secondary mb-0">Al het andere mag. Veel plezier ermee.</p>',
]],

]];
