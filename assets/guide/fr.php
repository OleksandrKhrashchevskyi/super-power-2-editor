<?php

return [
'name' => 'Français',
'lead' => 'Tout ce que fait l’éditeur, dans l’ordre, captures à l’appui.',

'sections' => [

['id' => 'start', 'icon' => 'rocket-takeoff', 'h' => 'Pour commencer', 'body' => [
  '<p>L’éditeur ouvre les fichiers du jeu lui-même. Il n’installe rien et ne convertit
    rien : il lit <code>DATABASE.GDB</code> — une base Firebird 1.5 — et les fichiers
    de texte <code>StringTable.*.gst</code> octet par octet, en PHP pur.</p>',
  '<ol>
     <li>Retrouvez les fichiers du jeu. Une installation propre les garde dans
       <code>SuperPower 2\\Extras\\</code> ; un mod garde sa propre copie dans
       <code>SuperPower 2\\MODS\\&lt;nom du mod&gt;\\</code>.</li>
     <li>Faites glisser <code>DATABASE.GDB</code> sur la zone de téléversement, ou cliquez dessus
       pour choisir le fichier.</li>
     <li>Passez à l’onglet <strong>Langues</strong> et téléversez aussi un
       <code>StringTable.*.gst</code>. Il est facultatif, mais sans lui les pays et les
       villes ne sont que des nombres.</li>
   </ol>',
  '<p>Un <code>.zip</code> contenant plusieurs de ces fichiers d’un coup fonctionne
    également : la base part dans un onglet et chaque fichier de langue dans l’autre.</p>',
  ['fig', '01-upload.webp', 'Écran de téléversement',
   'Déposez le fichier ou cliquez pour le choisir. La barre affiche les mégaoctets et la vitesse ; la page s’ouvre d’elle-même à la fin du téléversement.'],
  ['note', 'info-circle',
   'Vos fichiers sont des copies. Les originaux du jeu ne sont jamais touchés : à la fin, c’est la copie modifiée que vous téléchargez.'],
]],

['id' => 'project', 'icon' => 'folder2-open', 'h' => 'Votre projet et son lien', 'body' => [
  '<p>Il n’y a pas d’inscription. Dès que vous ouvrez la page, vous recevez un court code
    de projet, et tout ce que vous envoyez vit dans un dossier qui lui est propre. Deux
    personnes peuvent envoyer chacune son <code>DATABASE.GDB</code> à la même minute sans
    toucher au travail de l’autre.</p>',
  '<p>Le code est dans un cookie, donc en temps normal vous n’y pensez pas. Il est aussi
    dans le lien : gardez ce lien et le projet s’ouvre sur un autre ordinateur, sur un
    téléphone, ou après un effacement des cookies. Vous le trouverez en bas du panneau de
    gauche et en page d’accueil, sous <em>Votre projet</em>.</p>',
  ['fig', '22-project.webp', 'Fenêtre du projet',
   'Le lien du projet, le code, la place qu’il occupe, et un bouton pour en commencer un vide.'],
  '<p>Un projet est supprimé sept jours après sa dernière visite. <strong>Fermer</strong>
    retire un fichier du projet volontairement ; une copie reste de toute façon dans le
    dossier <code>backups</code> du projet.</p>',
]],

['id' => 'overview', 'icon' => 'speedometer2', 'h' => 'Ce que montre l’onglet base', 'body' => [
  '<p>Une base ouverte, vous arrivez sur un récapitulatif : combien de tables, de lignes et
    de colonnes, quelles tables sont les grosses, et des raccourcis vers tout ce qui vaut la
    peine d’être fait.</p>',
  ['fig', '02-overview.webp', 'Vue d’ensemble de la base',
   'SuperPower 2 est livré avec 28 tables et environ 62 800 lignes. La pastille verte à gauche signale qu’un fichier de langue est joint : les nombres s’affichent donc sous forme de noms.'],
]],

['id' => 'table', 'icon' => 'table', 'h' => 'Parcourir une table', 'body' => [
  '<p>Choisissez une table à gauche. Cliquez sur un en-tête de colonne pour trier. Le bouton
    <strong>Filtrer</strong> ouvre une rangée de cases sous les en-têtes : tapez dans l’une
    d’elles et seules les lignes correspondantes restent.</p>',
  ['fig', '03-table.webp', 'Table COUNTRY',
   'COUNTRY, 194 lignes et 86 colonnes. Sous NAME_STID, l’éditeur écrit le vrai nom tiré du fichier de langue — la base, elle, ne garde que 2136.'],
  '<ul>
     <li><span class="badge text-bg-secondary">IDX</span> marque une colonne dont se servent
       les index. Elles sont en lecture seule, et la section suivante explique pourquoi.</li>
     <li><span class="badge text-bg-secondary">TXT</span> marque une colonne qui pointe vers
       le fichier de langue. Le nom sous le nombre vient de là.</li>
     <li>Les grandes tables sont paginées ; la taille de page, c’est vous qui la choisissez
       en bas.</li>
   </ul>',
]],

['id' => 'edit', 'icon' => 'pencil-square', 'h' => 'Modifier une ligne', 'body' => [
  '<p>Le crayon en début de ligne ouvre tous les champs de cette ligne dans une seule
    fenêtre. Il y a une case de recherche, car certaines tables dépassent les deux cents
    colonnes. <strong>Enregistrer</strong> écrit les valeurs directement dans le
    <code>.gdb</code>.</p>',
  ['fig', '04-edit-row.webp', 'Éditeur de ligne',
   'Chaque champ indique son type et, pour les colonnes de texte, la ligne du fichier de langue. NULL a son propre interrupteur : pour le jeu, « vide » et « non renseigné » ne sont pas la même chose.'],
  ['note', 'exclamation-triangle',
   'Les champs sur lesquels reposent les index — <code>ID</code> dans presque toutes les tables de SP2 — ne peuvent pas être modifiés. Un index est un arbre de clés à part, à l’intérieur du fichier : changez la valeur sans reconstruire l’arbre et le jeu se met à lire les mauvaises lignes. L’ajout et la suppression de lignes sont bloqués pour la même raison.'],
]],

['id' => 'struct', 'icon' => 'diagram-3', 'h' => 'Structure de la table', 'body' => [
  '<p><strong>Structure</strong> liste les colonnes d’une table avec leurs vrais types
    Firebird, leurs décalages et leurs domaines, et signale celles qui ressemblent à des
    liens vers d’autres tables. Pratique quand vous cherchez ce qu’une colonne veut vraiment
    dire.</p>',
  ['fig', '05-structure.webp', 'Structure de COUNTRY',
   'Les types tels que Firebird les stocke. La colonne Remarques signale les liens probables, l’appartenance aux index et les pointeurs de texte.'],
  '<p>La même liste existe sous forme de fichier : <em>Schéma JSON</em>, dans le panneau de
    gauche, vous remet le schéma entier pour vos propres scripts.</p>',
]],

['id' => 'map', 'icon' => 'globe-americas', 'h' => 'La carte du monde', 'body' => [
  '<p>La base ne contient aucune carte. SuperPower 2 garde 2 604 régions et 3 347 villes,
    chacune avec une latitude et une longitude : l’éditeur dessine donc une région comme le
    terrain plus proche de ses propres villes que de celles de n’importe qui d’autre, puis
    rogne le résultat sur les vraies côtes et les vraies frontières.</p>',
  '<p>D’où des formes proches mais pas exactes — et la carte le dit franchement dans la ligne
    sous l’image. Ce qu’elle donne exactement juste, c’est ce qui compte ici : <em>quelle
    région appartient à qui</em>, directement tiré de la table que vous modifiez.</p>',
]],

['id' => 'map-who', 'icon' => 'flag', 'h' => 'À qui appartient quoi', 'body' => [
  ['fig', '06-map-country.webp', 'Carte colorée par pays',
   'Chaque région peinte à la couleur de son propriétaire. La légende liste les plus gros propriétaires ; le reste se replie en « et 180 de plus ».'],
  '<p>Survolez une région et l’infobulle nomme la région, son propriétaire et sa capitale.
    Glissez pour déplacer, molette pour zoomer, et la case à côté de la liste des couches met
    un seul pays en évidence : son territoire se lit d’un coup d’œil, y compris les morceaux
    posés sur d’autres continents.</p>',
]],

['id' => 'map-lang', 'icon' => 'translate', 'h' => 'Langue et religion', 'body' => [
  '<p>La liste <strong>Colorer par</strong>, en haut à gauche, change le sens des couleurs. La langue et la
    religion viennent des tables <code>LANGUAGES</code> et <code>RELIGIONS</code>, qui
    gardent une part par région pour chaque langue et chaque confession : la carte peint
    celle dont la part est la plus grande.</p>',
  ['fig', '07-map-language.webp', 'Langue dominante', 'Langue dominante par région.'],
  ['fig', '08-map-religion.webp', 'Religion dominante', 'Religion dominante par région.'],
  '<p>Deux autres couches montrent la <em>part</em> d’une langue ou d’une confession choisie
    sous forme de dégradé : c’est le moyen le plus rapide de trouver les régions qu’un mod a
    oubliées.</p>',
  ['note', 'info-circle',
   'Ces couches montrent les données du jeu, pas le monde réel — et les données du jeu ont leurs bizarreries. La langue dominante du Brésil y est l’anglais, celle de l’Ukraine le russe. C’est ce qu’il y a dans <code>DATABASE.GDB</code>, et vous pouvez désormais le voir et le corriger.'],
]],

['id' => 'map-num', 'icon' => 'bar-chart', 'h' => 'Gouvernement et chiffres', 'body' => [
  ['fig', '09-map-government.webp', 'Type de gouvernement', 'Type de gouvernement par pays, d’après GVT_TYPE.'],
  '<p>Les couches chiffrées — population, infrastructures, niveau des télécommunications —
    utilisent un seul dégradé, avec la plus petite valeur, celle du milieu et la plus grande
    inscrites dans la légende. L’échelle suit le rang et non la valeur ; sinon la Chine et
    l’Inde écraseraient tout le reste dans une seule teinte.</p>',
  ['fig', '10-map-population.webp', 'Population',
   'Population par région. La légende donne la plus petite valeur, celle du milieu et la plus grande.'],
]],

['id' => 'map-rel', 'icon' => 'arrow-left-right', 'h' => 'Relations et traités', 'body' => [
  '<p>Choisissez <em>Relations d’un pays</em> puis un pays : tous les autres sont peints selon
    ce qu’ils éprouvent à son égard, de l’hostilité à l’alliance en passant par la neutralité,
    d’après la table <code>RELATIONS</code>.</p>',
  ['fig', '11-map-relations.webp', 'Relations envers les États-Unis',
   'Ce que le monde éprouve envers les États-Unis. Le pays choisi garde sa propre couleur.'],
  '<p><em>Membres d’un traité</em> fait pareil : choisissez un traité dans
    <code>TREATY</code> et ses membres s’allument.</p>',
  ['fig', '12-map-treaty.webp', 'Membres de l’OTAN',
   'Les membres d’un traité — ici l’OTAN — face à tous les autres.'],
]],

['id' => 'map-mil', 'icon' => 'shield', 'h' => 'Armées, missiles, puissance', 'body' => [
  '<p>Le menu <strong>Couches</strong>, à droite, allume deux séries de points : les unités de
    <code>UNITS</code> et les missiles de <code>MISSILE</code>, chacun dessiné là où le jeu dit
    qu’il se trouve.</p>',
  ['fig', '13-map-layers-menu.webp', 'Menu des couches',
   'Troupes et missiles en interrupteurs séparés, par-dessus ce que montrent les couleurs.'],
  '<p><em>Unités au total</em> additionne ce que chaque pays possède réellement
    et assombrit les pays selon le total : on voit d’un coup d’œil qui sont les poids lourds de
    cette base.</p>',
  ['fig', '14-map-force.webp', 'Unités au total',
   'Pays assombris selon la puissance des forces qu’ils possèdent, avec par-dessus les positions des unités et des missiles.'],
  ['note', 'info-circle',
   'SuperPower 2 n’a pas de table de noms de types d’unités : l’éditeur étiquette donc un type avec un modèle qui s’en sert réellement — un vrai échantillon tiré de <code>DESIGN</code>, pas une supposition.'],
]],

['id' => 'map-edit', 'icon' => 'cursor', 'h' => 'Modifier depuis la carte', 'body' => [
  '<p>La carte n’est pas une image : c’est une deuxième porte vers les mêmes lignes.</p>',
  '<ul>
     <li><strong>Cliquez sur une région</strong> et sa ligne de <code>REGION</code> s’ouvre
       dans la fenêtre de modification habituelle.</li>
     <li><strong>Le sélecteur de propriétaire</strong> de cette fenêtre donne la région à un
       autre pays sur-le-champ.</li>
     <li><strong>Maj + glisser</strong> trace un cadre sur plusieurs régions et les transfère
       toutes à un même pays en une fois. La carte se redessine aussitôt : une erreur saute aux
       yeux, et le journal des modifications la défait.</li>
   </ul>',
]],

['id' => 'merge', 'icon' => 'diagram-2', 'h' => 'Fusionner des pays', 'body' => [
  '<p>La fusion, c’est la grosse opération : tout ce qui appartient à un pays — régions, villes,
    unités, partis, traités — passe à un autre, et le pays d’origine est désactivé.
    La matrice des relations reste intacte.
    C’est ainsi qu’on bâtit un mod où la Yougoslavie n’a jamais éclaté, ou bien où la carte
    compte douze pays au lieu de cent quatre-vingt-quatorze.</p>',
  ['fig', '15-merge.webp', 'Fusionner des pays',
   'Indiquez le pays cible et les pays à fusionner ; l’éditeur liste ce qui va bouger avant que rien ne se passe.'],
  '<p>Rien ne bouge tant que vous n’avez pas confirmé, et la fusion entière atterrit dans le
    journal des modifications comme une seule étape, que vous pouvez défaire.</p>',
]],

['id' => 'replace', 'icon' => 'arrow-repeat', 'h' => 'Rechercher et remplacer', 'body' => [
  '<p>Une colonne à la fois, avec un aperçu. Choisissez une table et une colonne, dites ce qui
    doit correspondre — égal, contient, supérieur à, vide — et ce qu’il faut mettre à la place.
    L’<strong>aperçu</strong> montre exactement quelles lignes changeraient, et combien.</p>',
  ['fig', '16-replace.webp', 'Rechercher et remplacer avec aperçu',
   'L’aperçu d’abord : les lignes qui changeraient, avant et après, avec le compte.'],
]],

['id' => 'check', 'icon' => 'clipboard-check', 'h' => 'Vérifier la base', 'body' => [
  '<p>La vérification ne cherche que de vraies anomalies ; rien n’est deviné :</p>',
  '<ul>
     <li>des liens qui pointent vers des lignes inexistantes ;</li>
     <li>des pointeurs de texte absents du fichier de langue joint ;</li>
     <li>des pays actifs sans la moindre région ;</li>
     <li>des capitales posées dans un autre pays ;</li>
     <li>des clés en double, et des régions sans une seule ville.</li>
   </ul>',
  ['fig', '17-check.webp', 'Vérification de la base',
   'Les anomalies regroupées par nature. Chaque ligne est un lien direct vers la ligne à corriger.'],
  '<p>Elle vaut le coup après une fusion ou un gros remplacement, et encore une fois avant de
    ramener le fichier dans le jeu.</p>',
]],

['id' => 'find', 'icon' => 'binoculars', 'h' => 'Recherche globale', 'body' => [
  '<p>Une case, toutes les tables, et le fichier de langue en plus. Tapez un nom et vous obtenez
    les lignes qui le mentionnent, où qu’elles vivent.</p>',
  ['fig', '18-find.webp', 'Recherche globale',
   'Recherche de « Poland » dans toutes les tables et dans le fichier de langue à la fois.'],
]],

['id' => 'log', 'icon' => 'clock-history', 'h' => 'Journal et Défaire', 'body' => [
  '<p>Chaque modification de cette session — une ligne, un remplacement, une fusion, un transfert
    sur la carte — est notée avec les valeurs qu’elle a remplacées. <strong>Défaire</strong>
    remet les valeurs précédentes.</p>',
  ['fig', '19-log.webp', 'Journal des modifications',
   'Ce qui a changé, où, combien de cellules, et « Défaire » à côté de chaque étape.'],
  '<p>En plus, une copie du fichier est déposée dans le dossier <code>backups</code> du projet
    avant chaque modification. Le journal couvre la session ; les copies, elles, durent aussi
    longtemps que le projet.</p>',
]],

['id' => 'gst', 'icon' => 'translate', 'h' => 'Fichiers de langue (.gst)', 'body' => [
  '<p>Un <code>.gst</code>, c’est un index plus un bloc de texte UTF-16 : c’est là que vit
    réellement chaque nom que le jeu vous montre. L’onglet Langues les liste tous avec une
    recherche, et une ligne se modifie sur place.</p>',
  ['fig', '20-languages.webp', 'Fichier de langue',
   'StringTable.english.gst — les numéros de ligne d’ici sont les nombres que la base garde dans ses colonnes *_STID.'],
  '<p>Plusieurs fichiers de langue peuvent être ouverts en même temps ; celui que vous choisissez
    est celui qu’emploient les tables de la base pour écrire les noms sous les nombres. Renommer
    un pays pour tout le jeu tient en une ligne ici, pas en une modification dans la base.</p>',
]],

['id' => 'ui', 'icon' => 'palette', 'h' => 'Apparence', 'body' => [
  '<p>Dix thèmes, votre propre couleur d’accent et de texte, dix polices, une taille et un
    interrupteur de densité. Tout s’applique dans le navigateur et y reste : cela ne coûte rien et
    vous suit dans tout l’éditeur.</p>',
  ['fig', '21-appearance.webp', 'Fenêtre d’apparence',
   'Thèmes, accent, police et densité, avec un aperçu en direct en bas.'],
]],

['id' => 'mobile', 'icon' => 'phone', 'h' => 'Sur un téléphone', 'body' => [
  '<p>La mise en page se replie : la liste des tables arrive en glissant depuis le côté, la barre
    d’outils passe à la ligne, et la carte prend toute la largeur, avec déplacement au doigt et
    zoom à deux doigts.</p>',
  ['fig', '23-mobile.webp', 'L’éditeur sur un téléphone', 'Le même éditeur en 414 pixels de large.'],
]],

['id' => 'save', 'icon' => 'download', 'h' => 'Récupérer votre travail', 'body' => [
  '<p><strong>Tout télécharger (.zip)</strong>, en haut, met la base et tous les fichiers de langue
    ouverts dans une seule archive — les noms attendus par le jeu, prêts à être remis dans
    <code>Extras\\</code> ou dans le dossier de votre mod. Les fichiers isolés se téléchargent
    séparément depuis le panneau de gauche.</p>',
  '<p>L’éditeur n’écrit jamais dans votre jeu de lui-même.</p>',
]],

['id' => 'safety', 'icon' => 'shield-check', 'h' => 'Ce qu’il ne fera pas', 'body' => [
  '<ul>
     <li><strong>Les champs indexés restent en lecture seule.</strong> Changer un <code>ID</code>
       sans reconstruire l’arbre de l’index casserait le fichier en silence — et une corruption
       silencieuse dans une base que le jeu lit à chaque tour est la pire de toutes.</li>
     <li><strong>Aucune ligne n’est ajoutée ni supprimée</strong>, pour la même raison.</li>
     <li><strong>Les colonnes BLOB</strong> s’affichent en <code>[BLOB]</code> et ne sont
       pas touchées.</li>
     <li><strong>Les dossiers du serveur lui-même sont hors de portée.</strong> Ouvrir des fichiers
       directement depuis un dossier du jeu est désactivé, sauf si vous faites tourner l’éditeur
       chez vous et l’activez vous-même.</li>
   </ul>',
  '<p class="text-body-secondary mb-0">Tout le reste est à vous. Amusez-vous bien.</p>',
]],

]];
