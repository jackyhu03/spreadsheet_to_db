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

    require 'DataStructures/class.googleAPI.php';
    require 'DataStructures/class.response.php';
    require 'DataStructures/class.sqlc.php';

    switch ($_SERVER['REQUEST_METHOD']){

        case 'GET': {
            
            if (isset($_GET['spreadsheeturl'])){

                // Verifica parametri passati 
                $link = $_GET['spreadsheeturl'];
                $intervals = array();
                $table_names = array();
                for ($i=1; $i<count($_GET); $i++){
                    if (isset($_GET["TABLE{$i}"])){
                        $table_names[$i-1] = $_GET["TABLE{$i}"];
                        if (isset($_GET["INTERVAL{$i}"])){
                            $intervals[$i-1] = $_GET["INTERVAL{$i}"];
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
                
                $spreadsheet_id = googleAPI::get_spreadsheet_id($link);
                $sql_ctx = "";

                foreach (array_combine($table_names, $intervals) as $table_name => $interval){

                    // Get table (array[][])
                    $table = googleAPI::get_spreadsheet($spreadsheet_id, $table_name, $interval);

                    if ($table === false){
                        response::client_error(400, "Il foglio {$table_name} non e' impostato correttamente");
                    }

                    // Get sql code for the table
                    $sql_ctx .= sqlc::parseSQL($table_name, $table) . "\n\n";
                }
                
                file_put_contents('database.sql', $sql_ctx);

                // download database.sql file on client side
                // response::download_file('database.sql');
            }

            break;
        }

        case 'POST': {

            break;
        }

        default: {

            // Method not allowed
            response::client_error(405);
            break;
        }
    }
    

?>