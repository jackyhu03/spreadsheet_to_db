<?php

    class sqlc {
        
        private static $conn = null;

        public static function connect($address = "localhost", $name = "mywebs", $password = "", $dbname = "my_mywebs"){
            self::$conn = new mysqli($address, $name, $password, $dbname);
        }

        public static function qry_exec(string $qry){
            if (self::$conn === null) return false;
            $response = self::$conn->query($qry)->fetch_all();
            return $response;
        }

        public static function get_sql_ctx($tables, $table_names, $db_name){

            $sql_ctx = "";
            $sql_ctx .= "\n\n -- CREATED BY |GS2DB| Service \n\n";
            $sql_ctx .= "SET SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO';\n";
            $sql_ctx .= "START TRANSACTION;\n";
            $sql_ctx .= "SET time_zone = '+00:00';\n\n";
            
            $sql_ctx .= "CREATE DATABASE IF NOT EXISTS `{$db_name}`;";

            foreach ($table_names as $table_name){
                $sql_ctx .= self::parseSQL($table_name, $tables[$table_name], $db_name) . "\n\n";
            }

            return $sql_ctx;
        }

        private static function get_script(string $tablename, array $table, string $method, string $db_name){
            // method CREATION   -> ritorna script sql creazione tabella passata 
            // method INSERTION  -> ritorna script sql inserimento dati nella tabella
            $tablename = str_replace(" ", "_", $tablename);
            $sql = "";
            if (!isset($table[0])){
                return $sql;
            }
            switch (strtoupper($method)){

                case "INSERTION": {
                    $cnames = "(";
                    for ($i=0; $i<count($table[0]); $i++){
                        if ($i === count($table[0])-1)
                            $cnames .= "`" . str_replace(" ", "_", $table[0][$i]['value']) . "`)\n";
                        else
                            $cnames .= "`" . str_replace(" ", "_", $table[0][$i]['value']) . "`,";    
                    }
                    $vnames = "";
                    for ($i=1; $i<count($table); $i++){
                        $vnames .= "(";
                        for ($j=0; $j<count($table[0]); $j++){
                            
                            $s = "'";
                            if (SQLTypes::__CATEGORY__($table[0][$j]['type']) === "I") $s = "";
                            
                            if ($table[$i][$j]['value'] === "NULL")
                                $s = "";

                            if ($j === count($table[0])-1){
                                if ($i === count($table)-1)
                                    $vnames .= $s . str_replace(" ", "_", $table[$i][$j]['value']) . $s . ")";
                                else
                                    $vnames .= $s . str_replace(" ", "_", $table[$i][$j]['value']) . $s . "),\n";
                            }else
                                $vnames .= $s . str_replace(" ", "_", $table[$i][$j]['value']) . $s . ",";
                            end:
                        }    
                    }
            
                    $sql = "INSERT INTO `{$db_name}`.`{$tablename}` \n{$cnames}VALUES \n{$vnames};";
                    break;
                }

                case "CREATION": {
                    $sql = "CREATE TABLE IF NOT EXISTS `{$db_name}`.`{$tablename}` (\n";
                    for ($i=0; $i<count($table[0]); $i++){
                        if ($i === count($table[0])-1)
                            $sql .= "\t`" . str_replace(" ", "_", $table[0][$i]['value']) ."` ". $table[0][$i]['type'] . "\n";
                        else
                            $sql .= "\t`" . str_replace(" ", "_", $table[0][$i]['value']) . "` ".  $table[0][$i]['type'] . ",\n";
                    }
                    $sql .= ") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
                    break;
                }

                default: {
                    $sql = "";
                    break;
                }
            }
            return $sql;
        }

        // Ritorna script SQL per creazione tabella ed inserimento dati passati
        public static function parseSQL(string $table_name, array $table, string $db_name){

            /*

                descrizione $table (variabile creata con la funzione GoogleAPI::format_data_adv($json) ):

                    $table[N][M]['value'] => ritorna 'valore' a riga N, colonna M
                    $table[N][M]['type'] => ritona 'tipo' a riga N, colonna M


                    'type' può essere:
                        => 'NULL', in questo caso anche 'value' sarà 'NULL'
                        => 'stringValue'
                        => 'numberValue' (intero, float)
                        => 'boolValue' (true|false)
                        => 'DATE' (yy|yy/-m|m/-d|d)


                    url per fare test postman:    
                        https://sheets.googleapis.com/v4/spreadsheets/13p3_l3bnNjV0K9MuTl2_M37n-0bYySLan2fdpDNeoiY?ranges=TABELLA1!A1:C16&fields=sheets(data(rowData(values(userEnteredFormat%2FnumberFormat%2CuserEnteredValue))%2CstartColumn%2CstartRow))&key=AIzaSyAxrhJVQNqFD43MjLPLlj55OlLXs7yUqJw
                    possibilità di vedere oggetto originale ritornato dalla richiesta

                    (riferimento al nostro foglio google)

            */

            $sql = "\n\n";
            $sql .= "-- CREATION TABLE\n";
            $sql .= sqlc::get_script($table_name, $table, "CREATION", $db_name); // ritorna query per la CREAZIONE della tabella
            $sql .= "\n\n";
            $sql .= "-- INSERTION DATA INTO TABLE\n";
            $sql .= sqlc::get_script($table_name, $table, "INSERTION", $db_name);  // ritorna query per l'INSERIMENTO DATI della tabella
            return $sql;
        }
    }


	class SQLTypes {

		private const DATE_P = ['Y/m/d', 'Y-m-d'];
		private const DATETIME_P = ['Y/m/d H:i:s', 'Y-m-d H:i:s'];
        
        public static function __CATEGORY__($SQL_TYPE){
            switch ($SQL_TYPE){
                case'INT':case'FLOAT':case'DOUBLE':case'TINYINT':case'SMALLINT':case'MEDIUMINT':
                case'BIGINT':case'REAL':case'BIT':case'BOOLEAN':case'SERIAL':
                    {return "I";break;};
                default:{return "S";break;};
            }
        }

		private static function DATETIME($str){

			foreach (self::DATETIME_P as $pattern)
				if (DateTime::createFromFormat($pattern, $str))
					return 1;
			return 0;
		}

		private static function DATE($str){

			foreach (self::DATE_P as $pattern)
				if (DateTime::createFromFormat($pattern, $str))
					return 1;
			return 0;
		}

		private static function YEAR($str){
			return strlen($str) === 4 && strtotime($str); 
		}

		private static function JSON($str){
			json_decode($str);
			if (json_last_error() === JSON_ERROR_NONE) {
				return 1;
			}
			return 0;
		}

		private static function INT($myString){
            $regex = preg_match('/^[0-9]*$/', $myString);
            if( $regex ){
                return true;
            }
            return false;
        }

        private static function FLOAT($str){
            $regex = preg_match('/^-?(?:\d+|\d*\.\d+)$/', $str);
            if( $regex ){
                return true;
            }
            return false;
        }

        private static function DECIMAL($str){
            $regex = preg_match('/^[0-9]+(\.[0-9]{1,2})?$/', $str);
            if ($regex){
                return true;
            }
            return false;
        }

		private static function CHAR($str){
			return strlen($str) < 256 && strlen($str) > -1;
		}

		private static function BOOLEAN($str){
			return in_array(strtoupper($str), array('0','1','TRUE','FALSE'));
		}

		private static function resolve_types($type1, $type2){

			if ($type1 === "NULL") return $type2;
			if ($type2 === "NULL") return $type1;
	
			if ($type1.$type2 === "FLOATDECIMAL" || $type1.$type2 === "DECIMALFLOAT")
				return "FLOAT";
			if ($type1.$type2 === "DATEYEAR" || $type1.$type2 === "YEARDATE")
				return "DATE";
			if (self::get_higher_type($type1) === self::get_higher_type($type2)){
				switch (self::get_higher_type($type1)){
					case 'I': return 'INT';
					case 'S': return "VARCHAR";
					case 'D': return "DATETIME";
				}
			}
			else return 0;
		}

		private static function get_higher_type($type, $bind = false){

			switch($type){
	
				case 'INT': case 'FLOAT': case 'DECIMAL': case 'BOOLEAN': {
					return "I";break;
				}
				case 'VARCHAR': case 'CHAR': case 'JSON': {
					return "S";break;
				}
				case 'DATE': case 'DATETIME': case 'YEAR': {
					return "D";break;
				}
			}
		}

		private static function get_type($v){

			if (self::DATE($v)) 
				return "DATE";
			else if (self::DATETIME($v))
				return "DATETIME";
			else if (self::YEAR($v))
				return "YEAR";
			else if (self::BOOLEAN($v))
				return "BOOLEAN";
			else if (self::INT($v))
				return "INT";
			else if (self::FLOAT($v)){
				if (self::DECIMAL($v))
					return "DECIMAL";
				else return "FLOAT";
			}
			else if (self::JSON($v))
				return "JSON";
			else if (self::CHAR($v))
				return "CHAR";
			else return "VARCHAR";
		}

		private static function get_gs_type($gs){

			$type = "";
	
			if ($gs['type'] === 'DATE')
				$type = "DATE";
			else if ($gs['type'] === "numberValue")
				$type = self::get_type(strval($gs['value']));
			else if ($gs['type'] === "boolValue")
				$type = "BOOLEAN";
			else if ($gs['type'] === "NULL")
				$type = "NULL";
	
			return $type;
		}

		private static function get_vertical_line($m, $index){

			$array = array();
	
			for ($j=1; $j<count($m); $j++)
				if ($m[$j][$index]['type'] === "NULL") continue; 
				else $array[] = strval($m[$j][$index]['value']);
            
			return $array;
		}

		public static function __GET__(&$m){

			$types = array();
	
			for ($i=0; $i<count($m[0]); $i++){
				$type = "";
				for ($j=1; $j<count($m); $j++){
					if ($j === 1){
						if ($m[$j][$i]['type'] !== "stringValue"){
							$type = self::get_gs_type($m[$j][$i]);
						}else{
							$type = self::get_type($m[$j][$i]['value']);
						}
					}
					else{
						$t = "";
						if ($m[$j][$i]['type'] !== "stringValue"){
							$t = self::get_gs_type($m[$j][$i]);
						}else{
							$t = self::get_type($m[$j][$i]['value']);
						}
						if ($type !== $t){
							$type = self::resolve_types($t, $type);
							if (!$type) {$types[] = "VARCHAR"; goto end;} 
						}else{
							$type = $t;
						}
					}
				}
				$types[] = $type;
				end:
			}
	
			if (in_array("VARCHAR", $types)){
				for ($i=0; $i<count($types); $i++){
					if ($types[$i] === "VARCHAR"){
						$lengths = array_map('strlen', self::get_vertical_line($m, $i)); 
						$types[$i] = "VARCHAR(" . max($lengths) . ")";
					}
				}
			}

            if (in_array("INT", $types)){
				for ($i=0; $i<count($types); $i++){
					if ($types[$i] === "INT"){

						$array = array_map('strtolower', self::get_vertical_line($m, $i));

                        for ($j=1; $j<count($array)+1; $j++){
                            if (strtolower($m[$j][$i]['value']) == "true")
                                $m[$j][$i]['value'] = "1";
                            else if (strtolower($m[$j][$i]['value']) == "false")
                                $m[$j][$i]['value'] = "0";
                        }
					}
				}
			}
	
			return $types;
		}
	}

?>