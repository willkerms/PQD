<?php

namespace libs\PQD;

class PQDLogs {
	
	static function log(\PDO $oConexao, $table, $pk){
		//TODO: PQDLogs
		$oConexao->prepare("
			INSERT INTO logs(
				idLog,
				idCliMatriz,
				idUsuario,
				tabela,
				campo,
				valor,
				datahora,
				ip
			)
			VALUES(
				:idLog,
				:idCliMatriz,
				:idUsuario,
				:tabela,
				:campo,
				:valor,
				:datahora,
				:ip
			);
		");
	}
}