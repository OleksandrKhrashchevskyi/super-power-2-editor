<?php

return [
'name' => 'Português',
'lead' => 'Tudo o que o editor faz, por ordem e com capturas de ecrã.',

'sections' => [

['id' => 'start', 'icon' => 'rocket-takeoff', 'h' => 'Primeiros passos', 'body' => [
  '<p>O editor abre os ficheiros do próprio jogo. Não instala nada nem converte nada: lê
    <code>DATABASE.GDB</code> — uma base Firebird 1.5 — e os ficheiros de texto
    <code>StringTable.*.gst</code> byte a byte, em PHP puro.</p>',
  '<ol>
     <li>Encontre os ficheiros do jogo. Uma instalação limpa guarda-os em
       <code>SuperPower 2\\Extras\\</code>; um mod guarda a sua própria cópia em
       <code>SuperPower 2\\MODS\\&lt;nome do mod&gt;\\</code>.</li>
     <li>Arraste <code>DATABASE.GDB</code> para a caixa de envio, ou clique nela e escolha
       o ficheiro.</li>
     <li>Passe ao separador <strong>Idiomas</strong> e envie também um
       <code>StringTable.*.gst</code>. É opcional, mas sem ele os países e as cidades são
       apenas números.</li>
   </ol>',
  '<p>Um <code>.zip</code> com vários desses ficheiros de uma vez também serve: a base vai
    para um separador e cada ficheiro de idioma para o outro.</p>',
  ['fig', '01-upload.webp', 'Ecrã de envio',
   'Largue o ficheiro ou clique para o escolher. A barra mostra megabytes e velocidade; a página abre-se sozinha quando o envio termina.'],
  ['note', 'info-circle',
   'Os seus ficheiros são cópias. Os originais do jogo nunca são tocados — no fim é a cópia editada que transfere.'],
]],

['id' => 'project', 'icon' => 'folder2-open', 'h' => 'O seu projeto e a sua ligação', 'body' => [
  '<p>Não há registo. Assim que abre a página recebe um código de projeto curto, e tudo o
    que enviar vive numa pasta só sua. Duas pessoas podem enviar cada uma o seu
    <code>DATABASE.GDB</code> no mesmo minuto sem mexer no trabalho da outra.</p>',
  '<p>O código fica num cookie, por isso normalmente nem pensa nele. Também está na ligação:
    guarde essa ligação e o projeto abre noutro computador, no telemóvel ou depois de
    limpar os cookies. Encontra-a em baixo no painel da esquerda e na página inicial, sob
    <em>O seu projeto</em>.</p>',
  ['fig', '22-project.webp', 'Janela do projeto',
   'A ligação do projeto, o código, quanto espaço ocupa, e um botão que começa um projeto vazio.'],
  '<p>Um projeto é apagado sete dias depois da última visita. <strong>Fechar</strong> retira
    um ficheiro do projeto de propósito — fica sempre uma cópia na pasta
    <code>backups</code> do projeto.</p>',
]],

['id' => 'overview', 'icon' => 'speedometer2', 'h' => 'O que mostra o separador Base de dados', 'body' => [
  '<p>Com uma base de dados aberta, chega a um resumo: quantas tabelas, linhas e colunas há, quais são
    as tabelas grandes, e atalhos para tudo o que vale a pena fazer.</p>',
  ['fig', '02-overview.webp', 'Resumo da base de dados',
   'O SuperPower 2 vem com 28 tabelas e cerca de 62 800 linhas. O emblema verde à esquerda quer dizer que há um ficheiro de idioma anexado, por isso os números aparecem como nomes.'],
]],

['id' => 'table', 'icon' => 'table', 'h' => 'Percorrer uma tabela', 'body' => [
  '<p>Escolha uma tabela à esquerda. Clique num cabeçalho de coluna para ordenar. O botão
    <strong>Filtrar</strong> abre uma fila de caixas debaixo dos cabeçalhos: escreva numa
    delas e só ficam as linhas que correspondem.</p>',
  ['fig', '03-table.webp', 'Tabela COUNTRY',
   'COUNTRY, 194 linhas e 86 colunas. Debaixo de NAME_STID o editor escreve o nome verdadeiro tirado do ficheiro de idioma — a base só guarda 2136.'],
  '<ul>
     <li><span class="badge text-bg-secondary">IDX</span> marca uma coluna usada pelos
       índices. Essas são só de leitura, e a secção seguinte explica porquê.</li>
     <li><span class="badge text-bg-secondary">TXT</span> marca uma coluna que aponta para o
       ficheiro de idioma. O nome por baixo do número vem dali.</li>
     <li>As tabelas grandes são paginadas; o tamanho da página escolhe-se em baixo.</li>
   </ul>',
]],

['id' => 'edit', 'icon' => 'pencil-square', 'h' => 'Editar uma linha', 'body' => [
  '<p>O lápis no início de uma linha abre todos os seus campos numa janela. Há uma caixa de
    procura, porque algumas tabelas passam das duzentas colunas. <strong>Guardar</strong>
    escreve os valores diretamente no <code>.gdb</code>.</p>',
  ['fig', '04-edit-row.webp', 'Editor de linha',
   'Cada campo mostra o seu tipo e, nas colunas de texto, a linha do ficheiro de idioma. NULL tem o seu próprio interruptor, porque vazio e «por preencher» não são a mesma coisa para o jogo.'],
  ['note', 'exclamation-triangle',
   'Os campos sobre os quais os índices são construídos — <code>ID</code> em quase todas as tabelas do SP2 — não podem ser alterados. Um índice é uma árvore de chaves à parte dentro do ficheiro: altere o valor sem reconstruir a árvore e o jogo passa a ler as linhas erradas. Acrescentar e apagar linhas está bloqueado pela mesma razão.'],
]],

['id' => 'struct', 'icon' => 'diagram-3', 'h' => 'Estrutura da tabela', 'body' => [
  '<p><strong>Estrutura</strong> lista as colunas de uma tabela com os seus tipos verdadeiros
    de Firebird, deslocamentos e domínios, e aponta as que parecem ligações a outras tabelas.
    Útil quando anda a descobrir o que uma coluna quer mesmo dizer.</p>',
  ['fig', '05-structure.webp', 'Estrutura de COUNTRY',
   'Os tipos tal como o próprio Firebird os guarda. A coluna Notas assinala as ligações prováveis, a pertença a índices e os ponteiros de texto.'],
  '<p>A mesma lista existe como ficheiro: <em>Esquema JSON</em>, no painel da esquerda,
    entrega-lhe o esquema inteiro para os seus próprios scripts.</p>',
]],

['id' => 'map', 'icon' => 'globe-americas', 'h' => 'O mapa-mundo', 'body' => [
  '<p>A base de dados não traz mapa nenhum. O SuperPower 2 guarda 2 604 regiões e 3 347 cidades, cada
    cidade com latitude e longitude — por isso o editor desenha uma região como o terreno que
    está mais perto das suas próprias cidades do que das de qualquer outro, e depois recorta o
    resultado pelas costas e fronteiras reais.</p>',
  '<p>É por isso que as formas ficam perto mas não exatas, e por isso o mapa o diz com
    franqueza na linha por baixo da imagem. O que acerta em cheio é o que aqui importa:
    <em>que região pertence a quem</em>, tirado diretamente da tabela que está a editar.</p>',
]],

['id' => 'map-who', 'icon' => 'flag', 'h' => 'Quem possui o quê', 'body' => [
  ['fig', '06-map-country.webp', 'Mapa colorido por país',
   'Cada região pintada com a cor do seu dono. A legenda nomeia os maiores donos; os restantes são agrupados em «e mais 180».'],
  '<p>Passe o rato por uma região e a etiqueta nomeia a região, o seu dono e a sua capital.
    Arraste para deslocar, use a roda para ampliar, e a caixa ao lado da lista de camadas
    destaca um único país, para ver o seu território de relance — incluindo os pedaços que
    ficam noutros continentes.</p>',
]],

['id' => 'map-lang', 'icon' => 'translate', 'h' => 'Idioma e religião', 'body' => [
  '<p>A lista <strong>Colorir por</strong>, em cima à esquerda, muda o que as cores querem dizer. O idioma e a
    religião vêm das tabelas <code>LANGUAGES</code> e <code>RELIGIONS</code>, que guardam uma
    proporção por região para cada idioma e cada religião — o mapa pinta o de maior proporção.</p>',
  ['fig', '07-map-language.webp', 'Idioma predominante', 'Idioma predominante por região.'],
  ['fig', '08-map-religion.webp', 'Religião predominante', 'Religião predominante por região.'],
  '<p>Outras duas camadas mostram a <em>proporção</em> de um idioma ou religião à sua escolha
    como gradiente, que é a forma mais rápida de encontrar as regiões de que um mod se
    esqueceu.</p>',
  ['note', 'info-circle',
   'Estas camadas mostram os dados do jogo, não o mundo real — e os dados do jogo têm as suas esquisitices. O idioma predominante do Brasil sai como inglês; o da Ucrânia, como russo. É o que está em <code>DATABASE.GDB</code>, e agora pode vê-lo e corrigi-lo.'],
]],

['id' => 'map-num', 'icon' => 'bar-chart', 'h' => 'Governo e números', 'body' => [
  ['fig', '09-map-government.webp', 'Tipo de governo', 'Tipo de governo por país, de GVT_TYPE.'],
  '<p>As camadas numéricas — população, infraestruturas, nível de telecomunicações — usam um
    único gradiente, com o valor mais pequeno, o do meio e o maior escritos na legenda. A
    escala vai por classificação e não por valor; de outro modo a China e a Índia esmagariam tudo o
    resto num só tom.</p>',
  ['fig', '10-map-population.webp', 'População',
   'População por região. A legenda dá o valor mais pequeno, o do meio e o maior.'],
]],

['id' => 'map-rel', 'icon' => 'arrow-left-right', 'h' => 'Relações e tratados', 'body' => [
  '<p>Escolha <em>Relações de um país</em> e depois um país: todos os outros ficam pintados
    conforme aquilo que sentem por ele, de hostil a aliado passando por neutro, segundo a
    tabela <code>RELATIONS</code>.</p>',
  ['fig', '11-map-relations.webp', 'Relações para com os Estados Unidos',
   'O que o mundo sente para com os Estados Unidos. O país escolhido mantém a sua própria cor.'],
  '<p><em>Membros de um tratado</em> faz o mesmo: escolha um tratado de
    <code>TREATY</code> e os seus membros acendem-se.</p>',
  ['fig', '12-map-treaty.webp', 'Membros da NATO',
   'Os membros de um tratado — aqui a NATO — em face de todos os outros.'],
]],

['id' => 'map-mil', 'icon' => 'shield', 'h' => 'Exércitos, mísseis, força', 'body' => [
  '<p>O menu <strong>Camadas</strong>, à direita, acende dois conjuntos de pontos: as unidades
    de <code>UNITS</code> e os mísseis de <code>MISSILE</code>, cada um desenhado onde o jogo
    diz que está.</p>',
  ['fig', '13-map-layers-menu.webp', 'Menu das camadas',
   'Tropas e mísseis como interruptores separados, por cima do que as cores estiverem a mostrar.'],
  '<p><em>Total de unidades</em> soma o que cada país possui de facto e sombreia os
    países pelo total, para se ver de relance quem são os pesos pesados desta base.</p>',
  ['fig', '14-map-force.webp', 'Total de unidades',
   'Países sombreados pela força que possuem, com as posições das unidades e dos mísseis por cima.'],
  ['note', 'info-circle',
   'O SuperPower 2 não tem tabela com nomes de tipos de unidade, por isso o editor rotula um tipo com um desenho que o usa de facto — uma amostra verdadeira tirada de <code>DESIGN</code>, não um palpite.'],
]],

['id' => 'map-edit', 'icon' => 'cursor', 'h' => 'Editar a partir do mapa', 'body' => [
  '<p>O mapa não é uma imagem — é uma segunda porta para as mesmas linhas.</p>',
  '<ul>
     <li><strong>Clique numa região</strong> e a sua linha de <code>REGION</code> abre-se na
       janela de edição do costume.</li>
     <li><strong>O seletor de dono</strong> dessa janela entrega a região a outro país na hora.</li>
     <li><strong>Shift + arrastar</strong> traça uma caixa sobre várias regiões e transfere-as
       todas para um país de uma só vez. O mapa redesenha-se logo, por isso um erro salta à
       vista — e o registo de alterações anula-o.</li>
   </ul>',
]],

['id' => 'merge', 'icon' => 'diagram-2', 'h' => 'Juntar países', 'body' => [
  '<p>Juntar é a operação pesada: tudo o que pertence a um país — regiões, cidades, unidades,
    mísseis, partidos, tratados — passa para outro, e o país de origem é desativado. É assim
    que se constrói um mod onde a Jugoslávia nunca se desfez, ou onde o mapa tem doze países
    em vez de cento e noventa e quatro.</p>',
  ['fig', '15-merge.webp', 'Juntar países',
   'Indique o país recetor e os países a juntar; o editor lista o que vai mudar de mãos antes de acontecer seja o que for.'],
  '<p>Nada se mexe enquanto não confirmar, e a junção inteira fica no registo de alterações
    como um único passo, que pode anular.</p>',
]],

['id' => 'replace', 'icon' => 'arrow-repeat', 'h' => 'Procurar e substituir', 'body' => [
  '<p>Uma coluna de cada vez, com pré-visualização. Escolha uma tabela e uma coluna, diga o
    que deve corresponder — igual, contém, maior que, vazio — e o que pôr no lugar. A
    <strong>Pré-visualizar</strong> mostra exatamente que linhas mudariam, e quantas.</p>',
  ['fig', '16-replace.webp', 'Procurar e substituir com pré-visualização',
   'A pré-visualização primeiro: as linhas que mudariam, antes e depois, com a contagem.'],
]],

['id' => 'check', 'icon' => 'clipboard-check', 'h' => 'Verificação da base', 'body' => [
  '<p>A verificação procura só estragos verdadeiros; nada é adivinhado:</p>',
  '<ul>
     <li>ligações que apontam para linhas que não existem;</li>
     <li>ponteiros de texto que faltam no ficheiro de idioma anexado;</li>
     <li>países ativos sem uma única região;</li>
     <li>capitais assentes noutro país;</li>
     <li>chaves repetidas e regiões sem uma única cidade.</li>
   </ul>',
  ['fig', '17-check.webp', 'Verificação da base de dados',
   'As ocorrências agrupadas por tipo. Cada linha é uma ligação direta para a linha que é preciso corrigir.'],
  '<p>Vale a pena passá-la depois de uma junção ou de uma substituição grande, e outra vez
    antes de levar o ficheiro de volta para o jogo.</p>',
]],

['id' => 'find', 'icon' => 'binoculars', 'h' => 'Pesquisa global', 'body' => [
  '<p>Uma caixa, todas as tabelas, e ainda o ficheiro de idioma. Escreva um nome e recebe as
    linhas que o mencionam, morem onde morarem.</p>',
  ['fig', '18-find.webp', 'Pesquisa global',
   'À procura de «Poland» em todas as tabelas e no ficheiro de idioma ao mesmo tempo.'],
]],

['id' => 'log', 'icon' => 'clock-history', 'h' => 'Registo e anular', 'body' => [
  '<p>Cada alteração desta sessão — uma linha, uma substituição, uma junção, uma transferência
    no mapa — fica anotada com os valores que substituiu. <strong>Anular</strong> repõe os
    valores anteriores.</p>',
  ['fig', '19-log.webp', 'Registo de alterações',
   'O que mudou, onde, quantas células, e anular ao lado de cada passo.'],
  '<p>Além disso, antes de cada edição é guardada uma cópia do ficheiro na pasta
    <code>backups</code> do projeto. O registo cobre a sessão; as cópias duram o que durar o
    projeto.</p>',
]],

['id' => 'gst', 'icon' => 'translate', 'h' => 'Ficheiros de idioma (.gst)', 'body' => [
  '<p>Um <code>.gst</code> é um índice mais um bloco de texto UTF-16 — é ali que vive mesmo
    cada nome que o jogo lhe mostra. O separador Idiomas lista-os todos com procura, e uma
    linha edita-se ali mesmo.</p>',
  ['fig', '20-languages.webp', 'Ficheiro de idioma',
   'StringTable.english.gst — os números de linha daqui são os números que a base guarda nas suas colunas *_STID.'],
  '<p>Podem estar abertos vários ficheiros de idioma ao mesmo tempo; o que escolher é o que as
    tabelas da base usam ao escrever os nomes por baixo dos números. Mudar o nome de um país
    para todo o jogo é uma única linha aqui — não uma edição na base.</p>',
]],

['id' => 'ui', 'icon' => 'palette', 'h' => 'Aspeto', 'body' => [
  '<p>Dez temas, a sua própria cor de destaque e de texto, dez tipos de letra, um tamanho e um
    interruptor de densidade. Tudo se aplica no navegador e fica por lá, por isso não custa
    nada e acompanha-o por todo o editor.</p>',
  ['fig', '21-appearance.webp', 'Janela do aspeto',
   'Temas, destaque, tipo de letra e densidade, com pré-visualização em tempo real em baixo.'],
]],

['id' => 'mobile', 'icon' => 'phone', 'h' => 'No telemóvel', 'body' => [
  '<p>A disposição dobra-se: a lista de tabelas entra a deslizar do lado, a barra de
    ferramentas passa para várias linhas, e o mapa ocupa toda a largura, com deslocação ao
    toque e ampliação com dois dedos.</p>',
  ['fig', '23-mobile.webp', 'O editor num telemóvel', 'O mesmo editor com 414 pixels de largura.'],
]],

['id' => 'save', 'icon' => 'download', 'h' => 'Levar o seu trabalho de volta', 'body' => [
  '<p><strong>Transferir tudo (.zip)</strong>, em cima, mete a base e todos os ficheiros de
    idioma que tiver abertos num só arquivo — com os nomes que o jogo espera, prontos a voltar
    para <code>Extras\\</code> ou para a pasta do seu mod. Os ficheiros soltos transferem-se
    à parte, a partir do painel da esquerda.</p>',
  '<p>O editor nunca escreve no seu jogo por sua conta.</p>',
]],

['id' => 'safety', 'icon' => 'shield-check', 'h' => 'O que não fará', 'body' => [
  '<ul>
     <li><strong>Os campos indexados continuam só de leitura.</strong> Alterar um
       <code>ID</code> sem reconstruir a árvore do índice partiria o ficheiro em silêncio — e
       uma quebra silenciosa numa base que o jogo lê a cada turno é a pior de todas.</li>
     <li><strong>Não se acrescentam nem se apagam linhas</strong> — pela mesma razão.</li>
     <li><strong>As colunas BLOB</strong> aparecem como <code>[BLOB]</code> e ficam intocadas.</li>
     <li><strong>As pastas do próprio servidor estão fora de alcance.</strong> Abrir ficheiros
       diretamente de uma pasta do jogo está desligado, a não ser que corra o editor em casa e
       seja o próprio a ligá-lo.</li>
   </ul>',
  '<p class="text-body-secondary mb-0">Todo o resto é terreno livre. Divirta-se.</p>',
]],

]];
