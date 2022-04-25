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
            init_pg();
            set_bg();
            echo "<span style='font-size:2rem;color:white;'>Invalid URL, <a style='color:aqua' href='service'>click here</a> to continue</span>";
            exit;
        };

        $google_oauth = new Google_Service_Oauth2($client);
        $google_account_info = $google_oauth->userinfo->get();
        $email =  $google_account_info->email;
        $name =  $google_account_info->name;
        $data = array($email, $name);

        $access_token = $token['access_token'];
        session_start();
        $_SESSION['ATKN'] = $access_token;

        if (isset($_SESSION["GS_ID"]))
        {
            if (GoogleAPI::spreadsheet_permission($_SESSION["GS_ID"], $access_token) === false)
            {
                unset($_SESSION["GS_ID"]);
                init_pg();
                print_form_NOAUTH($email);
                exit;

            }else{
                print_form_AUTH($email);
                init_pg();
                exit;
            }
        }else{
            print_form_access();
            init_pg();
            exit;
        }
    }

    function print_form_AUTH($email){
        echo "<span style='font-size:2rem;color:white;'>Authenticated, the email <a style='color:aqua;'>$email</a> allows access to the requested data</span>";
        echo "<br><br><span style='font-size:2rem;color:white;'>Click <a style='color:aqua;' href='service'>here</a> to continue</span>";
    }

    function print_form_access(){
        echo "<h1 style='font-size:2rem;color:white;'>Logged</h1>";
        echo "<br><span style='font-size:2rem;color:white;'> Click <a style='color:aqua;' href='service'>qui</a> to continue</span>";
    }

    function print_form_NOAUTH($email){
        echo "<br><h1 style='font-size:2rem;color:white;'>Unauthorized...</h1><br>";
        echo "<br><span style='font-size:2rem;color:white;'>The email <a style='color:aqua;'>$email</a> does not have access to the requested data</span>";
        echo "<br><br><span style='font-size:2rem;color:white;'> Click <a style='color:aqua;' href='service'>here</a> to continue</span>";
    }

    function set_bg(){
        echo "<style>@import url('https://fonts.googleapis.com/css2?family=Quicksand:wght@300;400&display=swap');body{background-color:#2F2F2F;font-family:'Quicksand', sans-serif;}</style>";
    }

    function init_pg(){
        echo "<head><meta charset='UTF-8'><meta http-equiv='X-UA-Compatible' content='IE=edge'><meta name='viewport' content='width=device-width, initial-scale=1.0'><link rel='stylesheet' href='www/css/index.css'><title>GS2DB-Service</title></head>";
    }

?>

<!DOCTYPE html>
<html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="stylesheet" href="www/css/index.css">
        <title>GS2DB-Service</title>
    </head>

    <body>
        <ul id="ul-nb">
          <li><a style='color:var(--dc);text-decoration-line: none;' href="service">service</a></li>
          <li><a style='color:var(--dc);text-decoration-line: none;' href="documentation">documentation</a></li>
        </ul>
        <br><br>
        <h1 class='TN' style="text-align:center;">GS2DB-Service</h1>

        <div class="sf" id="LOW_BOX">
            <input class="searchBox" id="spreadsheet_url" type="text" placeholder="Google spreadsheet URL">
            <button class="btnSearchBox" id="BTN">SEARCH</button>
        </div>

        <div id="loading" class="lds-ring" style="display:none">
            <div></div><div></div><div></div><div></div>
        </div>

        <div class="midBox" id="MID_BOX" style="display:none">
            <p style="color: white;font-size:3rem">Found Tables</p>
            <table id="TBL0" class="midTable"></table>
            <p class="showAnt" id="BTN1">Show Preview</p>
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
                    $('#loading').css("display", "none");

                    if (xhr.responseJSON.status_message === "PERMISSION_DENIED" && xhr.responseJSON.status_code === 403)
                    {
                        localStorage.setItem("url_403", $('#spreadsheet_url').val());
                        document.body.innerHTML = "<h2 style='font-size:2rem;color:white;'>You are trying to access a private spreadsheet</h2>";
                        document.body.innerHTML += "<center><a style='font-size:2rem;color:aqua' href='<?php echo GoogleClient::get_object()->createAuthUrl(); ?>'>Login with Google</a></center>";
                    }
                    else{
                        setTimeout(() => alert(JSON.stringify(xhr.responseJSON)),100);
                    }
                }
            });

        });

        // Dopo aver confermato i titoli tabelle, onclick sul pulsante e come
        // risposta i dati delle tabelle richieste
        // per adesso visualizza dati tabelle solo in console
        $('#BTN1').on('click', () => {

            const par = getParameters();
            if (Object.keys(par).length <= 1){
                alert("Select at least one sheet");
                return;
            }

            $('#MID_BOX').css("display", "none");
            $('#loading').css("display", "block");

            $.ajax({
                type: 'GET',
                url: 'www/resources/server.php',
                data: par, // ottiene nomi tabelle ed eventuali intervalli selezionati
                success: (response) => {
                    $('#loading').css("display", "none");
                    // per ogni tabella vengono mostrati i dati, ritornati dalla richiesta
                    //console.log(response);return;
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
                    console.log(xhr);
                    document.body.innerHTML = "<span style='color:white;font-size:2rem;'>Error in reading the sheets, click <a style='color:aqua;' href='service'>here</a> to continue</span>";
                    window.scrollTo(0, 0);
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

            let db_name = window.prompt("Enter Database name: ");
            let filename = window.prompt("Enter file name (*.sql): ");

            db_name = db_name == "" || db_name.length === 0 ? "GS2DB_db" : db_name;
            filename = filename == "" || filename.length === 0 ? "GS2DB_db" : filename;

            const url = "www/resources/server.php?SHEET_NAMES="+JSON.stringify(buffer['checksheet_names'])+"&DB_NAME="+db_name+"&FILENAME="+filename;

            $.ajax({
                type: 'POST',
                url: url,
                data: {"TABLES_B64": buffer['TABLES_B64']},
                success: (response) => {
                    //console.log(response);
                    window.location = "www/resources/server.php?filename="+response.filename;
                },
                error: (xhr) => {
                    window.scrollTo(0, 0);
                    console.log(xhr);
                    document.body.innerHTML = "<span style='color:white;font-size:2rem;'>Error in download your data, click <a style='color:aqua;' href='service'>here</a> to continue</span>";
                }
            });

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
