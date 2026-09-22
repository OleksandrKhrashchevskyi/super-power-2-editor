<?php

return [
'name' => 'Italiano',
'lead' => 'Tutto quello che fa l’editor, in ordine e con le schermate.',

'sections' => [

['id' => 'start', 'icon' => 'rocket-takeoff', 'h' => 'Per cominciare', 'body' => [
  '<p>L’editor apre i file del gioco stesso. Non installa nulla e non converte nulla: legge
    <code>DATABASE.GDB</code> — un database Firebird 1.5 — e i file di testo
    <code>StringTable.*.gst</code> byte per byte, in puro PHP.</p>',
  '<ol>
     <li>Trova i file del gioco. Un’installazione pulita li tiene in
       <code>SuperPower 2\\Extras\\</code>; una mod tiene la propria copia in
       <code>SuperPower 2\\MODS\\&lt;nome della mod&gt;\\</code>.</li>
     <li>Trascina <code>DATABASE.GDB</code> sul riquadro di caricamento, oppure fai clic e
       scegli il file.</li>
     <li>Passa alla scheda <strong>Lingue</strong> e carica anche uno
       <code>StringTable.*.gst</code>. È facoltativo, ma senza di esso paesi e città sono
       soltanto numeri.</li>
   </ol>',
  '<p>Va bene anche uno <code>.zip</code> con più di quei file insieme: la base finisce in una
    scheda e ogni file di lingua nell’altra.</p>',
  ['fig', '01-upload.webp', 'Schermata di caricamento',
   'Trascina qui il file oppure fai clic per sceglierlo. La barra mostra megabyte e velocità; la pagina si apre da sola quando il caricamento finisce.'],
  ['note', 'info-circle',
   'I tuoi file sono copie. Gli originali del gioco non vengono mai toccati: alla fine scarichi la copia modificata.'],
]],

['id' => 'project', 'icon' => 'folder2-open', 'h' => 'Il tuo progetto e il suo collegamento', 'body' => [
  '<p>Non c’è nessuna registrazione. Appena apri la pagina ricevi un breve codice di progetto,
    e tutto quello che carichi vive in una cartella tutta sua. Due persone possono caricare
    ciascuna il proprio <code>DATABASE.GDB</code> nello stesso minuto senza toccare il lavoro
    dell’altra.</p>',
  '<p>Il codice sta in un cookie, quindi di solito non ci pensi. Sta anche nel collegamento:
    conserva quel collegamento e il progetto si apre su un altro computer, sul telefono o dopo
    aver cancellato i cookie. Lo trovi in basso nel pannello di sinistra e in prima pagina,
    sotto <em>Il tuo progetto</em>.</p>',
  ['fig', '22-project.webp', 'Finestra del progetto',
   'Il collegamento al progetto, il codice, quanto spazio occupa, e un pulsante che ne comincia uno vuoto.'],
  '<p>Un progetto viene eliminato sette giorni dopo l’ultima visita. <strong>Chiudi</strong>
    toglie un file dal progetto apposta: una copia resta comunque nella cartella
    <code>backups</code> del progetto.</p>',
]],

['id' => 'overview', 'icon' => 'speedometer2', 'h' => 'Cosa mostra la scheda Database', 'body' => [
  '<p>Con un database aperto ti ritrovi su un riepilogo: quante tabelle, righe e colonne ci sono,
    quali sono le tabelle grosse, e scorciatoie verso tutto ciò che vale la pena fare.</p>',
  ['fig', '02-overview.webp', 'Riepilogo del database',
   'SuperPower 2 arriva con 28 tabelle e circa 62 800 righe. Il contrassegno verde a sinistra significa che è allegato un file di lingua, quindi i numeri compaiono come nomi.'],
]],

['id' => 'table', 'icon' => 'table', 'h' => 'Scorrere una tabella', 'body' => [
  '<p>Scegli una tabella a sinistra. Fai clic su un’intestazione di colonna per ordinare. Il
    pulsante <strong>Filtra</strong> apre una fila di caselle sotto le intestazioni: scrivi in
    una e restano solo le righe che corrispondono.</p>',
  ['fig', '03-table.webp', 'Tabella COUNTRY',
   'COUNTRY, 194 righe e 86 colonne. Sotto NAME_STID l’editor scrive il nome vero preso dal file di lingua: il database, da parte sua, conserva solo 2136.'],
  '<ul>
     <li><span class="badge text-bg-secondary">IDX</span> segna una colonna che usano gli
       indici. Quelle sono di sola lettura, e la sezione seguente spiega perché.</li>
     <li><span class="badge text-bg-secondary">TXT</span> segna una colonna che punta al file
       di lingua. Il nome sotto il numero viene da lì.</li>
     <li>Le tabelle grandi sono divise in pagine; la dimensione della pagina la scegli tu, in
       basso.</li>
   </ul>',
]],

['id' => 'edit', 'icon' => 'pencil-square', 'h' => 'Modificare una riga', 'body' => [
  '<p>La matita all’inizio di una riga apre tutti i suoi campi in una finestra. C’è una casella
    di ricerca, perché certe tabelle superano le duecento colonne. <strong>Salva</strong>
    scrive i valori direttamente nel <code>.gdb</code>.</p>',
  ['fig', '04-edit-row.webp', 'Editor di riga',
   'Ogni campo mostra il suo tipo e, per le colonne di testo, la riga del file di lingua. NULL ha un interruttore suo, perché vuoto e «non impostato» per il gioco sono due cose diverse.'],
  ['note', 'exclamation-triangle',
   'I campi su cui poggiano gli indici — <code>ID</code> in quasi tutte le tabelle di SP2 — non si possono cambiare. Un indice è un albero di chiavi a parte dentro il file: cambia il valore senza ricostruire l’albero e il gioco comincia a leggere le righe sbagliate. Aggiungere ed eliminare righe è bloccato per lo stesso motivo.'],
]],

['id' => 'struct', 'icon' => 'diagram-3', 'h' => 'Struttura della tabella', 'body' => [
  '<p><strong>Struttura</strong> elenca le colonne di una tabella con i loro tipi Firebird
    veri, gli scostamenti e i domini, e indica quali sembrano collegamenti ad altre tabelle.
    Comodo quando stai capendo cosa voglia dire davvero una colonna.</p>',
  ['fig', '05-structure.webp', 'Struttura di COUNTRY',
   'I tipi così come li conserva Firebird stesso. La colonna Note segnala i collegamenti probabili, l’appartenenza agli indici e i puntatori di testo.'],
  '<p>Lo stesso elenco esiste come file: <em>Schema JSON</em>, nel pannello di sinistra, ti
    consegna l’intero schema per i tuoi script.</p>',
]],

['id' => 'map', 'icon' => 'globe-americas', 'h' => 'La mappa del mondo', 'body' => [
  '<p>Dentro il database non c’è nessuna mappa. SuperPower 2 conserva 2 604 regioni e 3 347 città,
    ciascuna con latitudine e longitudine, quindi l’editor disegna una regione come il terreno
    più vicino alle sue città che a quelle di chiunque altro, e poi rifila il risultato su
    coste e confini veri.</p>',
  '<p>Ecco perché le forme ci vanno vicino ma non sono esatte, ed ecco perché la mappa lo dice
    apertamente nella riga sotto l’immagine. Quello che azzecca in pieno è ciò che qui conta:
    <em>quali regioni appartengono a chi</em>, preso direttamente dalla tabella che stai
    modificando.</p>',
]],

['id' => 'map-who', 'icon' => 'flag', 'h' => 'Chi possiede cosa', 'body' => [
  ['fig', '06-map-country.webp', 'Mappa colorata per paese',
   'Ogni regione dipinta del colore del suo proprietario. La legenda elenca i proprietari più grandi; il resto si ripiega in «e altri 180».'],
  '<p>Passa sopra una regione e il tooltip nomina la regione, il suo proprietario e la sua
    capitale. Trascina per spostare, rotella per ingrandire, e la casella accanto all’elenco
    dei livelli mette in risalto un solo paese: così ne vedi il territorio in un colpo d’occhio,
    compresi i pezzi che stanno su altri continenti.</p>',
]],

['id' => 'map-lang', 'icon' => 'translate', 'h' => 'Lingua e religione', 'body' => [
  '<p>L’elenco <strong>Colora per</strong>, in alto a sinistra, cambia il significato dei colori. Lingua e
    religione vengono dalle tabelle <code>LANGUAGES</code> e <code>RELIGIONS</code>, che
    tengono una quota per regione per ogni lingua e ogni fede: la mappa dipinge quella con la
    quota più grande.</p>',
  ['fig', '07-map-language.webp', 'Lingua prevalente', 'Lingua prevalente per regione.'],
  ['fig', '08-map-religion.webp', 'Religione prevalente', 'Religione prevalente per regione.'],
  '<p>Altri due livelli mostrano la <em>quota</em> di una lingua o di una fede scelta come
    sfumatura: è il modo più rapido per trovare le regioni che una mod ha dimenticato.</p>',
  ['note', 'info-circle',
   'Questi livelli mostrano i dati del gioco, non il mondo reale — e i dati del gioco hanno le loro stranezze. Come lingua prevalente del Brasile esce l’inglese, per l’Ucraina il russo. È quello che sta in <code>DATABASE.GDB</code>, e ora puoi vederlo e sistemarlo.'],
]],

['id' => 'map-num', 'icon' => 'bar-chart', 'h' => 'Governo e numeri', 'body' => [
  ['fig', '09-map-government.webp', 'Tipo di governo', 'Tipo di governo per paese, da GVT_TYPE.'],
  '<p>I livelli numerici — popolazione, infrastrutture, livello delle telecomunicazioni — usano
    una sola sfumatura, con il valore più piccolo, quello di mezzo e il più grande scritti in
    legenda. La scala va per rango e non per valore; altrimenti Cina e India schiaccerebbero
    tutto il resto in una sola tinta.</p>',
  ['fig', '10-map-population.webp', 'Popolazione',
   'Popolazione per regione. La legenda dà il valore più piccolo, quello di mezzo e il più grande.'],
]],

['id' => 'map-rel', 'icon' => 'arrow-left-right', 'h' => 'Relazioni e trattati', 'body' => [
  '<p>Scegli <em>Relazioni di un paese</em> e poi un paese: tutti gli altri vengono dipinti
    secondo come si sentono verso quello, da ostile ad alleato passando per neutrale, secondo
    la tabella <code>RELATIONS</code>.</p>',
  ['fig', '11-map-relations.webp', 'Relazioni verso gli Stati Uniti',
   'Come si sente il mondo verso gli Stati Uniti. Il paese scelto mantiene il proprio colore.'],
  '<p><em>Membri di un trattato</em> fa lo stesso: scegli un trattato da
    <code>TREATY</code> e i suoi membri si accendono.</p>',
  ['fig', '12-map-treaty.webp', 'Membri della NATO',
   'I membri di un trattato — qui la NATO — di fronte a tutti gli altri.'],
]],

['id' => 'map-mil', 'icon' => 'shield', 'h' => 'Eserciti, missili, forza', 'body' => [
  '<p>Il menu <strong>Livelli</strong>, a destra, accende due insiemi di punti: le unità di
    <code>UNITS</code> e i missili di <code>MISSILE</code>, ognuno disegnato dove il gioco dice
    che si trova.</p>',
  ['fig', '13-map-layers-menu.webp', 'Menu dei livelli',
   'Truppe e missili come interruttori separati, sopra quello che stanno mostrando i colori.'],
  '<p><em>Unità totali</em> somma ciò che ogni paese possiede davvero e
    ombreggia i paesi in base al totale: si vede a colpo d’occhio chi sono i pesi massimi di
    questo database.</p>',
  ['fig', '14-map-force.webp', 'Unità totali',
   'Paesi ombreggiati secondo la forza che possiedono, con sopra le posizioni di unità e missili.'],
  ['note', 'info-circle',
   'SuperPower 2 non ha una tabella con i nomi dei tipi di unità, così l’editor etichetta un tipo con un progetto che lo usa davvero: un campione reale preso da <code>DESIGN</code>, non una supposizione.'],
]],

['id' => 'map-edit', 'icon' => 'cursor', 'h' => 'Modificare dalla mappa', 'body' => [
  '<p>La mappa non è un’immagine: è una seconda porta verso le stesse righe.</p>',
  '<ul>
     <li><strong>Fai clic su una regione</strong> e la sua riga di <code>REGION</code> si apre
       nella solita finestra di modifica.</li>
     <li><strong>Il selettore del proprietario</strong> in quella finestra consegna la regione
       a un altro paese all’istante.</li>
     <li><strong>Maiusc + trascinamento</strong> traccia un riquadro su più regioni e le
       trasferisce tutte a un paese in un colpo solo. La mappa si ridisegna subito, quindi un
       errore salta all’occhio — e il registro delle modifiche lo annulla.</li>
   </ul>',
]],

['id' => 'merge', 'icon' => 'diagram-2', 'h' => 'Unisci paesi', 'body' => [
  '<p>L’unione è l’operazione pesante: tutto ciò che appartiene a un paese — regioni, città,
    unità, partiti, trattati — passa a un altro, e il paese di partenza viene
    disattivato. È così che si costruisce una mod in cui la Jugoslavia non si è mai sfasciata,
    o in cui sulla mappa ci sono dodici paesi invece di centonovantaquattro.</p>',
  ['fig', '15-merge.webp', 'Unisci paesi',
   'Indica il paese ricevente e i paesi da unire; l’editor elenca cosa sta per spostarsi prima che accada qualsiasi cosa.'],
  '<p>Niente si sposta finché non confermi, e l’intera unione finisce nel registro delle
    modifiche come un solo passo, che puoi annullare.</p>',
]],

['id' => 'replace', 'icon' => 'arrow-repeat', 'h' => 'Trova e sostituisci', 'body' => [
  '<p>Una colonna alla volta, con anteprima. Scegli una tabella e una colonna, di’ cosa deve
    corrispondere — uguale, contiene, maggiore di, vuoto — e cosa mettere al suo posto.
    L’<strong>Anteprima</strong> mostra esattamente quali righe cambierebbero, e quante.</p>',
  ['fig', '16-replace.webp', 'Trova e sostituisci con anteprima',
   'Prima l’anteprima: le righe che cambierebbero, prima e dopo, con il conteggio.'],
]],

['id' => 'check', 'icon' => 'clipboard-check', 'h' => 'Controllo del database', 'body' => [
  '<p>Il controllo cerca solo guasti veri; niente viene indovinato:</p>',
  '<ul>
     <li>collegamenti che puntano a righe inesistenti;</li>
     <li>puntatori di testo che mancano nel file di lingua collegato;</li>
     <li>paesi attivi senza nemmeno una regione;</li>
     <li>capitali che stanno in un altro paese;</li>
     <li>chiavi doppie e regioni senza una sola città.</li>
   </ul>',
  ['fig', '17-check.webp', 'Controllo del database',
   'I risultati raggruppati per tipo. Ogni riga è un collegamento diretto alla riga da sistemare.'],
  '<p>Vale la pena farlo dopo un’unione o una sostituzione grossa, e ancora una volta prima di
    riportare il file nel gioco.</p>',
]],

['id' => 'find', 'icon' => 'binoculars', 'h' => 'Ricerca globale', 'body' => [
  '<p>Una casella, tutte le tabelle, più il file di lingua. Scrivi un nome e ottieni le righe
    che lo nominano, ovunque si trovino.</p>',
  ['fig', '18-find.webp', 'Ricerca globale',
   'Ricerca di «Poland» in tutte le tabelle e nel file di lingua insieme.'],
]],

['id' => 'log', 'icon' => 'clock-history', 'h' => 'Registro e annullamento', 'body' => [
  '<p>Ogni modifica di questa sessione — una riga, una sostituzione, un’unione, un trasferimento
    sulla mappa — viene annotata con i valori che ha sostituito. <strong>Annulla</strong>
    rimette a posto i valori precedenti.</p>',
  ['fig', '19-log.webp', 'Registro delle modifiche',
   'Cosa è cambiato, dove, quante celle, e l’annullamento accanto a ogni passo.'],
  '<p>In più, prima di ogni singola modifica viene messa una copia del file nella cartella
    <code>backups</code> del progetto. Il registro copre la sessione; le copie restano finché
    resta il progetto.</p>',
]],

['id' => 'gst', 'icon' => 'translate', 'h' => 'File di lingua (.gst)', 'body' => [
  '<p>Un <code>.gst</code> è un indice più un blocco di testo UTF-16: è lì che vive davvero ogni
    nome che il gioco ti mostra. La scheda Lingue li elenca tutti con la ricerca, e una riga si
    modifica sul posto.</p>',
  ['fig', '20-languages.webp', 'File di lingua',
   'StringTable.english.gst — i numeri di riga qui sono i numeri che il database conserva nelle sue colonne *_STID.'],
  '<p>Possono essere aperti più file di lingua insieme; quello che scegli è quello che usano le
    tabelle del database quando scrivono i nomi sotto i numeri. Rinominare un paese per tutto il
    gioco qui è una riga sola, non una modifica nella base.</p>',
]],

['id' => 'ui', 'icon' => 'palette', 'h' => 'Aspetto', 'body' => [
  '<p>Dieci temi, il tuo colore d’accento e di testo, dieci caratteri, una dimensione e un
    interruttore per la densità. Tutto si applica nel browser e lì resta, quindi non costa
    nulla e ti segue per tutto l’editor.</p>',
  ['fig', '21-appearance.webp', 'Finestra dell’aspetto',
   'Temi, accento, carattere e densità, con un’anteprima in tempo reale in basso.'],
]],

['id' => 'mobile', 'icon' => 'phone', 'h' => 'Sul telefono', 'body' => [
  '<p>L’impaginazione si ripiega: l’elenco delle tabelle entra scorrendo di lato, la barra degli
    strumenti va a capo, e la mappa prende tutta la larghezza, con spostamento a dito e zoom a
    due dita.</p>',
  ['fig', '23-mobile.webp', 'L’editor su un telefono', 'Lo stesso editor a 414 pixel di larghezza.'],
]],

['id' => 'save', 'icon' => 'download', 'h' => 'Riprenderti il lavoro', 'body' => [
  '<p><strong>Scarica tutto (.zip)</strong>, in alto, mette la base e tutti i file di lingua che
    hai aperto in un solo archivio — con i nomi che il gioco si aspetta, pronti da rimettere in
    <code>Extras\\</code> o nella cartella della tua mod. I singoli file si scaricano a parte
    dal pannello di sinistra.</p>',
  '<p>L’editor non scrive mai nel tuo gioco di sua iniziativa.</p>',
]],

['id' => 'safety', 'icon' => 'shield-check', 'h' => 'Cosa non farà', 'body' => [
  '<ul>
     <li><strong>I campi indicizzati restano di sola lettura.</strong> Cambiare un
       <code>ID</code> senza ricostruire l’albero dell’indice romperebbe il file in silenzio, e
       un guasto silenzioso in un database che il gioco legge a ogni turno è il peggiore di tutti.</li>
     <li><strong>Le righe non vengono aggiunte né eliminate</strong>, per lo stesso motivo.</li>
     <li><strong>Le colonne BLOB</strong> compaiono come <code>[BLOB]</code> e restano
       intoccate.</li>
     <li><strong>Le cartelle del server stesso sono fuori portata.</strong> Aprire i file
       direttamente da una cartella del gioco è disattivato, a meno che tu non faccia girare
       l’editor a casa tua e lo attivi di persona.</li>
   </ul>',
  '<p class="text-body-secondary mb-0">Tutto il resto è terreno libero. Buon divertimento.</p>',
]],

]];
