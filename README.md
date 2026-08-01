# PQD — PHP Quick Development

Framework PHP minimalista para desenvolvimento rápido de aplicações web, focado em:

- **Roteamento por convenção** (URL → Controller, sem tabela de rotas).
- **ORM próprio** sobre PDO, com builder de SQL legível.
- **Multi-ambiente / multi-host** nativo: a mesma instalação atende várias aplicações.
- **MVC enxuto:** Entity, BO, DAO, Controller, View — cada um com uma única responsabilidade.

> Repositório: <https://github.com/willkerms/pqd>
> Licença: GPL-2.0-or-later
> Autor: Willker Moraes Silva

---

## Instalação

```bash
composer require willkerms/pqd:dev-master
```

Requer PHP 8.0+.

---

## Bootstrap

PQD não tem `bin/console` ou esqueleto: você cria um único arquivo de bootstrap (ex.: `req_default.php`) e o `public/index.php` apenas faz `require_once`.

```php
<?php
require_once __DIR__ . '/vendor/autoload.php';

use PQD\PQDApp;

// Cria o app. O segundo argumento lista os ambientes válidos.
$oApp = PQDApp::run($_SERVER['REQUEST_URI'], ['admin', '']);

// Mapeia os ambientes para pastas dentro de modulos/
// '' = modulos/ raiz, 'admin' = modulos/admin/, etc.
$oApp->setHostsEnv([
    'admin.exemplo.com' => 'admin',
    'app.exemplo.com'   => '',
]);

// Conexão de banco (suporta mssql, mysql, pgsql via PDO)
$oApp->setDbConnection('mysql', 'localhost', 'meu_banco', 'usuario', 'senha');

// Templates de header/footer aplicados em todas as views
$oApp->setTemplate('templates/tpl.head.php', 'templates/tpl.footer.php');

// Caminhos que não exigem sessão autenticada
$oApp->addFreePaths('/login', '/api/public');

$oApp->exec();
```

`PQDApp::run()` é estático e retorna a instância única do aplicativo — não pode ser instanciada manualmente.

---

## Roteamento por convenção

Não há tabela de rotas. A URL é mapeada diretamente para a estrutura de pastas:

| URL | Mapeamento |
|-----|-----------|
| `/catalog/products` | `modulos/catalog/products/ProductsCtrl::view()` |
| `/catalog/products?act=form&id=5` | `modulos/catalog/products/ProductsCtrl::form()` |
| `/catalog/products?act=salvar` | `modulos/catalog/products/ProductsCtrl::salvar()` |

Regras:

- Cada segmento de URL após o ambiente vira uma pasta (kebab-case → camelCase).
- O último segmento é o nome do **controller**, em singular para a pasta e plural para o arquivo `<Entidades>Ctrl.php`.
- O parâmetro `?act=` define o método chamado. Sem `act`, o método padrão é `view()`.

---

## Camadas

PQD impõe uma separação rígida entre cinco arquivos por caso de uso:

```
modulos/<grupo>/<caso>/
├── <Entity>.php          # Entity   (PQDEntity)        — mapeia tabela
├── <Entity>DTO.php       # DTO       (extends Entity)  — adiciona campos de JOIN
├── <Entities>BO.php      # Business  (PQDBo)           — regras de negócio
├── <Entities>DAO.php     # Data      (PQDDAO)          — SQL / persistência
├── <Entities>Ctrl.php    # Controller (PQDController) — HTTP
└── view/
    ├── <Entities>Grid.php
    ├── <Entity>View.php
    └── <Entities>PDF.php
```

### Entity (`PQDEntity`)

Representa uma tabela. Atributos camelCase mapeiam colunas. Anotações no phpDOC alimentam o ORM e as views:

```php
/**
 * @entity(isEscaped=true)
 * @table(name=products, clsDTO=modulos\catalog\products\ProductDTO)
 */
class Product extends PQDEntity {

    /**
     * @field(name=idProduct, description=ID, type=int, isNotNull=true)
     */
    protected $idProduct;

    /**
     * @field(name=price, description=Price, type=money, precision=2, required=true, isFilter=true)
     */
    protected $price;

    /**
     * @field(name=name, description=Name, type=string, required=true)
     */
    protected $name;

    // getters/setters camelCase; setters retornam $this para encadeamento
}
```

Métodos úteis herdados:

- `toArray()` — serializa em array.
- `toJSON()` — serializa em JSON.
- `escapeHTML()` — HTML-escape em todos os atributos string (in-place).

Propriedades suportadas **dentro** de `@field(...)` (lista resumida):

| Propriedade | Função |
|-------------|--------|
| `name` | Nome real da coluna. Em DTOs pode incluir alias de tabela: `c.name` |
| `alias` | Alias de retorno em DTOs: `name=c.name, alias=category` ⇒ `c.name AS category` |
| `description` | Label do campo (use entidades HTML para acentos) |
| `type` | `int`, `string`, `money`, `float`, `date`, `datetime`, `boolean` |
| `precision` | Casas decimais para `money` / `float` |
| `isNotNull` | Coluna `NOT NULL` no banco |
| `required` | Obrigatório na view, adiciona `*` no label |
| `requiredCaption` | Caption da mensagem de validação quando o campo é virtual (ex.: FK) |
| `virtual` | Campo não persistido (só leitura ou calculado) — não vai ao banco |
| `isFilter` | Aparece no modal de filtros avançados |
| `filterOrder` | Ordem do campo na lista de filtros |

#### Anotações complementares: `@help` e `@list`

`@help` e `@list` são **anotações independentes** — cada uma em sua própria linha do phpDOC, logo abaixo do `@field`. Não são propriedades de `@field(...)`.

```php
/**
 * @field(name=status, description=Status, type=int, isNotNull=true, isFilter=true)
 * @list(["Inactive", "Active", "Archived"])
 * @help(Current status of the record)
 * @var int
 */
protected $status;
```

**`@help(texto)`** — texto livre (uma linha) exibido como tooltip ao lado do label. Caracteres acentuados podem ser escritos como entidades HTML. Apenas **uma** anotação `@help` por campo é lida pelo parser.

**`@list(...)`** — declara a lista de valores de um campo enum/select. Duas formas suportadas pelo PQD:

| Forma | Comportamento |
|-------|---------------|
| **JSON inline** | O conteúdo entre parênteses é decodificado diretamente como JSON. Pode ser um array indexado (`@list(["Inactive", "Active", "Archived"])`), um objeto chave→valor, ou qualquer estrutura aceita por `json_decode`. |
| **Arquivo `.json`** | Se o conteúdo for uma string terminando em `.json` (ex.: `@list("status.json")`), PQD carrega o arquivo a partir do **diretório da classe da entidade** e usa seu conteúdo como lista. Lança exceção se o arquivo não existir. |

Aplicações podem estender o parser (subclasse de `PQDAnnotation`) para resolver `@list` a partir de outras fontes — por exemplo, sobrescrever `getFields()` e, quando o valor decodificado de `list` ainda for uma string (não array), buscá-la em banco de dados, API ou cache.

### DTO

Estende a Entity e adiciona campos vindos de `JOIN`. As anotações seguem o mesmo formato, mas com aliases:

```php
/**
 * @field(name=c.name, alias=category, description=Category, type=string)
 */
protected $category;
```

Use `virtual=true` para que a validação client-side ignore o campo e valide pela FK correspondente.

### DAO (`PQDDAO` → `PQD\SQL\SQLSelect`)

Responsável por SQL. Métodos principais herdados:

- `save(PQDEntity &$entity)` — INSERT ou UPDATE, retorna `bool`.
- `delete(PQDEntity $entity)` — DELETE, retorna `bool`.
- `genericSearch(SQLWhere $where, ?array $fields=null, $fetchClass=true, ?SQLOrderBy $orderBy=null, $limit=null, $page=0, ?SQLGroupBy $groupBy=null)` — SELECT genérico.
- `retEntity($id, $fetchClass=true, ?array $fields=null)` — busca um único registro pela PK.
- `retNumReg(SQLWhere $where)` — `COUNT(*)` com os mesmos filtros.

Joins ficam declarados no construtor do DAO via `PQD\SQL\SQLJoin`:

```php
public function __construct(...) {
    parent::__construct(...);

    $oJoin = new SQLJoin('p'); // alias da tabela principal
    $oJoin->setLeftJoin('categories c', 'c.idCategory = p.idCategory');
    $this->setDefaultWhereOnSelect($oJoin);
}
```

### BO (`PQDBo` implementa `IPQDBo`)

Camada de regras de negócio. Recebe `\stdClass $oSession` (para validação de permissões) e `PQDExceptions $oExceptions` no construtor. Convenção de métodos por caso de uso:

| Método | Função |
|--------|--------|
| `new<Entity>()` | Instancia entidade com defaults |
| `formatView($entity)` | Converte campos do formato banco para o formato de exibição |
| `formatDB($entity)` | Converte campos do formato view para o formato banco |
| `isValid($entity)` | Validações de regra de negócio antes de persistir |
| `save(&$entity)` | Chama `formatDB`, `isValid` e `getDAO()->save()` |
| `delete(&$entity)` | Soft ou hard delete via DAO |
| `search($q, $type, ...)` | Lista paginada padrão usada pelo controller |

`PQDBo` declara `getDAO()` como abstrato — cada BO concreto retorna sua instância de DAO.

### Controller (`PQDController` implementa `IPQDController`)

Recebe os superglobais já encapsulados:

- `$this->get` — clone de `$_GET`.
- `$this->post` — clone de `$_POST`.
- `$this->session` — `\stdClass` da sessão.
- `$this->exceptions` — instância de `PQDExceptions`.

Métodos comuns:

```php
public function view()    { /* lista (Grid)         */ }
public function novo()    { /* form vazio           */ }
public function form()    { /* form preenchido      */ }
public function salvar()  { /* persiste o $_POST    */ }
public function excluir() { /* deleta               */ }
public function search()  { /* JSON da busca        */ }
public function xls()     { /* exportação Excel     */ }
```

`setView($path, $autoRender=true, $requireHeaderAndFooter=true)` instancia a view; `getView()` devolve a instância para atribuir variáveis (`$this->getView()->oProduct = ...`).

### View (`PQDView`)

A view é apenas um arquivo PHP. Variáveis atribuídas via `$controller->getView()->foo = $bar` viram `$this->foo` dentro do arquivo. `render()` injeta os templates de header/footer registrados em `PQDApp::setTemplate()`.

Helpers úteis:

- `$this->e($field, $escape=null, $charset='UTF-8')` — devolve a `description` do campo a partir dos metadados (`$this->fields`).
- Acesso dinâmico via `__get` / `__set` permite injetar qualquer propriedade.

---

## Anotações (`PQDAnnotation`)

`PQDAnnotation` é o parser que transforma o phpDOC das entidades nos metadados que a aplicação injeta no DAO (colunas, PK, tipos) e nas views (labels, filtros, listas, tooltips). É instanciado com o **caminho do arquivo** da classe, a partir da raiz da aplicação:

```php
$oAnnotation = new PQDAnnotation('modulos/catalog/products/Product.php');

$oAnnotation->getTable();      // array('name' => 'products')
$oAnnotation->getPk();         // 'idProduct'
$oAnnotation->getAllFields();  // campos da Entity + campos do DTO
```

As barras invertidas são normalizadas para `/`, então passar o nome da classe com namespace + `.php` também funciona. Se o arquivo não existir, a exceção de código `11` é registrada em `PQDApp::getApp()->getExceptions()` e a instância passa a responder com metadados vazios.

### Anotações reconhecidas

| Anotação | Onde | Função |
|----------|------|--------|
| `@table(...)` | phpDOC da classe | Nome da tabela e classe complementar (`clsDTO` / `clsView`) |
| `@entity(...)` | phpDOC da classe | Flags da entidade, ex.: `isEscaped=true` |
| `@field(...)` | phpDOC do atributo | Metadados da coluna — ver [Entity](#entity-pqdentity) |
| `@list(...)` | phpDOC do atributo | Lista de valores de campos enum/select |
| `@help(...)` | phpDOC do atributo | Tooltip do campo |

`@table` aceita a forma posicional (`@table(products)` ⇒ `array('name' => 'products')`) ou nomeada:

| Propriedade | Função |
|-------------|--------|
| `name` | Nome da tabela |
| `clsDTO` | Classe DTO cujos `@field` são carregados como `viewFields` / `viewFilters` |
| `clsView` | Alternativa a `clsDTO`, para entidades mapeadas sobre uma view do banco |

`clsDTO` / `clsView` recebem o nome da classe com namespace: PQD troca `\` por `/`, acrescenta `.php` e lê o arquivo — ou seja, o namespace precisa espelhar a estrutura de pastas a partir da raiz da aplicação.

Duas propriedades de `@field` existem exclusivamente para o parser:

| Propriedade | Função |
|-------------|--------|
| `isPk` | `isPk=true` marca a coluna devolvida por `getPk()` |
| `fk` | Tabela/classe referenciada; alimenta `getFks()` |

### Regras de sintaxe do parser

- Uma anotação **por linha** do phpDOC.
- `@field` é lido até o **último `)` da linha** — não escreva nada depois do fecha-parênteses.
- Os pares dentro dos parênteses são separados por **vírgula + espaço** (`, `). `name=x,description=y`, sem o espaço, não é quebrado corretamente.
- `@list` e `@help` são anotações independentes: pertencem ao campo cujo bloco de comentário as contém, não a `@field`.
- `@help` é lido até o fim da linha e conta com quebra de linha **CRLF** — o padrão dos arquivos de entidade.
- Cada campo é indexado pela propriedade `name`; campos sem `name` recebem índice numérico.
- `@list` apontando para um `.json` inexistente registra a exceção de código `13`; JSON malformado lança exceção com o arquivo e a coluna na mensagem.

### API pública

| Método | Retorno |
|--------|---------|
| `getTable()` | Valores de `@table` |
| `getEntity()` | Valores de `@entity` |
| `getFields()` | `@field` da própria classe, indexados pelo nome da coluna |
| `getField($field)` | Um campo pelo nome, considerando entidade + DTO/View, ou `null` |
| `getPk()` | Nome da coluna com `isPk=true`, ou `null` |
| `getFks()` | Lista de `array(coluna => fk)` |
| `getFilters()` | Campos com `isFilter=true` |
| `getViewFields()` / `getViewFilters()` | Idem, mas da classe `clsDTO` / `clsView` |
| `getAllFields()` / `getAllFilters()` | União da entidade com o DTO/View |
| `getClassAnnotation()` | Mapa completo de metadados da classe |
| `getClass()` | Caminho normalizado do arquivo lido |

Qualquer um pode ser o primeiro a ser chamado: o parser resolve as dependências sozinho (`getPk()` dispara `getFields()`, que dispara `getTable()`). Em `getAllFields()`, campo de mesmo nome declarado no DTO/View prevalece sobre o da entidade.

O resultado fica em um cache **estático** indexado pelo caminho do arquivo — a mesma entidade é lida do disco uma única vez por request, mesmo com várias instâncias de `PQDAnnotation`.

### Estendendo o parser

O cache é `protected static`, o que permite a uma subclasse enriquecer metadados já resolvidos:

```php
class AppAnnotation extends PQDAnnotation {

    public function getFields(){
        $aFields = parent::getFields();
        // ex.: resolver @list que ficou como string, traduzir descriptions,
        // ajustar required conforme configuração da instalação...
        return $aFields;
    }
}
```

Esse é o gancho usado para resolver `@list` a partir de banco de dados, API ou cache — ver [`@list`](#anotações-complementares-help-e-list).

---

## Convenções de nomenclatura de variáveis

PQD adota um esquema enxuto de prefixos por tipo nas variáveis locais, inspirado em uma versão leve de Hungarian notation. O objetivo é deixar o tipo da variável visível no nome, sem depender da declaração ou do retorno de uma função para inferi-lo.

| Tipo | Prefixo | Exemplos |
|------|---------|----------|
| Objeto / instância | `$o<Nome>` | `$oCliente`, `$oWhere`, `$oOrderBy` |
| Array | `$a<Nome>` | `$aFields`, `$aDados`, `$aClientes` |
| Boolean | `$is<X>` ou `$has<X>` (inglês preferido) | `$isCodigo`, `$hasNome`, `$isAtivo` |
| Data (`date`) | `$dta<Nome>` | `$dtaInicio`, `$dtaFim`, `$dtaCadastro` |
| Timestamp / `datetime` | `$time<Nome>` | `$timeInicio`, `$timeUltimaAlt` |
| String SQL | `$sql` (com sufixo opcional) | `$sql`, `$sqlInsert`, `$sqlContagem` |
| Primitivos (int, string, float) | camelCase plain | `$idCliente`, `$titulo`, `$status`, `$valor` |

**Boolean é predicado.** Sempre começa com `is` ou `has` em inglês — o padrão recomendado é o inglês porque a expressão lê naturalmente como pergunta (`if ($isCodigo)` → "se é código"). Versões em português (`$ehCodigo`, `$temNome`) são aceitas, mas não recomendadas.

**SQL pode ter sufixo** quando há mais de uma query no mesmo escopo (`$sqlInsert`, `$sqlUpdate`, `$sqlContagem`), mas o nome **sempre começa com `$sql`** — esse é o sinal de que a variável carrega SQL cru.

**Primitivos não levam prefixo de tipo.** O nome do campo já carrega a semântica (`$idCliente` é claramente um ID inteiro; `$titulo` é claramente uma string). Prefixar `$i` ou `$s` polui o nome sem ganho.

**Atributos de classe seguem a mesma regra.** Ex.: `$this->oWhere`, `$this->aFields`, `$this->isCodigo`.

---

## ORM — Builders de SQL

### `SQLWhere`

```php
$oWhere = new SQLWhere();
$oWhere->setEqual('idCategory', 5, SQLWhere::NUMBER)
       ->setAnd()
       ->setBetween('createdAt', ['2024-01-01', '2024-12-31'], SQLWhere::BETWEEN)
       ->setAnd()
       ->setLike('name', 'Pro%', SQLWhere::LIKE)
       ->setAnd()
       ->setParentheses()
           ->setIn('status', [1, 2, 3], SQLWhere::IN)
           ->setOr()
           ->setIsNull('archivedAt')
       ->setParentheses(true);

$oDAO->genericSearch($oWhere);
```

Operadores: `setEqual`, `setDiff` (`<>`), `setMore` (`>`), `setMoreEqual` (`>=`), `setLess` (`<`), `setLessEqual` (`<=`), `setLike`, `setBetween`, `setIn`, `setIsNull`, `setSQL` (raw fragment). Conectores: `setAnd()`, `setOr()`, `setParentheses($close=false)`.

Constantes de tipo: `SQLWhere::STRING` (envolve com aspas e escapa), `::NUMBER`, `::DATE`, `::DATETIME`, `::IN`, `::BETWEEN`, `::IS`.

### `SQLOrderBy`

```php
$oOrderBy = new SQLOrderBy(['price', 'createdAt'], false); // false = DESC
$oOrderBy->addField('name');
$oDAO->genericSearch($oWhere, null, true, $oOrderBy);
```

### `SQLJoin extends SQLWhere`

```php
$oJoin = new SQLJoin('p');
$oJoin->setLeftJoin('categories c', 'c.idCategory = p.idCategory');
$oJoin->setInnerJoin('brands b', 'b.idBrand = p.idBrand');
```

O construtor recebe o **alias da tabela principal** — não passe o nome da tabela.

### `SQLGroupBy`

Análogo a `SQLOrderBy`; passado como sétimo argumento de `genericSearch`.

---

## Conexões e PDO

`PQDApp::setDbConnection($driver, $host, $database, $user, $password)` retorna o **índice** da conexão. Aplicações que falam com múltiplos bancos devem guardar esses índices em constantes:

```php
define('DB_DEFAULT', $oApp->setDbConnection('mysql', 'host1', 'db1', 'u', 'p'));
define('DB_LOGS',    $oApp->setDbConnection('mysql', 'host2', 'db2', 'u', 'p'));
```

`PQDDb::getConnection($index = 0)` devolve um `PQDPDO` (extensão de `\PDO`). `PQDPDO` reescreve `query()` e `exec()` para registrar erros automaticamente no objeto de exceções da aplicação.

---

## Exceções

`PQDApp` cria uma instância única de `PQDExceptions` e a injeta em todos os Controllers, BOs e DAOs. Há três classes hierárquicas:

| Classe | Quando usar |
|--------|-------------|
| `PQDExceptions` | Exceção de usuário final (mensagem segura para exibir) |
| `PQDExceptionsDev` | Exceção interna que sinaliza erro de programação |
| `PQDExceptionsDB` | Erros oriundos do banco; já capturados por `PQDPDO::query()` e `exec()` |

Padrão no controller:

```php
try {
    if ($this->getBO()->save($oEntity)) {
        $this->getView()->setMessage('Salvo!', 'success');
    }
}
catch (PQDExceptionsDev $e) {
    $this->exceptions->setException($e);
}
```

---

## Multi-ambiente e free paths

O método `PQDApp::setHostsEnv([$host => $envFolder, ...])` permite que a mesma instalação atenda múltiplos hostnames, cada um direcionado a uma pasta diferente dentro de `modulos/`. Ambientes diferentes podem ter:

- Assets públicos próprios (ex.: `public/<env>/`).
- Templates, views e controllers próprios.
- Conjunto de telas e permissões próprios.

`PQDApp::addFreePaths('/login', '/recuperar-senha', ...)` lista URLs que **não** exigem sessão autenticada.

---

## Classes acessórias

| Classe | Função |
|--------|--------|
| `PQDAnnotation` | Parser de phpDOC das entidades (`@table`, `@entity`, `@field`, `@list`, `@help`) — ver [Anotações](#anotações-pqdannotation) |
| `PQDCrypt` | Encriptação simétrica para tokens e cookies |
| `PQDLogs` | Logger de acessos e SQL com rotação por arquivo |
| `PQDDynamicView` / `PQDDynamicReport` | Views/relatórios renderizados a partir de definição declarativa |
| `PQDWorkflow` | Máquina de estados para entidades com fluxo de aprovação |
| `DB\DB`, `DB\DBTable`, `DB\DBColumn` | Introspecção de schema (gerar migrações, comparar bancos) |
| `DBF\Dbf`, `DbfToSQL`, `DbfToSQLite` | Importação de arquivos `.dbf` legados |
| `LDAP\LDAPCon` | Cliente LDAP para autenticação corporativa |
| `REPORT\Report` | Engine de relatórios paramétricos exportáveis em HTML/PDF/XLS |

---

## Estrutura de pastas sugerida para a aplicação

```
projeto/
├── composer.json
├── req_default.php          # bootstrap (require_once vendor/autoload + PQDApp::run)
├── index.php                # redireciona para public/
├── public/
│   ├── index.php            # require_once '../req_default.php'
│   └── .htaccess            # rewrite all → index.php
├── templates/
│   ├── tpl.head.php
│   └── tpl.footer.php
├── modulos/
│   ├── util/                # base classes da aplicação que estendem PQD*
│   └── <grupo>/<caso>/
├── scripts/                 # SQL inicial e migrações
├── logs/
├── uploads/
└── vendor/
```

---

## Versão atual

`1.4` — ver [`composer.json`](composer.json).
