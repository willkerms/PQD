<?php
namespace PQD;

/**
 * Parser das anotações de phpDOC das entidades
 *
 * Recebe o caminho do arquivo da classe, a partir da raiz da aplicação, e extrai
 * por expressão regular as anotações @entity, @table, @field, @list e @help.
 * O resultado fica em um cache estático indexado pelo caminho do arquivo, de modo
 * que a mesma entidade é lida do disco apenas uma vez por request.
 *
 * @author Willker Moraes Silva
 * @since 2015-11-13
 *
 */
class PQDAnnotation{

	/**
	 * Cache das anotações já lidas, indexado pelo caminho do arquivo da classe
	 *
	 * Chaves de cada entrada: table, entity, fields, pk, fks, filters,
	 * viewFields, viewFilters, allFields, allFilters.
	 * Protegido e estático de propósito: subclasses podem completar ou alterar
	 * os metadados já resolvidos.
	 *
	 * @var array
	 */
	protected static $annotation = array();

	/**
	 * Caminho do arquivo da classe anotada, com separador normalizado em "/"
	 *
	 * @var string
	 */
	protected $class;

	/**
	 * Se o arquivo não existir, registra a exceção de código 11 e a instância
	 * passa a responder com metadados vazios.
	 *
	 * @param string $classFile Caminho do arquivo da classe a partir da raiz da aplicação, ex.: "modulos/cad/pessoas/Pessoa.php". Barras invertidas são normalizadas, o que permite informar o nome da classe com namespace + ".php".
	 */
	function __construct($classFile) {

		$this->class = str_replace("\\", '/', $classFile);


		if(!is_file($this->class)){
			PQDApp::getApp()->getExceptions()->setException(new \Exception("Classe não encontrada!", 11) );
			self::$annotation[$this->class] = array();
		}
	}
	/**
	 * Retorna valores dentro dos parênteses
	 *
	 * O conteúdo é quebrado por ", " (vírgula + espaço) e cada parte por "=".
	 * Partes no formato "chave=valor" viram índices associativos; partes sem "="
	 * viram índices numéricos, ex.: "@table(products)" retorna array(0 => 'products').
	 *
	 * @param string $nameAttr Nome da anotação, com "@", ex.: "@field"
	 * @param string $str Anotação completa, ex.: "@field(name=price, type=money)"
	 * @return array
	 */
	private function retValues($nameAttr, $str){
		$str = substr($str, strlen($nameAttr)+1, -1);
		$annotations = explode(", ", $str);
		$col = array();

		foreach ($annotations as $description){
			$values = explode("=", $description);
			if(count($values) == 2)
				$col[$values[0]] = $values[1];
			else
				$col[] = $values[0];
		}

		return $col;
	}

	/**
	 * Retorna um objeto com todos os campos anotados no arquivo informado
	 *
	 * Percorre todos os blocos de comentário do arquivo; em cada bloco que contenha um
	 * campo anotado, agrega a ele as anotações de lista e de ajuda do mesmo bloco. Os
	 * campos são indexados pela propriedade "name", ou por índice numérico quando
	 * "name" não é informado.
	 *
	 * A anotação @help é lida até o fim da linha, contando com a quebra de linha no
	 * formato CRLF.
	 *
	 * @author Willker Moraes Silva
	 * @since 2015-12-01
	 * @param string $file Caminho do arquivo da entidade, do DTO ou da View
	 * @return \stdClass pk (string|null), fks (array), filters (array) e fields (array)
	 */
	private function getAllFieldsFile($file){

		$return = new \stdClass();
		$return->pk = null;
		$return->fks = array();
		$return->filters = array();
		$return->fields = array();

		//Todos os comentários dá classe
		preg_match_all('/(\/\*(.|\s)+?(\*\/))/', file_get_contents($file), $matches);

		if(isset($matches[0])){

			$fields = array();
			$pk = null;
			foreach($matches[0] as $key => $comment){
				preg_match('/\@field\(.*\)/', $comment, $field);

				if(isset($field[0])){
					$field = $field[0];

					$col = $this->retValues('@field', $field);

					preg_match('/\@list\([^\r\n]*/m', $comment, $list);
					if(isset($list[0])){
						$col['list'] = PQDUtil::json_decode(substr($list[0], strlen('@list')+1, -1));

						if( is_string($col['list']) && substr($col['list'], -5) == '.json'){

							$jsonFile = dirname($this->class) . '/' . $col['list'];

							if (is_file($jsonFile))
								$col['list'] = PQDUtil::json_decode(file_get_contents($jsonFile));
							else
								PQDApp::getApp()->getExceptions()->setException(new \Exception("Arquivo JSON não encontrado!", 13) );
						}

						if (is_null($col['list']))
							throw new \Exception("Erro no JSON da entidade! Arquivo(" . $this->class . "), coluna: (" . $col['name'] . ").");
					}

					preg_match('/\@help\(.*$/m', $comment, $help);
					if(isset($help[0]))
						$col['help'] = substr($help[0], strlen('@help')+1, -2);

					if(isset($col['name']))
						$fields[$col['name']] = $col;
					else
						$fields[] = $col;

					if(isset($col['isPk']) && $col['isPk'] == 'true')
						$pk = $col['name'];

					if(isset($col['isFilter']) && $col['isFilter'] == 'true')
						$return->filters[] = $col;

					if(isset($col['fk']))
						$return->fks[] = array($col['name'] => $col['fk']);
				}
			}

			$return->fields = $fields;
			$return->pk = $pk;
		}

		return $return;
	}

	/**
	 * retorna nome da coluna chave primaria, a que está marcada com isPk=true
	 *
	 * @return string|null
	 */
	public function getPk(){
		if(isset(self::$annotation[$this->class]['pk']))
			return self::$annotation[$this->class]['pk'];
		else{
			$this->getFields();
			return self::$annotation[$this->class]['pk'];
		}
	}

	/**
	 * retorna os filtros, ou seja, os campos marcados com isFilter=true
	 *
	 * @return array
	 */
	public function getFilters(){
		if(isset(self::$annotation[$this->class]['filters']))
			return self::$annotation[$this->class]['filters'];
		else{
			$this->getFields();
			return self::$annotation[$this->class]['filters'];
		}
	}

	/**
	 * retorna FK's, um array(coluna => fk) para cada campo que declara a propriedade fk
	 *
	 * @return array
	 */
	public function getFks(){
		if(isset(self::$annotation[$this->class]['fks']))
			return self::$annotation[$this->class]['fks'];
		else{
			$this->getFields();
			return self::$annotation[$this->class]['fks'];
		}
	}

	/**
	 * retorna valores setados na anotação @table
	 *
	 * No mesmo passo resolve a anotação @entity e, quando @table declara clsDTO ou
	 * clsView, os campos e filtros dessa classe (viewFields e viewFilters).
	 *
	 * @return array Normalmente array('name' => '<tabela>')
	 */
	public function getTable(){

		if(isset(self::$annotation[$this->class]['table']))
			return self::$annotation[$this->class]['table'];
		else{
			self::$annotation[$this->class]['table'] = array();
			self::$annotation[$this->class]['entity'] = array();
			self::$annotation[$this->class]['viewFields'] = array();
			self::$annotation[$this->class]['viewFilters'] = array();

			preg_match('/\@table\([^)]*\)/', file_get_contents($this->class), $matches);

			if(isset($matches[0])){

				$values = $this->retValues('@table', $matches[0]);
				$table = isset($values[0]) ? array('name' => $values[0]) : $values;

				if (isset($table['clsView']) || isset($table['clsDTO'])) {
					$cls = isset($table['clsView']) ? $table['clsView'] : $table['clsDTO'];
					$fields = $this->getAllFieldsFile(str_replace("\\", '/', $cls) . ".php");

					self::$annotation[$this->class]['viewFields'] = $fields->fields;
					self::$annotation[$this->class]['viewFilters'] = $fields->filters;
				}

				self::$annotation[$this->class]['table'] = $table;
			}

			preg_match('/\@entity\([^)]*\)/', file_get_contents($this->class), $matches);
			if(isset($matches[0]))
				self::$annotation[$this->class]['entity'] = $this->retValues('@entity', $matches[0]);

			return self::$annotation[$this->class]['table'];
		}
	}

	/**
	 * retorna os campos anotados na anotação @field, indexados pelo nome da coluna
	 *
	 * @return array
	 */
	public function getFields(){

		if(isset(self::$annotation[$this->class]['fields']))
			return self::$annotation[$this->class]['fields'];
		else{
			$fields = $this->getAllFieldsFile($this->class);

			self::$annotation[$this->class]['pk'] = $fields->pk;
			self::$annotation[$this->class]['fks'] = $fields->fks;
			self::$annotation[$this->class]['filters'] = $fields->filters;
			self::$annotation[$this->class]['fields'] = $fields->fields;

			$this->getTable();

			return self::$annotation[$this->class]['fields'];
		}
	}

	/**
	 * Retorna os campos da classe DTO ou Vw declarada em @table
	 *
	 * @return array
	 */
	public function getViewFields(){
		if(isset(self::$annotation[$this->class]['viewFields']))
			return self::$annotation[$this->class]['viewFields'];
		else{
			$this->getFields();
			return self::$annotation[$this->class]['viewFields'];
		}
	}

	/**
	 * Retorna os valores setados em @entity
	 *
	 * @return array
	 */
	public function getEntity(){
		if(isset(self::$annotation[$this->class]['entity']))
			return self::$annotation[$this->class]['entity'];
		else{
			$this->getTable();
			return self::$annotation[$this->class]['entity'];
		}
	}

	/**
	 * Retorna os filtros da classe DTO ou Vw, os campos marcados com isFilter=true
	 *
	 * @return array
	 */
	public function getViewFilters(){
		if(isset(self::$annotation[$this->class]['viewFilters']))
			return self::$annotation[$this->class]['viewFilters'];
		else{
			$this->getFields();
			return self::$annotation[$this->class]['viewFilters'];
		}
	}

	/**
	 * Retorna todos os campos inclusive os que estão na classe DTO ou vw
	 *
	 * Em caso de mesmo nome de coluna nas duas classes, o campo do DTO ou da View
	 * prevalece sobre o da entidade.
	 *
	 * @return array
	 */
	public function getAllFields(){
		if(isset(self::$annotation[$this->class]['allFields']))
			return self::$annotation[$this->class]['allFields'];
		else {
			$this->getFields();
			self::$annotation[$this->class]['allFields'] = array_merge(self::$annotation[$this->class]['fields'], self::$annotation[$this->class]['viewFields']);
			return self::$annotation[$this->class]['allFields'];
		}
	}

	/**
	 * Retorna um campo pelo nome, ou null caso não exista
	 *
	 * A busca é feita no conjunto completo, ou seja, nos campos da entidade e nos da
	 * classe DTO ou vw, pela chave, que é a propriedade "name" do campo.
	 *
	 * @param string $field Nome da coluna, ex.: "idProduct"
	 * @return array|null
	 */
	public function getField($field){
		$aFields = $this->getAllFields();
		return isset($aFields[$field]) ? $aFields[$field] : null;
	}

	/**
	 * Retorna todos os campos filtros inclusive os da classe DTO ou vw
	 *
	 * @return array
	 */
	public function getAllFilters(){

		if(isset(self::$annotation[$this->class]['allFilters']))
			return self::$annotation[$this->class]['allFilters'];
		else{
			$this->getFields();
			self::$annotation[$this->class]['allFilters'] = array_merge(self::$annotation[$this->class]['filters'], self::$annotation[$this->class]['viewFilters']);
			return self::$annotation[$this->class]['allFilters'];
		}
	}

	/**
	 * Retorna a entrada completa do cache de anotações da classe
	 *
	 * @return array
	 */
	public function getClassAnnotation(){

		if(isset(self::$annotation[$this->class]))
			return self::$annotation[$this->class];
		else{
			$this->getFields();
			return self::$annotation[$this->class];
		}
	}

	/**
	 * Retorna o caminho do arquivo lido, com separador normalizado em "/"
	 *
	 * @return string
	 */
	public function getClass() {
		return $this->class;
	}
}