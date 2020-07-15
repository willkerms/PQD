<?php

namespace PQD\VIEW;

use PQD\PQDExceptions;

class Field extends FieldAttr{

	public static $tab = 0;
	
	/**
	 * 
	 * @var array[Field]
	 */
	private $childs = array();
	
	/**
	 * @var array[FieldEvent]
	 */
	private $events = array();

	/**
	 * 
	 * @var array[FieldList]
	 */
	private $lists = array();
	
	/**
	 *
	 * @var View
	 */
	protected $pqdViewChild;
	
	/**
	 *
	 * @var View
	 */
	private $parent;
	/**
	 *
	 * @var Field
	 */
	private $nodeParent;
	
	/**
	 * @return Field $nodeParent
	 */
	public function getNodeParent() {
		return $this->nodeParent;
	}
	/**
	 * @return View $parent
	 */
	public function getParent() {
		return $this->parent;
	}
	
	/**
	 * @return View $pqdViewChild
	 */
	public function getPqdViewChild() {
		return $this->pqdViewChild;
	}
	
	/**
	 * @return the $events
	 */
	public function getEvents() {
		return $this->events;
	}

	/**
	 * @return the $lists
	 */
	public function getLists() {
		return $this->lists;
	}
	
	/**
	 * @return the $childs
	 */
	public function getChilds() {
		return $this->childs;
	}
	
	public function __construct(View $parent, Field $nodeParent = null) {
		$this->parent = $parent;
		$this->nodeParent = $nodeParent;
	}
	
	/**
	 * @param \PDO $connection
	 */
	private function setEvents(\PDO $connection) {
		$this->events = array();
		
		$sql = "SELECT * FROM pqd_viewFieldsEvents WHERE idPqdViewField = :idPqdViewField";
		$sth = $connection->prepare($sql);
		$sth->bindValue(":idPqdViewField", $this->getIdPqdViewField(), \PDO::PARAM_INT);
		
		if ($sth->execute())
			$this->events = $sth->fetchAll(\PDO::FETCH_CLASS, 'PQD\VIEW\FieldEvent');
	}

	/**
	 * @param  \PDO $connection
	 */
	private function setLists(\PDO $connection) {
		
		$this->lists = array();

		$sql = "SELECT * FROM pqd_viewFieldsList WHERE idPqdViewField = :idPqdViewField order by ordem, idPqdViewField";
		$sth = $connection->prepare($sql);
		$sth->bindValue(":idPqdViewField", $this->getIdPqdViewField(), \PDO::PARAM_INT);
		
		if ($sth->execute())
			$this->lists = $sth->fetchAll(\PDO::FETCH_CLASS, 'PQD\VIEW\FieldList');
	}
	
	
	/**
	 * @param  \PDO $connection
	 */
	private function setChilds(\PDO $connection) {
		
		$this->childs = array();

		//MySQL
		$sqlMysql = "SELECT * FROM pqd_viewFields WHERE idPqdViewFieldParent = :idPqdViewField order by IFNULL(ordem, 1000), idPqdViewField";
		
		//SQL Server
		$sqlServer = "SELECT * FROM pqd_viewFields WHERE idPqdViewFieldParent = :idPqdViewField order by ISNULL(ordem, 1000), idPqdViewField";
		
		if($connection->getAttribute(\PDO::ATTR_DRIVER_NAME) == "mysql")
			$sth = $connection->prepare($sqlMysql);
		else
			$sth = $connection->prepare($sqlServer);
		
		$sth->bindValue(":idPqdViewField", $this->getIdPqdViewField(), \PDO::PARAM_INT);
		
		$sth->setFetchMode( \PDO::FETCH_CLASS, 'PQD\VIEW\Field', array($this->getParent(), $this));
		if ($sth->execute()){
			while (($field = $sth->fetch(\PDO::FETCH_CLASS)) !== false) {
				//FIXME: não funciona no php 5.5 no Linux, verificar pq, foi adicionado o código abaixo.
				//$field->load($connection);
				$this->childs[] = $field;
			}
			foreach ($this->childs as $field)
				$field->load($connection);
		}
	}

	public function load(\PDO $connection){
		
		if($this->getIdPqdViewChild() > 0){
			$this->pqdViewChild = new View($this->getIdPqdViewChild(), $this->getParent()->getAObjs(), $this, $connection);
			$this->pqdViewChild->setPreview($this->getParent()->getPreview());
		}
		
		$this->setEvents($connection);
		$this->setLists($connection);
		$this->setChilds($connection);
	}
	
	public function setId(){
		if($this->getIdFiled() == null && ($this->getParent()->getPreview() || $this->getMask() != null || $this->getDatepicker() == 1 || $this->getTpField() == "guias" || $this->getTpField() == "abas" || count($this->getEvents()) > 0 ))
			$this->setIdFiled($this->getTpField() . '_' . $this->getIdPqdView() . '_' . $this->getIdPqdViewField());
		
		//FIXME: namespace, deve ser quantos caracteres quiser e deve-se propagar a todas as view's filhas
		if($this->getCampoTabela() != null && $this->getParent()->getNamespace() != substr($this->getIdFiled(), 0, 3))
			$this->setIdFiled($this->getParent()->getNamespace().substr($this->getIdFiled(), 2));
	}
	
	private function retAttrsCols(){
		$attr = "";
		
		if($this->getBorder() != null)
			$attr .= ' border="' . $this->getBorder() . '"';
		
		if($this->getValign() != null)
			$attr .= ' valign="' . $this->getValign() . '"';
		
		if($this->getAlign() != null)
			$attr .= ' align="' . $this->getAlign() . '"';
		
		if($this->getRowspan() != null)
			$attr .= ' rowspan="' . $this->getRowspan() . '"';
		
		if($this->getColspan() != null)
			$attr .= ' colspan="' . $this->getColspan() . '"';
		
		if($this->getWidth() != null)
			$attr .= ' width="' . $this->getWidth() . '"';
		
		if($this->getHeight() != null)
			$attr .= ' height="' . $this->getHeight() . '"';
		
		return $attr;
	}
	
	private function retAttrs(){
		$attr = "";
		
		$this->setId();
		if($this->getIdFiled() != null)
			$attr .= ' id="' . $this->getIdFiled() . '"';
		
		if($this->getNameField() != null && $this->getTpField() != "abas"){
			$name = ($this->tpField == "label" ? 'for': 'name');
			
			//FIXME: namespace, deve ser quantos caracteres quiser e deve-se propagar a todas as view's filhas
			if($this->getCampoTabela() != null && $this->getParent()->getNamespace() != substr($this->getNameField(), 0, 2))
				$attr .= ' '.$name.'="' . $this->getParent()->getNamespace().substr($this->getNameField(), 3) . '"';
			else
				$attr .= ' '.$name.'="' . $this->getNameField() . '"';
		}
		
		if($this->getValue() != null)
			$attr .= ' value="' . $this->getValue() . '"';
		
		if($this->getCss() != null)
			$attr .= ' class="' . $this->getCss() . '"';
		
		if($this->getStyle() != null)
			$attr .= ' style="' . $this->getStyle() . '"';
		
		if($this->getCellpadding() != null)
			$attr .= ' cellpadding="' . $this->getCellpadding() . '"';
		
		if($this->getCellspacing() != null)
			$attr .= ' cellspacing="' . $this->getCellspacing() . '"';
		
		if($this->getHref() != null)
			$attr .= ' href="' . $this->getHref() . '"';
		
		if($this->getSrc() != null)
			$attr .= ' src="' . $this->getSrc() . '"';
		
		if($this->getAlt() != null)
			$attr .= ' alt="' . htmlentities($this->getAlt()) . '"';
		
		if($this->getTitle() != null)
			$attr .= ' title="' . htmlentities($this->getTitle()) . '"';
		
		if($this->getPlaceholder() != null)
			$attr .= ' placeholder="' . htmlentities($this->getPlaceholder()) . '"';
		
		if($this->getSize() != null)
			$attr .= ' size="' . $this->getSize() . '"';
		
		if($this->getMask() != null)
			$this->getParent()->addScript("$('#". $this->getIdFiled() ."').mask(" . $this->getMask() . ");" . (IS_DEVELOPMENT ? PHP_EOL : ""));
		
		if($this->getDatepicker() == 1)
			$this->getParent()->addScript("$('#". $this->getIdFiled() ."').datepicker();" . (IS_DEVELOPMENT ? PHP_EOL : ""));
		
		if($this->getTarget() != null)
			$attr .= ' target="' . $this->getTarget() . '"';
		
		return $attr;
	}
	
	private function retLabel(){
		$eol = IS_DEVELOPMENT ? PHP_EOL : "";
		$t = IS_DEVELOPMENT ? "\t" : "";
		$ret = "";
		
		if ($this->getLabel() != null){
			
			if ($this->getLblSide() != 1){//Esquerda
					
				if ($this->getLblCol() == 1) {
					if ($this->getNodeParent() == null || $this->getNodeParent()->getTpField() != "td"){
						$ret .= str_repeat($t, self::$tab) . '<td align="right">' . $eol;
						self::$tab++;
					}
				}
					
				$ret .= str_repeat($t, self::$tab) . '<label for="' . $this->getIdFiled() . '">' . $this->getLabel() . '</label>' . $eol;
					
				if ($this->getLblCol() == 1) {
					if ($this->getNodeParent() != null && $this->getNodeParent()->getTpField() == "td"){
						self::$tab--;
						$ret .= str_repeat($t, self::$tab) . '</td>' . $eol;
						$ret .= str_repeat($t, self::$tab) . '<td'.$this->retAttrsCols().'>' . $eol;
						self::$tab++;
					}
					else{
						self::$tab--;
						$ret .= str_repeat($t, self::$tab) . '</td>' . $eol;
					}
				}
			}
			
			if ($this->getLblSide() == 1){//Direita
			
				if ($this->getLblCol() == 1) {
					if ($this->getNodeParent() != null && $this->getNodeParent()->getTpField() == "td"){
						self::$tab--;
						$ret .= str_repeat($t, self::$tab) . '</td>' . $eol;
						$ret .= str_repeat($t, self::$tab) . '<td'.$this->retAttrsCols().'>' . $eol;
						self::$tab++;
					}
					else{
						self::$tab--;
						$ret .= str_repeat($t, self::$tab) . '<td'.$this->retAttrsCols().'>' . $eol;
						self::$tab++;
					}
				}
					
				$ret .= str_repeat($t, self::$tab) . '<label for="' . $this->getIdFiled() . '">' . $this->getLabel() . '</label>' . $eol;
					
				if ($this->getLblCol() == 1) {
					if ($this->getNodeParent() == null || $this->getNodeParent()->getTpField() != "td"){
						self::$tab--;
						$ret .= str_repeat($t, self::$tab) . '</td>' . $eol;
						self::$tab++;
					}
				}
			}
		}
		
		return $ret;
	}
	
	private function retHtmlElement($iput = true, $label = false){
		$eol = IS_DEVELOPMENT ? PHP_EOL : "";
		$t = IS_DEVELOPMENT ? "\t" : "";
		
		$ret = "";
		
		if ($label && $this->getLabel() != null && $this->getLblSide() != 1)
			$ret .= $this->retLabel();
		
		if($iput){
			if(($this->getTpField() == "radio" || $this->getTpField() == "checkbox") && count($this->lists) > 0 ){
				$this->setId();
				$id = $this->getIdFiled();
				$value = $this->getValue();
				
				$name = $this->getNameField();
				$this->setNameField($this->getTpField() == "checkbox" ? $this->getNameField() . '[]' : $this->getNameField());
				
				for ($i = 0; $i < count($this->lists); $i++) {
					
					$this->setIdFiled($id . $this->lists[$i]->getChave());
					$this->setValue($this->lists[$i]->getChave());
					
					$ret .= str_repeat($t, self::$tab) . '<input type="' . $this->getTpField() . '"'.$this->retAttrs(). ($this->lists[$i]->getChecked() == 1 ? ' checked="checked"': '') . '>' . $eol;
					$ret .= str_repeat($t, self::$tab) . '<label for="' . $this->getIdFiled() . '">'. html_entity_decode($this->lists[$i]->getValor()) .'</label>' . $eol;
				}
				
				$this->setIdFiled($id);
				$this->setValue($value);
				$this->setNameField($name);
			}
			else
				$ret .= str_repeat($t, self::$tab) . '<input type="' . $this->getTpField() . '"'.$this->retAttrs().'>' . $eol;
		}
		else{
			$ret .= str_repeat($t, self::$tab) . '<' . $this->getTpField() . $this->retAttrs() . $this->retAttrsCols(). '>' . $eol;
			
			if($this->getTpField() == "label" || $this->getTpField() == "button" || $this->getTpField() == "span"){
				self::$tab++;
				$ret .= str_repeat($t, self::$tab) . $this->getLabel() . $eol;
				self::$tab--;
			}
			
			if($this->getTpField() == "select"){
				
				$ret .= str_repeat($t, self::$tab+1) . '<option value="">&nbsp;</option>' . $eol;
				if(count($this->lists) > 0){
					if($this->lists[0]->getTpList() == 1){
						
						$class = $this->lists[0]->getClasse();
						//require_once "$class.php";
						
						$obj = new $class(new \stdClass(), new PQDExceptions());
						$keys = preg_split("/\|/", $this->lists[0]->getChave());
						$values = preg_split("/\|/", $this->lists[0]->getValor());
						$arr = $obj->{$this->lists[0]->getMetodo()}();
						for ($i = 0; $i< count($arr); $i++){
							self::$tab++;
							$key = "";
							$value = "";
							
							for ($j = 0; $j < count($keys); $j++)
								$key .= substr($keys[$j], 0, 2) == "->" ? $arr[$i]->{substr($keys[$j], 2)}(): $keys[$j];
							
							for ($j = 0; $j < count($values); $j++)
								$value .= substr($values[$j], 0, 2) == "->" ? $arr[$i]->{substr($values[$j], 2)}(): $values[$j];

							$ret .= str_repeat($t, self::$tab) . '<option value="'. $key .'">'. htmlentities(utf8_encode($value)) . '</option>' . $eol;
							self::$tab--;
						}
					}
					else{
						for ($i = 0; $i< count($this->lists); $i++){
							self::$tab++;
							$ret .= str_repeat($t, self::$tab) . '<option value="'. $this->lists[$i]->getChave() .'">'. html_entity_decode($this->lists[$i]->getValor()) . '</option>' . $eol;
							self::$tab--;
						}
					}
				}
			}

			for ($i = 0; $i< count($this->childs); $i++){
				self::$tab++;
				$ret .= $this->childs[$i];
				self::$tab--;
			}
			
			$ret .= str_repeat($t, self::$tab) . '</' . $this->getTpField() . '>' . $eol;
		}
		
		if ($label && $this->getLabel() != null && $this->getLblSide() == 1)
				$ret .= $this->retLabel();
		
		return $ret;
	}
	
	public function __toString(){
		$eol = IS_DEVELOPMENT ? PHP_EOL : "";
		$t = IS_DEVELOPMENT ? "\t" : "";
		
		if(count($this->getEvents()) > 0 ){
			$this->setId();
			foreach ($this->events as $oFieldEvent)
				$this->parent->addScript("$('#" . $this->getIdFiled() . "').on('".$oFieldEvent->getEvent()."', ".$oFieldEvent->getScript().");" . $eol);
		}
		
		switch ($this->getTpField()){
			case 'text':
				return $this->retHtmlElement(true, true);
			break;
			case 'hidden':
				return $this->retHtmlElement(true);
			break;
			case 'textarea':
				$ret = "";
				
				if ($this->getLabel() != null && $this->getLblSide() != 1)
					$ret .= $this->retLabel();
				
				$ret .= str_repeat($t, self::$tab) . '<textarea'.$this->retAttrs().'></textarea>';
				
				if ($this->getLabel() != null && $this->getLblSide() == 1)
					$ret .= $this->retLabel();
				
				return $ret;;
			break;
			case 'radio':
				return $this->retHtmlElement(true, true);
			break;
			case 'checkbox':
				return $this->retHtmlElement(true, true);
			break;
			case 'select':
				return $this->retHtmlElement(false, true);
			break;
			case 'file':
				return $this->retHtmlElement(true, true);
			break;
			case 'password':
				return $this->retHtmlElement(true, true);
			break;
			case 'image':
				return str_repeat($t, self::$tab) . '<img'.$this->retAttrs().'/>' . $eol;
			break;
			case 'guias':
				//FIXME: verificar caso para li
				$ret = str_repeat($t, self::$tab) . '<div'.$this->retAttrs().'>' . $eol;
				self::$tab++;
				$ret .= str_repeat($t, self::$tab) . "<ul>" .$eol;
				for ($i = 0; $i< count($this->childs); $i++){
					self::$tab++;
					if($this->childs[$i]->getTpField() == "abas"){
						$this->childs[$i]->setId();
						$idLi = $this->childs[$i]->getNameField() != null ? ' id="'. $this->childs[$i]->getNameField() .'"': '';
						$ret .= str_repeat($t, self::$tab) . '<li'.$idLi.'><a href="#'.$this->childs[$i]->getIdFiled().'">'.$this->childs[$i]->getLabel().'</a></li>' .$eol;
					}
					self::$tab--;
				}
				$ret .= str_repeat($t, self::$tab) . "</ul>" .$eol;
				self::$tab--;
				
				for ($i = 0; $i< count($this->childs); $i++){
					self::$tab++;
					$ret .= $this->childs[$i];
					self::$tab--;
				}
				
				$ret .= str_repeat($t, self::$tab) . '</div>' . $eol;
				$this->getParent()->addScript("$('#". $this->getIdFiled() ."').tabs();" . $eol);
				return $ret;
			break;
			case 'abas':
				$ret = str_repeat($t, self::$tab) . '<div' . $this->retAttrs(). '>' . $eol;
				for ($i = 0; $i< count($this->childs); $i++){
					self::$tab++;
					$ret .= $this->childs[$i];
					self::$tab--;
				}
				$ret .= str_repeat($t, self::$tab) . '</div>' . $eol;
				return $ret;
			break;
			case 'view':
				
				//FIXME: namespace, deve ser quantos caracteres quiser e deve-se propagar a todas as view's filhas
				if($this->getNameField() != null)
					$this->getPqdViewChild()->setNamespace($this->getNameField());
				
				return (string)$this->getPqdViewChild();
			break;
			case 'col':
				return str_repeat($t, self::$tab) . '<col'.$this->retAttrs().'/>' . $eol;
			break;
			case 'button':
			case 'span':
			case 'div':
			case 'tr':
			case 'td':
			case 'table':
			case 'colgroup':
			case 'fieldset':
			case 'legend':
			case 'a':
			case 'label':
			case 'tbody':
			case 'thead':
			case 'th':
				return $this->retHtmlElement(false);
			break;
			default:
				return "<h1>Alerta Tipo do Elemento n&atilde;o definido!!!!!</h1>";
			break;
		};
	}
	
	public function json() {

		$params = parent::toArray();
		$params['events'] = array();
		$params['lists'] = array();
		
		foreach ($this->events as $event)
			$params['events'][] = $event->toArray();
		
		foreach ($this->lists as $list)
			$params['lists'][] = $list->toArray();
		
		$ret = json_encode($params);
		
		for ($i = 0; $i< count($this->childs); $i++)
			$ret .= ",".$this->childs[$i]->json();
		
		return $ret;
	}
}