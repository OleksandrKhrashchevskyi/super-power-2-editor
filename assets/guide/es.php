<?php

return [
'name' => 'Español',
'lead' => 'Todo lo que hace el editor, en orden y con capturas.',

'sections' => [

['id' => 'start', 'icon' => 'rocket-takeoff', 'h' => 'Primeros pasos', 'body' => [
  '<p>El editor abre los archivos del propio juego. No instala nada ni convierte nada:
    lee <code>DATABASE.GDB</code> —una base Firebird 1.5— y los archivos de texto
    <code>StringTable.*.gst</code> byte a byte, en PHP puro.</p>',
  '<ol>
     <li>Busque los archivos del juego. Una instalación limpia los guarda en
       <code>SuperPower 2\\Extras\\</code>; un mod guarda su propia copia en
       <code>SuperPower 2\\MODS\\&lt;nombre del mod&gt;\\</code>.</li>
     <li>Arrastre <code>DATABASE.GDB</code> al recuadro de subida, o pulse en él y elija el archivo.</li>
     <li>Pase a la pestaña <strong>Idiomas</strong> y suba también un
       <code>StringTable.*.gst</code>. Es opcional, pero sin él los países y las
       ciudades son solo números.</li>
   </ol>',
  '<p>También sirve un <code>.zip</code> con varios de esos archivos a la vez: la base
    va a una pestaña y cada archivo de idioma a la otra.</p>',
  ['fig', '01-upload.webp', 'Pantalla de subida',
   'Suelte el archivo o pulse para elegirlo. La barra muestra megabytes y velocidad; la página se abre sola cuando termina la subida.'],
  ['note', 'info-circle',
   'Sus archivos son copias. Los originales del juego no se tocan nunca: al terminar usted descarga la copia editada.'],
]],

['id' => 'project', 'icon' => 'folder2-open', 'h' => 'Su proyecto y su enlace', 'body' => [
  '<p>No hay registro. En cuanto abre la página recibe un código de proyecto corto, y
    todo lo que suba vive en una carpeta propia. Dos personas pueden subir su propio
    <code>DATABASE.GDB</code> en el mismo minuto sin tocar el trabajo del otro.</p>',
  '<p>El código está en una cookie, así que normalmente no hay que pensar en él.
    También está en el enlace: guarde ese enlace y el proyecto se abre en otro
    ordenador, en un móvil o después de borrar las cookies. Lo encontrará abajo en el
    panel izquierdo y en la portada, bajo <em>Su proyecto</em>.</p>',
  ['fig', '22-project.webp', 'Ventana del proyecto',
   'El enlace del proyecto, el código, cuánto espacio ocupa y un botón para empezar uno vacío.'],
  '<p>Un proyecto se borra siete días después de la última visita. <strong>Cerrar</strong>
    quita un archivo del proyecto a propósito; de todas formas queda una copia en la
    carpeta <code>backups</code> del proyecto.</p>',
]],

['id' => 'overview', 'icon' => 'speedometer2', 'h' => 'Qué muestra la pestaña de la base', 'body' => [
  '<p>Con una base abierta llega a un resumen: cuántas tablas, filas y columnas hay,
    cuáles son las tablas grandes y accesos directos a todo lo que merece la pena hacer.</p>',
  ['fig', '02-overview.webp', 'Resumen de la base',
   'SuperPower 2 viene con 28 tablas y unas 62 800 filas. La etiqueta verde de la izquierda indica que hay un archivo de idioma adjunto, así que los números se muestran como nombres.'],
]],

['id' => 'table', 'icon' => 'table', 'h' => 'Recorrer una tabla', 'body' => [
  '<p>Elija una tabla a la izquierda. Pulse en una cabecera de columna para ordenar. El
    botón <strong>Filtrar</strong> abre una fila de casillas bajo las cabeceras: escriba
    en una y solo quedan las filas que coinciden.</p>',
  ['fig', '03-table.webp', 'Tabla COUNTRY',
   'COUNTRY, 194 filas y 86 columnas. Bajo NAME_STID el editor escribe el nombre real sacado del archivo de idioma; la base solo guarda 2136.'],
  '<ul>
     <li><span class="badge text-bg-secondary">IDX</span> marca una columna que usan los
       índices. Son de solo lectura y la sección siguiente explica por qué.</li>
     <li><span class="badge text-bg-secondary">TXT</span> marca una columna que apunta al
       archivo de idioma. El nombre bajo el número viene de ahí.</li>
     <li>Las tablas grandes se paginan; el tamaño de página lo elige usted abajo.</li>
   </ul>',
]],

['id' => 'edit', 'icon' => 'pencil-square', 'h' => 'Editar una fila', 'body' => [
  '<p>El lápiz al principio de una fila abre todos sus campos en una ventana. Hay una
    casilla de búsqueda, porque algunas tablas pasan de doscientas columnas.
    <strong>Guardar</strong> escribe los valores directamente en el <code>.gdb</code>.</p>',
  ['fig', '04-edit-row.webp', 'Editor de fila',
   'Cada campo muestra su tipo y, en las columnas de texto, la línea del archivo de idioma. NULL tiene su propio interruptor, porque vacío y «sin poner» no son lo mismo para el juego.'],
  ['note', 'exclamation-triangle',
   'Los campos sobre los que se construyen los índices —<code>ID</code> en casi todas las tablas de SP2— no se pueden cambiar. Un índice es un árbol de claves aparte dentro del archivo: cambie el valor sin reconstruir el árbol y el juego empieza a leer las filas equivocadas. Añadir y borrar filas está bloqueado por lo mismo.'],
]],

['id' => 'struct', 'icon' => 'diagram-3', 'h' => 'Estructura de la tabla', 'body' => [
  '<p><strong>Estructura</strong> lista las columnas de una tabla con sus tipos reales de
    Firebird, desplazamientos y dominios, y señala cuáles parecen enlaces a otras tablas.
    Útil cuando está averiguando qué significa de verdad una columna.</p>',
  ['fig', '05-structure.webp', 'Estructura de COUNTRY',
   'Los tipos tal como los guarda el propio Firebird. La columna Notas marca los enlaces probables, la pertenencia a índices y los punteros de texto.'],
  '<p>La misma lista está disponible como archivo: <em>Esquema JSON</em>, en el panel
    izquierdo, le entrega el esquema entero para sus propios scripts.</p>',
]],

['id' => 'map', 'icon' => 'globe-americas', 'h' => 'El mapa del mundo', 'body' => [
  '<p>La base no lleva ningún mapa dentro. SuperPower 2 guarda 2 604 regiones y 3 347
    ciudades, cada ciudad con una latitud y una longitud, así que el editor dibuja una
    región como el terreno que está más cerca de sus propias ciudades que de las de
    nadie más, y luego recorta el resultado contra costas y fronteras reales.</p>',
  '<p>Por eso las formas se acercan pero no son exactas, y por eso el mapa lo dice
    claramente en la línea bajo la imagen. Lo que sí acierta del todo es lo que aquí
    importa: <em>qué región pertenece a quién</em>, directamente de la tabla que usted
    está editando.</p>',
]],

['id' => 'map-who', 'icon' => 'flag', 'h' => 'Quién posee qué', 'body' => [
  ['fig', '06-map-country.webp', 'Mapa coloreado por país',
   'Cada región pintada del color de su dueño. La leyenda lista a los mayores dueños; el resto se pliega en «y 180 más».'],
  '<p>Pase el ratón por una región y el cuadro emergente nombra la región, su dueño y su capital.
    Arrastre para desplazar, use la rueda para acercar, y la casilla junto a la lista de
    capas resalta un solo país para ver su territorio de un vistazo, incluidos los trozos
    que quedan en otros continentes.</p>',
]],

['id' => 'map-lang', 'icon' => 'translate', 'h' => 'Idioma y religión', 'body' => [
  '<p>La lista <strong>Colorear por</strong>, arriba a la izquierda, cambia lo que significan los colores. El
    idioma y la religión salen de las tablas <code>LANGUAGES</code> y
    <code>RELIGIONS</code>, que guardan una proporción por región para cada lengua y cada
    credo: el mapa pinta la de mayor proporción.</p>',
  ['fig', '07-map-language.webp', 'Idioma dominante', 'Idioma dominante por región.'],
  ['fig', '08-map-religion.webp', 'Religión dominante', 'Religión dominante por región.'],
  '<p>Otras dos capas muestran la <em>proporción</em> de una lengua o un credo concretos
    como degradado, que es la forma más rápida de encontrar las regiones que un mod se
    dejó olvidadas.</p>',
  ['note', 'info-circle',
   'Estas capas muestran los datos del juego, no el mundo real, y los datos del juego tienen sus rarezas. El idioma dominante de Brasil sale como inglés; el de Ucrania, como ruso. Eso es lo que hay en <code>DATABASE.GDB</code>, y ahora usted puede verlo y arreglarlo.'],
]],

['id' => 'map-num', 'icon' => 'bar-chart', 'h' => 'Gobierno y números', 'body' => [
  ['fig', '09-map-government.webp', 'Tipo de gobierno', 'Tipo de gobierno por país, de GVT_TYPE.'],
  '<p>Las capas numéricas —población, infraestructura, nivel de telecomunicaciones— usan
    un único degradado con el valor menor, el del medio y el mayor escritos en la leyenda.
    La escala va por rango y no por valor; si no, China y la India aplastarían todo lo
    demás en un solo tono.</p>',
  ['fig', '10-map-population.webp', 'Población',
   'Población por región. La leyenda da el valor menor, el del medio y el mayor.'],
]],

['id' => 'map-rel', 'icon' => 'arrow-left-right', 'h' => 'Relaciones y tratados', 'body' => [
  '<p>Elija <em>Relaciones de un país</em> y escoja un país: todos los demás se pintan
    según cómo se sienten hacia él, de hostil a aliado pasando por neutral, según la
    tabla <code>RELATIONS</code>.</p>',
  ['fig', '11-map-relations.webp', 'Relaciones hacia los Estados Unidos',
   'Cómo se siente el mundo hacia los Estados Unidos. El país elegido conserva su propio color.'],
  '<p><em>Miembros de un tratado</em> hace lo mismo: elija un tratado de
    <code>TREATY</code> y se encienden sus miembros.</p>',
  ['fig', '12-map-treaty.webp', 'Miembros de la OTAN',
   'Los miembros de un tratado —aquí la OTAN— frente a todos los demás.'],
]],

['id' => 'map-mil', 'icon' => 'shield', 'h' => 'Ejércitos, misiles, fuerza', 'body' => [
  '<p>El menú <strong>Capas</strong>, a la derecha, enciende dos conjuntos de puntos: las
    unidades de <code>UNITS</code> y los misiles de <code>MISSILE</code>, cada uno dibujado
    donde el juego dice que está.</p>',
  ['fig', '13-map-layers-menu.webp', 'Menú de capas',
   'Tropas y misiles como interruptores aparte, encima de lo que muestren los colores.'],
  '<p><em>Unidades totales</em> suma lo que cada país posee de verdad y sombrea
    los países por el total, así se ve de un vistazo quiénes son los pesos pesados de esta
    base.</p>',
  ['fig', '14-map-force.webp', 'Unidades totales',
   'Países sombreados por la fuerza que poseen, con las posiciones de unidades y misiles encima.'],
  ['note', 'info-circle',
   'SuperPower 2 no tiene tabla de nombres de tipos de unidad, así que el editor etiqueta un tipo con un diseño que lo usa de verdad: una muestra real sacada de <code>DESIGN</code>, no una suposición.'],
]],

['id' => 'map-edit', 'icon' => 'cursor', 'h' => 'Editar desde el mapa', 'body' => [
  '<p>El mapa no es una imagen: es una segunda entrada a las mismas filas.</p>',
  '<ul>
     <li><strong>Pulse en una región</strong> y su fila de <code>REGION</code> se abre en
       la ventana de edición de siempre.</li>
     <li><strong>El selector de dueño</strong> de esa ventana entrega la región a otro país
       en el acto.</li>
     <li><strong>Mayús + arrastrar</strong> dibuja un recuadro sobre varias regiones y las
       transfiere todas a un país de una vez. El mapa se redibuja al instante, así que un
       error se ve enseguida, y el registro de cambios lo deshace.</li>
   </ul>',
]],

['id' => 'merge', 'icon' => 'diagram-2', 'h' => 'Fusionar países', 'body' => [
  '<p>Fusionar es la operación gorda: todo lo que pertenece a un país —regiones, ciudades,
    unidades, partidos, tratados— pasa a otro, y el país de origen se desactiva.
    La matriz de relaciones queda intacta.
    Así se construye un mod donde Yugoslavia nunca se rompió, o donde hay doce países en el
    mapa en lugar de ciento noventa y cuatro.</p>',
  ['fig', '15-merge.webp', 'Fusionar países',
   'Indique el país receptor y los países que se fusionan; el editor lista lo que va a moverse antes de que ocurra nada.'],
  '<p>Nada se mueve hasta que usted confirma, y la fusión entera queda en el registro de
    cambios como un solo paso que puede deshacer.</p>',
]],

['id' => 'replace', 'icon' => 'arrow-repeat', 'h' => 'Buscar y reemplazar', 'body' => [
  '<p>Una columna cada vez, con vista previa. Elija una tabla y una columna, diga qué debe
    coincidir —igual, contiene, mayor que, vacío— y qué poner en su lugar. La
    <strong>Vista previa</strong> muestra exactamente qué filas cambiarían y cuántas.</p>',
  ['fig', '16-replace.webp', 'Buscar y reemplazar con vista previa',
   'Primero la vista previa: las filas que cambiarían, antes y después, con el recuento.'],
]],

['id' => 'check', 'icon' => 'clipboard-check', 'h' => 'Comprobar la base', 'body' => [
  '<p>La comprobación busca solo roturas reales; nada se adivina:</p>',
  '<ul>
     <li>enlaces que apuntan a filas que no existen;</li>
     <li>punteros de texto que faltan en el archivo de idioma adjunto;</li>
     <li>países activos sin ninguna región;</li>
     <li>capitales situadas en otro país;</li>
     <li>claves duplicadas y regiones sin una sola ciudad.</li>
   </ul>',
  ['fig', '17-check.webp', 'Comprobación de la base',
   'Los hallazgos agrupados por tipo. Cada línea es un enlace directo a la fila que hay que arreglar.'],
  '<p>Merece la pena pasarla después de una fusión o de un reemplazo grande, y otra vez antes
    de llevarse el archivo de vuelta al juego.</p>',
]],

['id' => 'find', 'icon' => 'binoculars', 'h' => 'Búsqueda global', 'body' => [
  '<p>Una casilla, todas las tablas y además el archivo de idioma. Escriba un nombre y
    obtiene las filas que lo mencionan, vivan donde vivan.</p>',
  ['fig', '18-find.webp', 'Búsqueda global',
   'Buscando «Poland» en todas las tablas y en el archivo de idioma a la vez.'],
]],

['id' => 'log', 'icon' => 'clock-history', 'h' => 'Registro de cambios y deshacer', 'body' => [
  '<p>Cada cambio de esta sesión —una fila, un reemplazo, una fusión, una transferencia en el
    mapa— queda anotado con los valores que sustituyó. <strong>Deshacer</strong> devuelve los
    valores anteriores.</p>',
  ['fig', '19-log.webp', 'Registro de cambios',
   'Qué cambió, dónde, cuántas celdas, y deshacer junto a cada paso.'],
  '<p>Además, antes de cada edición se guarda una copia del archivo en la carpeta
    <code>backups</code> del proyecto. El registro cubre la sesión; las copias duran lo que
    dure el proyecto.</p>',
]],

['id' => 'gst', 'icon' => 'translate', 'h' => 'Archivos de idioma (.gst)', 'body' => [
  '<p>Un <code>.gst</code> es un índice más un bloque de texto UTF-16: ahí vive de verdad
    cada nombre que el juego le enseña. La pestaña Idiomas los lista todos con búsqueda, y
    una línea se edita allí mismo.</p>',
  ['fig', '20-languages.webp', 'Archivo de idioma',
   'StringTable.english.gst — los números de línea de aquí son los números que la base guarda en sus columnas *_STID.'],
  '<p>Pueden estar abiertos varios archivos de idioma a la vez; el que elija es el que usan
    las tablas de la base al escribir los nombres bajo los números. Cambiar el nombre de un
    país para todo el juego es una sola línea aquí, no una edición en la base.</p>',
]],

['id' => 'ui', 'icon' => 'palette', 'h' => 'Apariencia', 'body' => [
  '<p>Diez temas, su propio color de acento y de texto, diez tipografías, un tamaño y un
    interruptor de densidad. Todo se aplica en el navegador y ahí se queda, así que no cuesta
    nada y le sigue por todo el editor.</p>',
  ['fig', '21-appearance.webp', 'Ventana de apariencia',
   'Temas, acento, tipografía y densidad, con una vista previa en vivo abajo.'],
]],

['id' => 'mobile', 'icon' => 'phone', 'h' => 'En el móvil', 'body' => [
  '<p>La disposición se pliega: la lista de tablas entra deslizándose desde el lado, la barra
    de herramientas se reparte en varias líneas y el mapa ocupa todo el ancho, con
    desplazamiento táctil y zoom de dos dedos.</p>',
  ['fig', '23-mobile.webp', 'El editor en un móvil', 'El mismo editor a 414 píxeles de ancho.'],
]],

['id' => 'save', 'icon' => 'download', 'h' => 'Recuperar su trabajo', 'body' => [
  '<p><strong>Descargar todo (.zip)</strong>, arriba, empaqueta la base y todos los archivos
    de idioma que tenga abiertos en un solo archivo comprimido, con los mismos nombres que
    espera el juego, listos para devolver a <code>Extras\\</code> o a la carpeta de su mod.
    Los archivos sueltos se pueden descargar por separado desde el panel izquierdo.</p>',
  '<p>El editor nunca escribe en su juego por su cuenta.</p>',
]],

['id' => 'safety', 'icon' => 'shield-check', 'h' => 'Lo que no hará', 'body' => [
  '<ul>
     <li><strong>Los campos indexados siguen siendo de solo lectura.</strong> Cambiar un
       <code>ID</code> sin reconstruir el árbol del índice rompería el archivo en silencio, y
       una rotura silenciosa en un formato de partida guardada es la peor de todas.</li>
     <li><strong>No se añaden ni se borran filas</strong>, por lo mismo.</li>
     <li><strong>Las columnas BLOB</strong> se muestran como <code>[BLOB]</code> y no se tocan.</li>
     <li><strong>Las carpetas del propio servidor quedan fuera de alcance.</strong> Abrir
       archivos directamente desde una carpeta del juego está desactivado salvo que usted
       ejecute el editor en casa y lo active a mano.</li>
   </ul>',
  '<p class="text-body-secondary mb-0">Todo lo demás es terreno libre. Que lo disfrute.</p>',
]],

]];
