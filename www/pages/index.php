<?php

    require_once "../resources/class.response.php";
    require_once '../resources/googleTools.php';
    require_once '../resources/OAuth/google/vendor/autoload.php';
	
    if (isset($_GET['code']))
    {
        $client = GoogleClient::get_object();
        $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);
        
        try {
            $client->setAccessToken($token['access_token']);
        } catch (Exception $e){
            http_response_code(400);
            echo "<span>URL non valido, <a href='home'>clicca qui</a> per tornare alla home page</span>";
            exit;
        };

        $google_oauth = new Google_Service_Oauth2($client);
        $google_account_info = $google_oauth->userinfo->get();
        $email =  $google_account_info->email;
        $name =  $google_account_info->name;
        $data = array($email, $name);
        $access_token = $token['access_token'];
        setcookie("atkn", $access_token, time()+3600, "/");
        
        if (isset($_COOKIE["gs_id_403"]))
        {
            if (GoogleAPI::spreadsheet_permission($_COOKIE["gs_id_403"], $access_token) === false)
            {
                echo "<h1>Non autorizzato...</h1><br>";
                echo "<h3>L'email $email non ha accesso ai dati richiesti</h3>";
                echo "<span> clicca <a href='home'>qui</a> per tornare alla home page</span>";
                setcookie("gs_id_403", false, time()-3600, "/");
                exit;
            }else{
                echo "<h1>Ti sei autenticato</h1>";
                echo "<span> clicca <a href='home'>qui</a> per tornare alla home page</span>";
                exit;
            }
        }else{
            echo "<h1>Accesso effettuato</h1>";
            echo "<span> clicca <a href='home'>qui</a> per tornare alla home page</span>";
            exit;
        }

    }

?>

<!DOCTYPE html>
<html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="stylesheet" href="www/css/index.css">
        <title> - GS2DB Service - </title>
    </head>

    <body>
        <br><br>
        <h1 style="text-align:center;">Google Spreadsheet &#8921; DB</h1>

        <div class="sf" id="LOW_BOX">
            <input class="searchBox" id="spreadsheet_url" type="text" placeholder="Google spreadsheet URL">
            <button class="btnSearchBox" id="BTN">SEARCH</button>
        </div>

        <div id="loading" class="lds-ring" style="display:none">
            <div></div><div></div><div></div><div></div>
        </div>

        <div class="midBox" id="MID_BOX" style="display:none">
            <p style="color: white;font-size:3rem">Tabelle trovate</p>
            <table id="TBL0" class="midTable"></table>
            <p class="showAnt" id="BTN1">Mostra anteprima</p>
        </div>

        <div class="finalBox" id="FINAL_BOX" style="display:none">
            <button></button>
        </div>

        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
        <script src="www/resources/api.js"></script>
    </body>

    <script>
        
        const delay = (ms) => new Promise(resolve => setTimeout(resolve, ms));

        $(document).ready(() => {
            $('#spreadsheet_url').val(localStorage.getItem("url_403"));   
            localStorage.removeItem("url_403");    
        });

        // variabile globale per tenere in memoria URL, nomi tabelle...
        var buffer = {};

        // Dopo aver inserito URL foglio google premi invia e viene eseguita questa:
        $('#BTN').on('click', () => {
            let value = $('#spreadsheet_url').val();

            if (value === '') 
            { 
                $('#spreadsheet_url').css("background-color", "rgb(255, 137, 137)");
                delay(500).then(()=>$('#spreadsheet_url').css("background-color", "white"));
                return;
            }

            $('#MID_BOX').css("display", "none");
            $('#loading').css("display", "block");
            document.getElementById('FINAL_BOX').innerHTML = "";
            $('#FINAL_BOX').css("display", "none");

            $.ajax({
                type: 'GET',
                url: 'www/resources/server.php',
                data: {spreadsheet_url: value},
                success: (response) => {
                    // mostra grafica => [CHECKBOX] [NOME_TABELLA] [INTERVALLO]
                    $('#loading').css("display", "none");
                    page.showTablesCheckBox($('#TBL0'), response.spreadsheet_names);
                    
                    // Variabile globale buffer: salvo url foglio google
                    buffer["spreadsheet_url"] = value;
                    // Salvo nomi tabelle ritornati dalla richiesta
                    buffer["spreadsheet_names"] = response.spreadsheet_names;
                    // Il button #BTN1 adesso è visibile
                    $("#MID_BOX").css('display', 'block');
                },
                error: (xhr) => {
                    if (xhr.responseJSON.status_message === "PERMISSION_DENIED" && xhr.responseJSON.status_code === 403)
                    {
                        localStorage.setItem("url_403", $('#spreadsheet_url').val());
                        document.body.innerHTML = "<h2>Il foglio di Google a cui stai tentando di accedere e' protetto</h2>";
                        document.body.innerHTML += "<span><a href='<?php echo GoogleClient::get_object()->createAuthUrl(); ?>'>Accedi con google</a></span>";
                    }
                    else
                        alert(JSON.stringify(xhr.responseJSON));
                }
            });

        });

        // Dopo aver confermato i titoli tabelle, onclick sul pulsante e come
        // risposta i dati delle tabelle richieste
        // per adesso visualizza dati tabelle solo in console
        $('#BTN1').on('click', () => {

            $('#MID_BOX').css("display", "none");
            $('#loading').css("display", "block");

            $.ajax({
                type: 'GET',
                url: 'www/resources/server.php',
                data: getParameters(), // ottiene nomi tabelle ed eventuali intervalli selezionati
                success: (response) => {
                    $('#loading').css("display", "none");
                    // per ogni tabella vengono mostrati i dati, ritornati dalla richiesta
                    const tables_b64 = response.tables_b64;
                    const tables = JSON.parse(atob(tables_b64));
                    buffer['checksheet_names'] = []; 
                    buffer['TABLES_B64'] = response.tables_b64;
                    document.getElementById('FINAL_BOX').innerHTML += "<button class='downloadBtn' onclick='downloadInit()' id='DOWNLOAD_INIT'>Download SQL</button>";
                    buffer.spreadsheet_names.forEach(tableName => {
                        if (tables[tableName] !== undefined){
                            // tables[tableName][index_riga][index_colonna]
                            // tables[tableName][0][...] => NOMI COLONNE
                            // tables[tableName][1->n][...] => Righe effettive tabella
                            buffer['checksheet_names'].push(tableName);
                            page.showTable(tables[tableName], tableName);
                            //console.log(tables[tableName]);
                        }
                    });
                    $('#FINAL_BOX').css('display', 'block');
                },
                error: (xhr) => {
                    document.body.innerHTML = JSON.stringify(xhr.responseJSON);
                }
            });
        });

        const downloadInit = () => {

            var tables = atob(buffer['TABLES_B64']);
            tables = JSON.parse(tables);

            for (let i=0; i<buffer['checksheet_names'].length; i++){
                for (let j=0; j<tables[buffer['checksheet_names'][i]][0].length; j++){
                    tables[buffer['checksheet_names'][i]][0][j]['type'] = document.getElementById("ID_"+buffer['checksheet_names'][i]+"_"+tables[buffer['checksheet_names'][i]][0][j]['value'].replace(" ", "+")+"_TYPE").value;
                }
            }
            
            buffer['TABLES_B64'] = btoa(JSON.stringify(tables));
            
            let db_name = window.prompt("Inserire nome database: ");
            let filename = window.prompt("Inserire nome file (*.sql): ");

            db_name = db_name == "" || db_name.length === 0 ? "GS2DB_db" : db_name;
            filename = filename == "" || filename.length === 0 ? "GS2DB_db" : filename;

            const url = "www/resources/server.php?SHEET_NAMES="+JSON.stringify(buffer['checksheet_names'])+"&TABLES_B64="+buffer['TABLES_B64']+"&DB_NAME="+db_name+"&FILENAME="+filename;
            window.location = url;
        };

        const getParameters = () => {
            // [TABLE1] [INTERVAL1]
            // [TABLE2] [INTERVAL2]
            // ........ ...........
            // [TABLEN] [INTERVALN]

            // Dalla variabile globale ottengo URL foglio google
            var parameters = {spreadsheet_url: buffer.spreadsheet_url};
            let i = 1;

            // Se la tabella è stata spuntata, aggiungo a 'parameters{}' il nome della tabella ed eventuale intervallo
            // L'intervallo è facoltativo (in base a impostazione foglio google utente) 
            buffer.spreadsheet_names.forEach(element => {
                const trNode = $("#NODE_"+element.replace(" ", "+")).prop('childNodes');
                // [0] => nodo figlio che rappresenta la check box, controllo se è spuntato
                if (trNode[0].childNodes[0].checked){
                    parameters['TABLE'+i] = trNode[1].childNodes[0].textContent;
                    parameters['INTERVAL'+i] = trNode[2].childNodes[0].value;
                    i++;
                }
            });

            return parameters;
        };

    </script>

</html>