<?php

    class sqlc {
        
        private static $conn = null;

        public static function connect($address = "127.0.0.1", $name = "root", $password = "", $dbname = ""){
            self::$conn = new mysqli($address, $name, $password, $dbname);
        }

        public static function qry_exec(string $qry){
            if (self::$conn === null) return false;
            $response = self::$conn->query($qry)->fetch_all();
            if (strpos($qry, "SELECT") >= 0)
                return empty($response) ? null : $response;
            else return $response; 
        }

        private static function get_script(string $tablename, array $table, string $method){
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
                            $cnames .= str_replace(" ", "_", $table[0][$i]) . ")";
                        else
                            $cnames .= str_replace(" ", "_", $table[0][$i]) . ", ";    
                    }
                    $vnames = "";
                    for ($i=1; $i<count($table); $i++){
                        $vnames .= "(";
                        for ($j=0; $j<count($table[0]); $j++){
                            if ($j === count($table[0])-1)
                                if ($i === count($table)-1)
                                    $vnames .= "'" . str_replace(" ", "_", $table[$i][$j]) . "'" . ")";
                                else
                                    $vnames .= "'" . str_replace(" ", "_", $table[$i][$j]) . "'" . "), ";
                            else
                                $vnames .= "'" . str_replace(" ", "_", $table[$i][$j]) . "'" . ", ";
                        }    
                    }
            
                    $sql = "INSERT INTO {$tablename} {$cnames} VALUES {$vnames};";
                    break;
                }

                case "CREATION": {
                    $sql = "CREATE TABLE {$tablename} (";
                    for ($i=0; $i<count($table[0]); $i++){
                        if ($i === count($table[0])-1)
                            $sql .= str_replace(" ", "_", $table[0][$i]) . " VARCHAR(255) NOT NULL";
                        else
                            $sql .= str_replace(" ", "_", $table[0][$i]) . " VARCHAR(255) NOT NULL, ";
                    }
                    $sql .= ");";
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
        public static function parseSQL(string $table_name, array $table){

            /*

                descrizione $table (variabile creata con la funzione GoogleAPI::format_data_adv($json) ):

                    $table[N][M]['value'] => ritorna 'valore' a riga N, colonna M
                    $table[N][M]['type'] => ritona 'tipo' a riga N, colonna M


                    'type' può essere:
                        => 'NULL', in questo caso anche 'value' sarà 'NULL'
                        => 'stringValue'
                        => 'numberValue' (intero, float)
                        => 'boolValue' (true|false)
                        => 'DATE' (gg/mm/yyyy)

                    -- forse altri

                    url per fare test postman:    
                        https://sheets.googleapis.com/v4/spreadsheets/13p3_l3bnNjV0K9MuTl2_M37n-0bYySLan2fdpDNeoiY?ranges=TABELLA1!A1:C16&fields=sheets(data(rowData(values(userEnteredFormat%2FnumberFormat%2CuserEnteredValue))%2CstartColumn%2CstartRow))&key=AIzaSyAxrhJVQNqFD43MjLPLlj55OlLXs7yUqJw
                    possibilità di vedere oggetto originale ritornato dalla richiesta

                    (riferimento al nostro foglio google)

            */


            $sql = "";
            $sql .= "-- CREATION TABLE\n";
            $sql .= sqlc::get_script($table_name, $table, "CREATION"); // ritorna query per la CREAZIONE della tabella
            $sql .= "\n";
            $sql .= "-- INSERTION DATA INTO TABLE\n";
            $sql .= sqlc::get_script($table_name, $table, "INSERTION");  // ritorna query per l'INSERIMENTO DATI della tabella
            return $sql;
        }
    }

?>