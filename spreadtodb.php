<?php 

    // Le richieste da parte del client possono essere effettate a questa pagina php

    // Esempio richiesta:
    
    // Nome servizio:           spreadtodb.php
    // ------------------------------------------
    // Elenco Paramentri
    // ------------------------------------------
    // Link foglio google:      spreadsheeturl=https://docs.google.com/spreadsheets/d/13p3_l3bnNjV0K9MuTl2_M37n-0bYySLan2fdpDNeoiY/edit
    // Riferimento tabella1:    TABLE1=nometabella1 & INTERVAL1=A1:C9
    // Riferimento tabella2:    TABLE2=nometabella2
    // Riferimento tabellaN:    TABLEN=nometabellaN & INTERVALN=A1:C4

    // Di default passando solo il nome del foglio (foglio1, foglio2, ...) il programma restituisce le tabella in un array
    // Se nel foglio oltre alla tabella dovessero esserci altre celle compilate che non fanno parte della tabella,
    // sono necessari i parametri INTERVAL in modo da precisare il range di caselle da prendere in considerazione
    // Se nel caso descritto non si inserisce il parametro INTERVAL viene generato un errore 400

    require 'DataStructures/class.googleAPI.php';
    require 'DataStructures/class.response.php';

    switch ($_SERVER['REQUEST_METHOD']){

        case 'GET': {
            
            if (isset($_GET['spreadsheeturl'])){

                // Verifica parametri passati 
                $link = $_GET['spreadsheeturl'];
                $interval = array();
                $table = array();
                for ($i=1; $i<count($_GET); $i++){
                    if (isset($_GET["TABLE{$i}"])){
                        $table[$i-1] = $_GET["TABLE{$i}"];
                        if (isset($_GET["INTERVAL{$i}"])){
                            $interval[$i-1] = $_GET["INTERVAL{$i}"];
                        }else{
                            $interval[$i-1] = '';
                        }
                    }else break;
                }

                // errore del client (400) 'parametri errati'
                if (count($table) === 0) {
                    response::client_error(400, "Bad parameters");
                } 

                // errore del client (400) 'parametri passati non correttamente o errati'
                if (!isset($table[0])) {
                    response::client_error(400, "Wrong or incorrect parameters");
                }
                
                // Lettura delle tabelle dal foglio google
                $spreadsheet_id = googleAPI::get_spreadsheet_id($link);
                $tables = array();
                for ($i=0; $i<count($table); $i++){
                    $tables[$table[$i]] = googleAPI::get_spreadsheet($spreadsheet_id, $table[$i], $interval[$i]);
                    if ($tables[$table[$i]] === false){
                        response::client_error(400, "Il foglio {$table[$i]} non è impostato correttamente");
                    }
                }

                // Accedere ai valori tabelle
                // $tables ['nometabella' (string)] ['indice_riga' (int)] ['indice_colonna' (int)]
	
                // X testing (mostra anteprima tabelle)
                foreach ($tables as $key => $value){
                    echo $key . "<br>---------------------------------------------------------<br>";
                    print_table($value);
                    echo "<br>---------------------------------------------------------<br>";
                }
            }

            break;
        }

        case 'POST': {

            break;
        }

        default: {

            // Metodo non autorizzato
            response::client_error(405);
            break;
        }
    }
    

?>