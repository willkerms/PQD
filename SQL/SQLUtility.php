<?php 
namespace PQD\SQL;

trait SQLUtility{
	/**
	 * Retorna o caracter que escapa a coluna para o banco de dados
	 * 
	 * @return string
	 */
	private function retEscapeColumn(){
		switch($this->getDriverDB($this->getIndexCon())){
			case 'mysql':
				return '`';
			break;
			case 'mssql':
				return '"';
			break;
		}
		return "";
	}
}