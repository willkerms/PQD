<?php

namespace PQD\VIEW;

class FieldAttr {
	/**
	 *
	 * @var int
	 */
	protected $idPqdViewField;
	/**
	 *
	 * @var int
	 */
	protected $idPqdView;
	/**
	 *
	 * @var int
	 */
	protected $idPqdViewChild;
	
	/**
	 *
	 * @var int
	 */
	protected $idPqdViewFieldParent;
	/**
	 *
	 * @var int
	 */
	protected $ordem;
	/**
	 *
	 * @var string
	 */
	protected $tpField;
	/**
	 *
	 * @var string
	 */
	protected $idFiled;
	/**
	 *
	 * @var string
	 */
	protected $nameField;
	/**
	 *
	 * @var string
	 */
	protected $label;
	/**
	 *
	 * @var int
	 */
	protected $lblSide;
	/**
	 *
	 * @var int
	 */
	protected $lblCol;
	/**
	 *
	 * @var string
	 */
	protected $value;
	/**
	 *
	 * @var string
	 */
	protected $placeholder;
	/**
	 *
	 * @var string
	 */
	protected $mask;
	/**
	 *
	 * @var string
	 */
	protected $css;
	/**
	 *
	 * @var string
	 */
	protected $style;
	/**
	 *
	 * @var int
	 */
	protected $datepicker;
	/**
	 *
	 * @var int
	 */
	protected $colspan;
	/**
	 *
	 * @var int
	 */
	protected $rowspan;
	/**
	 *
	 * @var string
	 */
	protected $align;
	/**
	 *
	 * @var int
	 */
	protected $border;
	/**
	 *
	 * @var int
	 */
	protected $cellpadding;
	/**
	 *
	 * @var int
	 */
	protected $cellspacing;
	/**
	 *
	 * @var string
	 */
	protected $width;
	/**
	 *
	 * @var string
	 */
	protected $height;
	/**
	 *
	 * @var string
	 */
	protected $valign;
	/**
	 *
	 * @var string
	 */
	protected $alt;
	/**
	 *
	 * @var string
	 */
	protected $title;
	/**
	 *
	 * @var string
	 */
	protected $src;
	/**
	 *
	 * @var string
	 */
	protected $href;
	/**
	 *
	 * @var string
	 */
	protected $target;
	
	/**
	 * 
	 * @var int
	 */
	protected $size;
	
	/**
	 * 
	 * @var int
	 */
	protected $maxlength;
	/**
	 * 
	 * @var string
	 */
	protected $campoTabela;
	/**
	 * 
	 * @var bool
	 */
	protected $isDouble;
	/**
	 * 
	 * @var int
	 */
	protected $precisaoDecimal;
	
	/**
	 *
	 * @return int
	 */
	public function getIdPqdViewField(){
		return $this->idPqdViewField;
	}
	/**
	 *
	 * @return int
	 */
	public function getIdPqdView(){
		return $this->idPqdView;
	}
	/**
	 *
	 * @return int
	 */
	public function getIdPqdViewChild(){
		return $this->idPqdViewChild;
	}
	/**
	 *
	 * @return int
	 */
	public function getIdPqdViewFieldParent(){
		return $this->idPqdViewFieldParent;
	}
	/**
	 *
	 * @return int
	 */
	public function getOrdem(){
		return $this->ordem;
	}
	/**
	 *
	 * @return string
	 */
	public function getTpField(){
		return $this->tpField;
	}
	/**
	 *
	 * @return string
	 */
	public function getIdFiled(){
		return $this->idFiled;
	}
	/**
	 *
	 * @return string
	 */
	public function getNameField(){
		return $this->nameField;
	}
	/**
	 *
	 * @return string
	 */
	public function getLabel(){
		return $this->label;
	}
	/**
	 *
	 * @return int
	 */
	public function getLblSide(){
		return $this->lblSide;
	}
	/**
	 *
	 * @return int
	 */
	public function getLblCol(){
		return $this->lblCol;
	}
	/**
	 *
	 * @return string
	 */
	public function getValue(){
		return $this->value;
	}
	/**
	 *
	 * @return string
	 */
	public function getPlaceholder(){
		return $this->placeholder;
	}
	/**
	 *
	 * @return string
	 */
	public function getMask(){
		return $this->mask;
	}
	/**
	 *
	 * @return string
	 */
	public function getCss(){
		return $this->css;
	}
	/**
	 *
	 * @return string
	 */
	public function getStyle(){
		return $this->style;
	}
	/**
	 *
	 * @return int
	 */
	public function getDatepicker(){
		return $this->datepicker;
	}
	/**
	 *
	 * @return int
	 */
	public function getColspan(){
		return $this->colspan;
	}
	/**
	 *
	 * @return int
	 */
	public function getRowspan(){
		return $this->rowspan;
	}
	/**
	 *
	 * @return string
	 */
	public function getAlign(){
		return $this->align;
	}
	/**
	 *
	 * @return int
	 */
	public function getBorder(){
		return $this->border;
	}
	/**
	 *
	 * @return int
	 */
	public function getCellpadding(){
		return $this->cellpadding;
	}
	/**
	 *
	 * @return int
	 */
	public function getCellspacing(){
		return $this->cellspacing;
	}
	/**
	 *
	 * @return string
	 */
	public function getWidth(){
		return $this->width;
	}
	/**
	 *
	 * @return string
	 */
	public function getHeight(){
		return $this->height;
	}
	/**
	 *
	 * @return string
	 */
	public function getValign(){
		return $this->valign;
	}
	/**
	 *
	 * @return string
	 */
	public function getAlt(){
		return $this->alt;
	}
	/**
	 *
	 * @return string
	 */
	public function getTitle(){
		return $this->title;
	}
	/**
	 *
	 * @return string
	 */
	public function getSrc(){
		return $this->src;
	}
	/**
	 *
	 * @return string
	 */
	public function getHref(){
		return $this->href;
	}
	/**
	 *
	 * @return string
	 */
	public function getTarget(){
		return $this->target;
	}
	
	/**
	 *
	 * @return int
	 */
	public function getSize(){
		return $this->size;
	}
	
	/**
	 *
	 * @return int
	 */
	public function getMaxlength(){
		return $this->maxlength;
	}
	
	/**
	 *
	 * @param int $idPqdViewField
	 */
	public function setIdPqdViewField($idPqdViewField){
		$this->idPqdViewField = $idPqdViewField;
	}
	/**
	 *
	 * @param int $idPqdView
	 */
	public function setIdPqdView($idPqdView){
		$this->idPqdView = $idPqdView;
	}
	/**
	 *
	 * @param int $idPqdViewChild
	 */
	public function setIdPqdViewChild($idPqdViewChild){
		$this->idPqdViewChild = $idPqdViewChild;
	}
	/**
	 *
	 * @param int $idPqdViewFieldParent
	 */
	public function setIdPqdViewFieldParent($idPqdViewFieldParent){
		$this->idPqdViewFieldParent = $idPqdViewFieldParent;
	}
	/**
	 *
	 * @param int $ordem
	 */
	public function setOrdem($ordem){
		$this->ordem = $ordem;
	}
	/**
	 *
	 * @param string $tpField
	 */
	public function setTpField($tpField){
		$this->tpField = $tpField;
	}
	/**
	 *
	 * @param string $idFiled
	 */
	public function setIdFiled($idFiled){
		$this->idFiled = $idFiled;
	}
	/**
	 *
	 * @param string $nameField
	 */
	public function setNameField($nameField){
		$this->nameField = $nameField;
	}
	/**
	 *
	 * @param string $label
	 */
	public function setLabel($label){
		$this->label = $label;
	}
	/**
	 *
	 * @param int $lblSide
	 */
	public function setLblSide($lblSide){
		$this->lblSide = $lblSide;
	}
	/**
	 *
	 * @param int $lblCol
	 */
	public function setLblCol($lblCol){
		$this->lblCol = $lblCol;
	}
	/**
	 *
	 * @param string $value
	 */
	public function setValue($value){
		$this->value = $value;
	}
	/**
	 *
	 * @param string $placeholder
	 */
	public function setPlaceholder($placeholder){
		$this->placeholder = $placeholder;
	}
	/**
	 *
	 * @param string $mask
	 */
	public function setMask($mask){
		$this->mask = $mask;
	}
	/**
	 *
	 * @param string $css
	 */
	public function setCss($css){
		$this->css = $css;
	}
	/**
	 *
	 * @param string $style
	 */
	public function setStyle($style){
		$this->style = $style;
	}
	/**
	 *
	 * @param int $datepicker
	 */
	public function setDatepicker($datepicker){
		$this->datepicker = $datepicker;
	}
	/**
	 *
	 * @param int $colspan
	 */
	public function setColspan($colspan){
		$this->colspan = $colspan;
	}
	/**
	 *
	 * @param int $rowspan
	 */
	public function setRowspan($rowspan){
		$this->rowspan = $rowspan;
	}
	/**
	 *
	 * @param string $align
	 */
	public function setAlign($align){
		$this->align = $align;
	}
	/**
	 *
	 * @param int $border
	 */
	public function setBorder($border){
		$this->border = $border;
	}
	/**
	 *
	 * @param int $cellpadding
	 */
	public function setCellpadding($cellpadding){
		$this->cellpadding = $cellpadding;
	}
	/**
	 *
	 * @param int $cellspacing
	 */
	public function setCellspacing($cellspacing){
		$this->cellspacing = $cellspacing;
	}
	/**
	 *
	 * @param string $width
	 */
	public function setWidth($width){
		$this->width = $width;
	}
	/**
	 *
	 * @param string $height
	 */
	public function setHeight($height){
		$this->height = $height;
	}
	/**
	 *
	 * @param string $valign
	 */
	public function setValign($valign){
		$this->valign = $valign;
	}
	/**
	 *
	 * @param string $alt
	 */
	public function setAlt($alt){
		$this->alt = $alt;
	}
	/**
	 *
	 * @param string $title
	 */
	public function setTitle($title){
		$this->title = $title;
	}
	/**
	 *
	 * @param string $src
	 */
	public function setSrc($src){
		$this->src = $src;
	}
	/**
	 *
	 * @param string $href
	 */
	public function setHref($href){
		$this->href = $href;
	}
	/**
	 *
	 * @param string $target
	 */
	public function setTarget($target){
		$this->target = $target;
	}
	/**
	 *
	 * @param int $size
	 */
	public function setSize($size){
		$this->size = $size;
	}
	/**
	 *
	 * @param int $maxlength
	 */
	public function setMaxlength($maxlength){
		$this->maxlength = $maxlength;
	}
	
	/**
	 * @return string $campoTabela
	 */
	public function getCampoTabela() {
		return $this->campoTabela;
	}

	/**
	 * @return bool $isDouble
	 */
	public function getIsDouble() {
		return $this->isDouble;
	}

	/**
	 * @return int $precisaoDecimal
	 */
	public function getPrecisaoDecimal() {
		return $this->precisaoDecimal;
	}

	/**
	 * @param string $campoTabela
	 */
	public function setCampoTabela($campoTabela) {
		$this->campoTabela = $campoTabela;
	}

	/**
	 * @param boolean $isDouble
	 */
	public function setIsDouble($isDouble) {
		$this->isDouble = $isDouble;
	}

	/**
	 * @param number $precisaoDecimal
	 */
	public function setPrecisaoDecimal($precisaoDecimal) {
		$this->precisaoDecimal = $precisaoDecimal;
	}

	public function toArray(){
		$arr = get_class_vars(__CLASS__);
		
		foreach ($arr as $key => $value)
			$arr[$key] = $this->{$key};
		
		return $arr;
	}
}