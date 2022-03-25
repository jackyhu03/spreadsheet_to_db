<?php 

    // Le richieste da parte del client possono essere effettate a questa pagina php
    
    // Esempio richiesta:
    
    // Nome servizio:           spreadtodb.php
    // ------------------------------------------
    // Elenco Paramentri
    // ------------------------------------------
    // Link foglio google:      spreadsheeturl=[link foglio google]
    // Riferimento tabella1:    TABLE1=[nometabella1] & INTERVAL1=[A1:C9]
    // Riferimento tabella2:    TABLE2=[nometabella2]
    // Riferimento tabellaN:    TABLEN=[nometabellaN] & INTERVALN=[A1:C4]

    // Di default passando solo il nome del foglio (foglio1, foglio2, ...) il programma restituisce le tabella in un array
    // Se nel foglio oltre alla tabella dovessero esserci altre celle compilate che non fanno parte della tabella,
    // sono necessari i parametri INTERVAL in modo da precisare il range di caselle da prendere in considerazione
    // Se nel caso descritto non si inserisce il parametro INTERVAL viene generato un errore 400

    require_once 'googleTools.php';
    require_once 'class.response.php';
    require_once 'sqlTools.php';
    
    switch ($_SERVER['REQUEST_METHOD']){

        case 'GET': {
            
            // ---> Return found table names
            if (isset($_REQUEST['spreadsheet_url']) && count($_GET) === 1){
                $spreadsheet_id = GoogleAPI::get_spreadsheet_id($_REQUEST['spreadsheet_url']);
                if ($spreadsheet_id === false) response::client_error(400, "Incorrect URL");
                
                else if (GoogleAPI::spreadsheet_permission($spreadsheet_id) === false){
                    response::client_error(403, "PERMISSION_DENIED");
                }

                $spreadsheet_settings = GoogleAPI::get_spreadsheet_settings($spreadsheet_id);
                $table_names = GoogleAPI::get_table_names($spreadsheet_settings);
                if ($table_names === false) response::client_error(400, "No tables found");
                else response::successful(200, false, array("spreadsheet_names" => $table_names));
                exit;
            }

            // ---> Return tables data
            else if (isset($_REQUEST['spreadsheet_url']) && count($_GET) > 1){
                // Verifica parametri passati 
                $link = $_REQUEST['spreadsheet_url'];
                $intervals = array();
                $table_names = array();
                for ($i=1; $i<count($_REQUEST); $i++){
                    if (isset($_REQUEST["TABLE{$i}"])){
                        $table_names[$i-1] = $_REQUEST["TABLE{$i}"];
                        if (isset($_REQUEST["INTERVAL{$i}"])){
                            $intervals[$i-1] = $_REQUEST["INTERVAL{$i}"];
                        }else{
                            $intervals[$i-1] = '';
                        }
                    }else break;
                }

                // errore del client (400) 'parametri errati'
                if (count($table_names) === 0) {
                    response::client_error(400, "Bad parameters");
                } 

                // errore del client (400) 'parametri passati non correttamente o errati'
                if (!isset($table_names[0])) {
                    response::client_error(400, "Wrong or incorrect parameters");
                }
                
                $spreadsheet_id = GoogleAPI::get_spreadsheet_id($link);
                $sql_ctx = "";
                $tables = array();

                foreach (array_combine($table_names, $intervals) as $table_name => $interval){

                    // Get table (array[][])
                    $tables[$table_name] = $table = GoogleAPI::get_spreadsheet($spreadsheet_id, $table_name, $interval);
                    if ($table === false){
                        response::client_error(400, "Il foglio {$table_name} non e' impostato correttamente");
                    }

                    // Get sql code for the table
                    //$sql_ctx .= sqlc::parseSQL($table_name, $table) . "\n\n";
                }
                $sql_b64 = base64_encode($sql_ctx);
                response::successful(200, false, array("tables" => $tables, "sql_b64" => $sql_b64));
                exit;
            }


            
            // ---> Request for download SQL file
            else if (isset($_REQUEST['SQL_B64'])){
                
                $filename = "database.sql";
                $base64sql = $_REQUEST['SQL_B64'];
                file_put_contents($filename, base64_decode($base64sql));
                response::download_file($filename);
            }


            

            break;
        }

        case 'GET': {

            break;
        }

        default: {

            // Method not allowed
            response::client_error(405);
            break;
        }
    }
