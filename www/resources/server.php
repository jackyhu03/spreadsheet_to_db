<?php

    require_once 'googleTools.php';
    require_once 'class.response.php';
    require_once 'tokens.php';

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

                $table_ctx = "";

                foreach (array_combine($table_names, $intervals) as $table_name => $interval){

                    // Get table (array[][])

                    $tables[$table_name] = $table = GoogleAPI::get_spreadsheet($spreadsheet_id, $table_name, $interval);

                    if ($table === false){
                        response::client_error(400, "Il foglio {$table_name} non e' impostato correttamente");
                    }

                    $types = SQLTypes::__GET__($tables[$table_name]);

                    for ($i=0; $i<count($tables[$table_name][0]); $i++)
                        $tables[$table_name][0][$i]['type'] = $types[$i];
                }

                $table_ctx .= json_encode($tables);

                $sql_b64 = base64_encode($sql_ctx);
                $tables_b64 = base64_encode($table_ctx);

                response::successful(200, false, array("tables_b64" => $tables_b64));
                exit;
            }

            else if (isset($_REQUEST['filename']) && count($_GET) === 1){
                response::download_file($_REQUEST['filename'], false);
                chdir("../");
                rmdir(hash("sha256", $_REQUEST['filename']));
                exit;
            }

            break;
        }

        case 'POST': {

            // ---> Request for download SQL file
            if (isset($_REQUEST['SHEET_NAMES']) && isset($_REQUEST['DB_NAME']) && isset($_REQUEST['FILENAME']) && isset($_REQUEST['TABLES_B64'])){

                $tables = json_decode(base64_decode($_REQUEST['TABLES_B64']), true);
                $names = json_decode($_REQUEST['SHEET_NAMES'], true);
                $db_name = $_REQUEST['DB_NAME'];
                $filename = $_REQUEST['FILENAME'] . ".sql";


                $sql_ctx = sqlc::get_sql_ctx($tables, $names, $db_name);

                $rep = hash("sha256", $filename);
                chdir("temp_sql_files");
                mkdir($rep);
                chdir($rep);
                file_put_contents($filename, $sql_ctx);
                response::successful(200, false, array("filename" => $filename));
            }

            break;
        }

        default: {

            // Method not allowed
            response::client_error(405);
            break;
        }
    }
