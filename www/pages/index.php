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
            response::client_error(400, "Invalid URL");
        };

        $google_oauth = new Google_Service_Oauth2($client);
        $google_account_info = $google_oauth->userinfo->get();
        $email =  $google_account_info->email;
        $name =  $google_account_info->name;
        $data = array($email, $name);
        $access_token = $token['access_token'];
        
        if (isset($_COOKIE["gs_id_403"]))
        {
            if (GoogleAPI::spreadsheet_permission($_COOKIE["gs_id_403"]) === false)
            {
                echo "<h1>Non autorizzato...</h1><br>";
                echo "<h3>L'email $email non ha accesso ai dati richiesti</h3>";
                echo "<span> clicca <a href='{$_SERVER['PHP_SELF']}'>qui</a> per tornare alla home page</span>";
                setcookie("gs_id_403", false, time()-3600, "/", "gitpod.io");
                exit;
            }
        }

        setcookie("atkn", $access_token, time()+3600, "/", "gitpod.io");
        echo "<h1>Ti sei autenticato</h1>";
        echo "<span> clicca <a href='{$_SERVER['PHP_SELF']}'>qui</a> per tornare alla home page</span>";
        exit;
    }

?>


<!DOCTYPE html>
<html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Spreadsheet To SQL</title>
    </head>

    <body>
        <h1 style="text-align: center;">Spreadsheet to SQL</h1>

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
        </div>

        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
        <script src="../resources/api.js"></script>
    </body>

    <script>
        
        const delay = (ms) => new Promise(resolve => setTimeout(resolve, ms));

        $(document).ready(() => {
            //$('#spreadsheet_url').val(localStorage.getItem("url_403"));   
            //localStorage.removeItem("url_403");    
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
            $('#FINAL_BOX').css("display", "none");

            $.ajax({
                type: 'GET',
                url: '../resources/server.php',
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
                        document.body.innerHTML = "<h2>Per effettuare questa richiesta e' necessario accedere con un account Google</h2>";
                        document.body.innerHTML += "<span><a href='<?php echo GoogleClient::get_object()->createAuthUrl(); ?>'>clicca qui</a> per accedere</span>";
                    }
                    else
                        document.body.innerHTML = JSON.stringify(xhr.responseJSON);
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
                url: '../resources/server.php',
                data: getParameters(), // ottiene nomi tabelle ed eventuali intervalli selezionati
                success: (response) => {
                    $('#loading').css("display", "none");
                    // per ogni tabella vengono mostrati i dati, ritornati dalla richiesta
                    const tables_b64 = response.tables_b64;
                    const tables = JSON.parse(atob(tables_b64));
                    buffer['checksheet_names'] = []; 
                    buffer['TABLES_B64'] = response.tables_b64;
                    buffer.spreadsheet_names.forEach(tableName => {
                        if (tables[tableName] !== undefined){
                            // tables[tableName][index_riga][index_colonna]
                            // tables[tableName][0][...] => NOMI COLONNE
                            // tables[tableName][1->n][...] => Righe effettive tabella
                            buffer['checksheet_names'].push(tableName);
                            page.showTable(tables[tableName]);
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

        const f = () => {
            $.ajax({
                type: 'GET',
                url: '../resources/server.php',
                data: {"SHEET_NAMES": buffer['checksheet_names'], "TABLES_B64": buffer['TABLES_B64']},
                success: (response) => {
                    document.body.innerHTML += response;
                },
                error: (xhr) => {

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
                const trNode = $("#NODE_"+element).prop('childNodes');
                // [0] => nodo figlio che rappresenta la check box, controllo se è spuntato
                if (trNode[0].childNodes[0].checked){
                    parameters['TABLE'+i] = trNode[1].childNodes[0].textContent;
                    parameters['INTERVAL'+i] = trNode[2].childNodes[0].value;
                    i++;
                }
            });

            return parameters;
        };

        const getTablesB64 = () => {



        };

    </script>

</html>

<style>

    @import url('https://fonts.googleapis.com/css2?family=Quicksand:wght@300;400&display=swap');

    body {
        font-family: 'Quicksand', sans-serif;
        justify-content: center;
        position: relative;
        min-height: 100vh;
    }
    
    .finalBox {

        width: 90%;
        margin-left: auto;
        margin-right: auto;
        margin-bottom: 100px;
    }

    .searchBox {
        font-family: 'Quicksand', sans-serif;
        width: 100%;
        text-align: center;
        font-size: 20px;
        margin: 10px;
        height: 50px;
        border-radius: 25px;
        outline: none;
        border: none;
        transition: .5s;
    }

    .showAnt {
        color: white;
        transition: .7s;
        font-size: 2.8rem;
        text-align: center;
        border-radius: 25px;
        color: rgb(0, 255, 119);
        font-weight: 100;
    }

    .showAnt:hover {
        transform: scale(1.2);
        cursor: pointer;
    }

    .midTable {
        padding: 80px;
        margin-left: auto;
        margin-right: auto;
        width: 80%;
    }

    .btnSearchBox {
        font-family: 'Quicksand', sans-serif;
        width: auto;
        text-align: center;
        font-size: 20px;
        margin-left: auto;
        margin-right: auto;
        width: 400px;
        height: 50px;
        text-align: center;
        margin: 10px;
        border-radius: 25px;
        outline: none;
        border: none;
        position: absolute;
        left: 50%;
        -ms-transform: translate(-50%, 0);
        transform: translate(-50%, 0);
    }

    .checkBoxClass {

        width: 30px !important;
        height: 30px !important;
        margin: 5px;
        outline: none;
        filter: hue-rotate(30deg);
        box-shadow: none;
        font-size: 2em;
    }

    .checkBoxClass:hover {
        cursor: pointer;
    }

    .intervalClass {

        font-family: 'Quicksand', sans-serif;
        background-color: #2f2f2f;
        outline: none;
        border: none;
        color: white;
    }

    .btnSend {

        font-family: 'Quicksand', sans-serif;
        width: auto;
        text-align: center;
        font-size: 20px;
        width: 50%;
        height: 50px;
        text-align: center;
        margin: 10px;
        font-size: 3rem;
        border-radius: 25px;
        outline: none;
        border: none;
        color: black;
    }

    .midBox {

        border: 1px solid black;
        border-radius: 25px;
        width: 80%;
        height: 60%;
        margin-left: auto;
        margin-right: auto;
        margin-bottom: 200px;    
        padding: 80px;   
    }   

    ::selection {
        background-color: rgb(60,60,60);
        color: white;
    }

    .sf {
        width: 80%;
        margin-left: auto;
        margin-right: auto;
        margin-bottom: 150px;
        margin-top: 50px;
    }

    p, h2 {
        font-size: 25px;
        text-align: center;
    }

    table {
        margin-left: auto;
        margin-right: auto;
        width: 100%;
    }

    td, tr, button {
        margin-left: auto;
        margin-right: auto;
        width: 50%;
    }

    button:hover {
        cursor: pointer;
    }


    h1{
        font-size: 30px;
        color: #fff;
        text-transform: uppercase;
        font-weight: 300;
        text-align: center;
        margin-bottom: 15px;
    }

    table{
        width:100%;
        table-layout: fixed;
    }

    .tbl-header{
        margin-top: 50px;
        background-color: rgba(255,255,255,0.3);
        width:100%;
    }

    .tbl-content{
        margin-bottom: 50px;
        height:300px;
        overflow-x:auto;
        margin-top: 0px;
        border: 1px solid rgba(255,255,255,0.3);
        width:100%;
    }

    th {
        padding: 20px 15px;
        text-align: left;
        font-weight: 500;
        font-size: 12px;
        color: #fff;
        text-transform: uppercase;
    }

    td {
        padding: 15px;
        text-align: left;
        vertical-align:middle;
        font-weight: 300;
        font-size: 12px;
        color: #fff;
        border-bottom: solid 1px rgba(255,255,255,0.1);
    }

    body {
        background: -webkit-linear-gradient(left, #25c481, #25b7c4);
        background: linear-gradient(to right, #25c481, #25b7c4);
    }
    section {
        margin: 50px;
    }

    .made-with-love {
        margin-top: 40px;
        padding: 10px;
        clear: left;
        text-align: center;
        font-size: 10px;
        font-family: arial;
        color: #fff;
    }

    .made-with-love i {
        font-style: normal;
        color: #F50057;
        font-size: 14px;
        position: relative;
        top: 2px;
    }
    .made-with-love a {
        color: #fff;
        text-decoration: none;
    }
    .made-with-love a:hover {
        text-decoration: underline;
    }

    ::-webkit-scrollbar {
        idth: 6px;
    } 
    ::-webkit-scrollbar-track {
        -webkit-box-shadow: inset 0 0 6px rgba(0,0,0,0.3); 
    } 
    ::-webkit-scrollbar-thumb {
        -webkit-box-shadow: inset 0 0 6px rgba(0,0,0,0.3); 
    }

    body {
        background: #2f2f2f;
    }

    .lds-ring {
        display: inline-block;
        position: relative;
        width: 80px;
        height: 80px;
        margin-left: auto;
        margin-right: auto;
        margin-top: 30px;
        margin-bottom: 30px;
    }
    .lds-ring div {
        box-sizing: border-box;
        display: block;
        position: absolute;
        width: 64px;
        height: 64px;
        margin: 8px;
        border: 8px solid #fff;
        border-radius: 50%;
        animation: lds-ring 1.2s cubic-bezier(0.5, 0, 0.5, 1) infinite;
        border-color: #fff transparent transparent transparent;
    }
    .lds-ring div:nth-child(1) {
        animation-delay: -0.45s;
    }
    .lds-ring div:nth-child(2) {
        animation-delay: -0.3s;
    }
    .lds-ring div:nth-child(3) {
        animation-delay: -0.15s;
    }

    @keyframes lds-ring {
        0% {
            transform: rotate(0deg);
        }
        100% {
            transform: rotate(360deg);
        }
    }


</style>
