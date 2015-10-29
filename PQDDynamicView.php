<?php
namespace PQD;

use PQD\VIEW\FieldEvent;

use PQD\VIEW\FieldList;

use PQD\VIEW\FieldAttr;

use PQD\VIEW\Field;

use PQD\VIEW\ViewAttr;

use PQD\VIEW\View;

class PQDDynamicView {
	
	public static function view($idOrSigla, array $aObjs = null,\PDO $connection = null, $preview = false){
		$connection = !is_null($connection) ? $connection : (new PQDDb(new PQDExceptions()))->getConnection();
		
		$oView = new View($idOrSigla, $aObjs, null, $connection);
		$oView->setPreview($preview);
		echo $oView;
	}
	
	public static function retView($idOrSigla, array $aObjs = null,\PDO $connection = null){
		$connection = !is_null($connection) ? $connection : (new PQDDb(new PQDExceptions()))->getConnection();
		return (string)(new View($idOrSigla, $aObjs, null, $connection));
	}
	
	public static function retJson($idOrSigla, \PDO $connection = null){
		$connection = !is_null($connection) ? $connection : (new PQDDb(new PQDExceptions()))->getConnection();
		return (new View($idOrSigla, null, null, $connection))->json();
	}
	
	public static function deleteFieldEvent($idEventDelete, \PDO $connection = null){
		
		$connection = !is_null($connection) ? $connection : (new PQDDb(new PQDExceptions()))->getConnection();
		
		$st = $connection->prepare("
			DELETE FROM pqd_viewfieldsevents where idPqdViewFieldEvent = :idPqdViewFieldEvent;
		");
		
		$st->bindValue(":idPqdViewFieldEvent", $idEventDelete, \PDO::PARAM_INT);
		
		if(!$st->execute()){
			$error = $st->errorInfo();
			throw new \Exception($error[2], $error[1]);
		}
	}
	
	public static function deleteFieldList($idListDelete, \PDO $connection = null){
		
		$connection = !is_null($connection) ? $connection : (new PQDDb(new PQDExceptions()))->getConnection();
		
		$st = $connection->prepare("
			DELETE FROM pqd_viewFieldsList where idPqdViewFieldList = :idPqdViewFieldList;
		");
		
		$st->bindValue(":idPqdViewFieldList", $idListDelete, \PDO::PARAM_INT);
		
		if(!$st->execute()){
			$error = $st->errorInfo();
			throw new \Exception($error[2], $error[1]);
		}
	}
	
	public static function saveFieldList(FieldList $list, \PDO $connection = null){
		$connection = !is_null($connection) ? $connection : (new PQDDb(new PQDExceptions()))->getConnection();
		
		if ($list->getIdViewFieldList() > 0) {
			$st = $connection->prepare("
				update pqd_viewFieldsList set
					idPqdViewField = :idPqdViewField,
					tpList = :tpList,
					ordem = :ordem,
					chave = :chave,
					valor = :valor,
					classe = :classe,
					metodo = :metodo,
					checked = :checked
				where
					idViewFieldList = :idViewFieldList;
			");
			$st->bindValue(":idViewFieldList", $list->getIdViewFieldList(), \PDO::PARAM_INT);
		}
		else{
			$st = $connection->prepare("
				INSERT INTO pqd_viewFieldsList(
					idPqdViewField,
					tpList,
					ordem,
					chave,
					valor,
					classe,
					metodo,
					checked
				)
				VALUES(
					:idPqdViewField,
					:tpList,
					:ordem,
					:chave,
					:valor,
					:classe,
					:metodo,
					:checked
				);
			");
		}
		$st->bindValue(":idPqdViewField", $list->getIdPqdViewField(), \PDO::PARAM_INT);
		$st->bindValue(":tpList", $list->getTpList(), \PDO::PARAM_INT);
		$st->bindValue(":ordem", $list->getOrdem(), \PDO::PARAM_INT);
		$st->bindValue(":chave", $list->getChave(), \PDO::PARAM_STR);
		$st->bindValue(":valor", ($list->getTpList() == 1 ? $list->getValor() : htmlentities($list->getValor())), \PDO::PARAM_STR);
		$st->bindValue(":classe", $list->getClasse(), \PDO::PARAM_STR);
		$st->bindValue(":metodo", $list->getMetodo(), \PDO::PARAM_STR);
		$st->bindValue(":checked", $list->getChecked(), \PDO::PARAM_BOOL);
		
		if(!$st->execute()){
			$error = $st->errorInfo();
			throw new \Exception($error[2], $error[1]);
		}
	}
	
	public static function saveFieldEvent(FieldEvent $event, \PDO $connection = null){
		$connection = !is_null($connection) ? $connection : (new PQDDb(new PQDExceptions()))->getConnection();
		
		if ($event->getIdPqdViewFieldEvent() > 0) {
			$st = $connection->prepare("
				update pqd_viewfieldsevents set
					idPqdViewField = :idPqdViewField,
					event = :event,
					script = :script
				where
					idPqdViewFieldEvent = :idPqdViewFieldEvent;
			");
			$st->bindValue(":idPqdViewFieldEvent", $event->getIdPqdViewFieldEvent(), \PDO::PARAM_INT);
		}
		else{
			$st = $connection->prepare("
				INSERT INTO pqd_viewfieldsevents(
					idPqdViewField,
					event,
					script
				)
				VALUES(
					:idPqdViewField,
					:event,
					:script
				);
			");
		}
		$st->bindValue(":idPqdViewField", $event->getIdPqdViewField(), \PDO::PARAM_INT);
		$st->bindValue(":event", $event->getEvent(), \PDO::PARAM_STR);
		$st->bindValue(":script", $event->getScript(), \PDO::PARAM_STR);
		
		if(!$st->execute()){
			$error = $st->errorInfo();
			throw new \Exception($error[2], $error[1]);
		}
	}
	
	public static function saveFieldView(FieldAttr $field, \PDO $connection = null){

		$connection = !is_null($connection) ? $connection : (new PQDDb(new PQDExceptions()))->getConnection();
		
		if ($field->getIdPqdViewField() > 0) {
			$st = $connection->prepare("
				update pqd_viewFields set 
					idPqdView = :idPqdView,
					idPqdViewChild = :idPqdViewChild,
					idPqdViewFieldParent = :idPqdViewFieldParent,
					ordem = :ordem,
					tpField = :tpField,
					idFiled = :idFiled,
					nameField = :nameField,
					label = :label,
					lblSide = :lblSide,
					lblCol = :lblCol,
					value = :value,
					placeholder = :placeholder,
					mask = :mask,
					css = :css,
					style = :style,
					datepicker = :datepicker,
					colspan = :colspan,
					rowspan = :rowspan,
					align = :align,
					border = :border,
					cellpadding = :cellpadding,
					cellspacing = :cellspacing,
					width = :width,
					height = :height,
					valign = :valign,
					alt = :alt,
					title = :title,
					src = :src,
					href = :href,
					target = :target,
					size = :size,
					maxlength = :maxlength,
					campoTabela = :campoTabela,
					isDouble = :isDouble,
					precisaoDecimal = :precisaoDecimal
				where
					idPqdViewField = :idPqdViewField
			");
			$st->bindValue(":idPqdViewField", $field->getIdPqdViewField(), \PDO::PARAM_INT);
		}
		else{
			$st = $connection->prepare("
				INSERT INTO pqd_viewFields(
					idPqdView,
					idPqdViewChild,
					idPqdViewFieldParent,
					ordem,
					tpField,
					idFiled,
					nameField,
					label,
					lblSide,
					lblCol,
					value,
					placeholder,
					mask,
					css,
					style,
					datepicker,
					colspan,
					rowspan,
					align,
					border,
					cellpadding,
					cellspacing,
					width,
					height,
					valign,
					alt,
					title,
					src,
					href,
					target,
					size,
					maxlength,
					campoTabela,
					isDouble,
					precisaoDecimal
				)
				VALUES(
					:idPqdView,
					:idPqdViewChild,
					:idPqdViewFieldParent,
					:ordem,
					:tpField,
					:idFiled,
					:nameField,
					:label,
					:lblSide,
					:lblCol,
					:value,
					:placeholder,
					:mask,
					:css,
					:style,
					:datepicker,
					:colspan,
					:rowspan,
					:align,
					:border,
					:cellpadding,
					:cellspacing,
					:width,
					:height,
					:valign,
					:alt,
					:title,
					:src,
					:href,
					:target,
					:size,
					:maxlength,
					:campoTabela,
					:isDouble,
					:precisaoDecimal
				);
			");
		}
		$st->bindValue(":idPqdView", $field->getIdPqdView(), \PDO::PARAM_INT);
		$st->bindValue(":idPqdViewChild", $field->getIdPqdViewChild(), \PDO::PARAM_INT);
		$st->bindValue(":idPqdViewFieldParent", $field->getIdPqdViewFieldParent(), \PDO::PARAM_INT);
		$st->bindValue(":ordem", $field->getOrdem(), \PDO::PARAM_INT);
		$st->bindValue(":tpField", $field->getTpField(), \PDO::PARAM_STR);
		$st->bindValue(":idFiled", $field->getIdFiled(), \PDO::PARAM_STR);
		$st->bindValue(":nameField", $field->getNameField(), \PDO::PARAM_STR);
		$st->bindValue(":label", $field->getLabel(), \PDO::PARAM_STR);
		$st->bindValue(":lblSide", $field->getLblSide(), \PDO::PARAM_INT);
		$st->bindValue(":lblCol", $field->getLblCol(), \PDO::PARAM_INT);
		$st->bindValue(":value", $field->getValue(), \PDO::PARAM_STR);
		$st->bindValue(":placeholder", $field->getPlaceholder(), \PDO::PARAM_STR);
		$st->bindValue(":mask", $field->getMask(), \PDO::PARAM_STR);
		$st->bindValue(":css", $field->getCss(), \PDO::PARAM_STR);
		$st->bindValue(":style", $field->getStyle(), \PDO::PARAM_STR);
		$st->bindValue(":datepicker", $field->getDatepicker(), \PDO::PARAM_BOOL);
		$st->bindValue(":colspan", $field->getColspan(), \PDO::PARAM_INT);
		$st->bindValue(":rowspan", $field->getRowspan(), \PDO::PARAM_INT);
		$st->bindValue(":align", $field->getAlign(), \PDO::PARAM_STR);
		$st->bindValue(":border", $field->getBorder(), \PDO::PARAM_INT);
		$st->bindValue(":cellpadding", $field->getCellpadding(), \PDO::PARAM_INT);
		$st->bindValue(":cellspacing", $field->getCellspacing(), \PDO::PARAM_INT);
		$st->bindValue(":width", $field->getWidth(), \PDO::PARAM_STR);
		$st->bindValue(":height", $field->getHeight(), \PDO::PARAM_STR);
		$st->bindValue(":valign", $field->getValign(), \PDO::PARAM_STR);
		$st->bindValue(":alt", $field->getAlt(), \PDO::PARAM_STR);
		$st->bindValue(":title", $field->getTitle(), \PDO::PARAM_STR);
		$st->bindValue(":src", $field->getSrc(), \PDO::PARAM_STR);
		$st->bindValue(":href", $field->getHref(), \PDO::PARAM_STR);
		$st->bindValue(":target", $field->getTarget(), \PDO::PARAM_STR);
		$st->bindValue(":size", $field->getSize(), \PDO::PARAM_INT);
		$st->bindValue(":maxlength", $field->getMaxlength(), \PDO::PARAM_INT);
		$st->bindValue(":campoTabela", $field->getCampoTabela(), \PDO::PARAM_STR);
		$st->bindValue(":isDouble", $field->getIsDouble(), \PDO::PARAM_BOOL);
		$st->bindValue(":precisaoDecimal", $field->getPrecisaoDecimal(), \PDO::PARAM_INT);
		
		if(!$st->execute()){
			$error = $st->errorInfo();
			throw new \Exception($error[2], $error[1]);
		}
		else{
			if(is_null($field->getIdPqdViewField()))
				$field->setIdPqdViewField($connection->lastInsertId());
		}
		
		return $field;
	}
	
	public static function deleteField($idField, \PDO $connection = null){
		$connection = !is_null($connection) ? $connection : (new PQDDb(new PQDExceptions()))->getConnection();
		
		$st = $connection->prepare("
					DELETE FROM pqd_viewFields WHERE idPqdViewField = :idPqdViewField;
				");
		
		$st->bindParam(":idPqdViewField", $idField, \PDO::PARAM_INT);
		if(!$st->execute()){
			$error = $st->errorInfo();
			throw new \Exception($error[2], $error[1]);
		}
	}
	
	public static function createView(ViewAttr $view, \PDO $connection = null){
		
		$connection = !is_null($connection) ? $connection : (new PQDDb(new PQDExceptions()))->getConnection();
		if($connection->getAttribute(\PDO::ATTR_DRIVER_NAME) == "mysql"){
			$st = $connection->prepare("
				insert into pqd_view values(
					DEFAULT,
					:sigla,
					:descricao,
					:namespace,
					:tabela
				);
			");
		}
		else{//SQL Server
			$st = $connection->prepare("
				insert into pqd_view values(
					:sigla,
					:descricao,
					:namespace,
					:tabela
				);
			");
		}
		
		$st->bindValue(":sigla", $view->getSigla(), \PDO::PARAM_STR);
		$st->bindValue(":descricao", $view->getDescricao(), \PDO::PARAM_STR);
		$st->bindValue(":namespace", $view->getNamespace(), \PDO::PARAM_STR);
		$st->bindValue(":tabela", $view->getTabela(), \PDO::PARAM_STR);
		
		if($st->execute()){
			
			$view->setIdPqdView($connection->lastInsertId());
			
			if (!is_null($view->getTabela())) {
				
				$st = $connection->prepare("
					SELECT COLUMN_NAME, CHARACTER_MAXIMUM_LENGTH FROM INFORMATION_SCHEMA.COLUMNS where table_name =  :tableName AND TABLE_SCHEMA = :schema
				");
				$st->bindValue(":tableName", $view->getTabela(), \PDO::PARAM_STR);
				$st->bindValue(":schema", PQD_DBPQD, \PDO::PARAM_STR);//FIXME: banco de dados
				$st->execute();
				
				$aCols = $st->fetchAll(\PDO::FETCH_NUM);
				$st = $connection->prepare("
					insert into pqd_viewFields (idPqdView, tpField, idFiled, nameField, label, idPqdViewFieldParent, maxlength, campoTabela) 
					values						(:idPqdView, :tpField, :idFiled, :nameField, :label, :idPqdViewFieldParent, :maxlength, :campoTabela);
				");
				$table = new FieldAttr();
				$table->setTpField('table');
				$table->setIdPqdView($view->getIdPqdView());
				$table = self::saveFieldView($table, $connection);
				
				for ($i=0; $i<count($aCols); $i += 2){
					
					$tr = new FieldAttr();
					$tr->setTpField('tr');
					$tr->setIdPqdView($view->getIdPqdView());
					$tr->setIdPqdViewFieldParent($table->getIdPqdViewField());
					$tr = self::saveFieldView($tr, $connection);
					
					$td = new FieldAttr();
					$td->setTpField('td');
					$td->setIdPqdView($view->getIdPqdView());
					$td->setIdPqdViewFieldParent($tr->getIdPqdViewField());
					$td = self::saveFieldView($td, $connection);
						
					$inp = new FieldAttr();
					$inp->setTpField('text');
					$inp->setIdPqdView($view->getIdPqdView());
					$inp->setIdPqdViewFieldParent($td->getIdPqdViewField());
					$inp->setIdFiled($view->getNamespace() . ucwords($aCols[$i][0]));
					$inp->setNameField($view->getNamespace() . ":" . $aCols[$i][0]);
					$inp->setLabel($aCols[$i][0]);
					$inp->setCampoTabela($aCols[$i][0]);
					$inp->setMaxlength($aCols[$i][1]);
					self::saveFieldView($inp, $connection);
						
					if(isset($aCols[$i+1])){
						$td = new FieldAttr();
						$td->setTpField('td');
						$td->setIdPqdView($view->getIdPqdView());
						$td->setIdPqdViewFieldParent($tr->getIdPqdViewField());
						self::saveFieldView($td, $connection);
						
						$inp = new FieldAttr();
						$inp->setTpField('text');
						$inp->setIdPqdView($view->getIdPqdView());
						$inp->setIdPqdViewFieldParent($td->getIdPqdViewField());
						$inp->setIdFiled($view->getNamespace() . ucwords($aCols[$i+1][0]));
						$inp->setNameField($view->getNamespace() . ":" . $aCols[$i+1][0]);
						$inp->setLabel($aCols[$i+1][0]);
						$inp->setCampoTabela($aCols[$i+1][0]);
						$inp->setMaxlength($aCols[$i+1][1]);
						self::saveFieldView($inp, $connection);
					}
				}
			}
		}
		else{
			$error = $st->errorInfo();
			throw new \Exception($error[2], $error[1]);
		}
		
		return new View($view->getIdPqdView(), null, null, $connection);
	}
}