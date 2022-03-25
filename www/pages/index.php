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
        <form class="sf">
            <input style="width:100%;text-align:center;font-size:20px;" id="spreadsheet_url" type="text"  placeholder="SPREADSHEET URL">
            <input style="width:AUTO;text-align:center;font-size:20px;" id="BTN" type='button' value='=>'>
        </form>

        <table id="TBL0" style="display:none"></table>
        <input id="BTN1" type="button" value='SEND' style="display:none"></table>
        <p id="pino"></p>
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
        <script src="resources/api.js"></script>
    </body>

    <script>
        
        // variabile globale per tenere in memoria URL, nomi tabelle...
        var buffer = {};

        // Dopo aver inserito URL foglio google premi invia e viene eseguita questa:
        $('#BTN').on('click', () => {
            
            let value = $('#spreadsheet_url').val();

            if (value === '') 
            { 
                alert("Required field");
                return;
            }

            $.ajax({
                type: 'GET',
                url: './server.php',
                data: { spreadsheet_url: $('#spreadsheet_url').val() },
                success: (response) => {
                    
                    // mostra grafica => [CHECKBOX] [NOME_TABELLA] [INTERVALLO]
                    page.showTablesCheckBox($('#TBL0'), response.spreadsheet_names);
                    
                    // Variabile globale buffer: salvo url foglio google
                    buffer["spreadsheet_url"] = value;
                    // Salvo nomi tabelle ritornati dalla richiesta
                    buffer["spreadsheet_names"] = response.spreadsheet_names;
                    // Il button #BTN1 adesso è visibile
                    $('#BTN1').css('display', 'block');
                },
                error: (xhr) => {
                    document.body.innerHTML = JSON.stringify(xhr.responseJSON);
                }
            });

        });

        // Dopo aver confermato i titoli tabelle, onclick sul pulsante e come
        // risposta i dati delle tabelle richieste
        // per adesso visualizza dati tabelle solo in console
        $('#BTN1').on('click', () => {

            $.ajax({
                type: 'GET',
                url: './server.php',
                data: getParameters(), // ottiene nomi tabelle ed eventuali intervalli selezionati
                success: (response) => {
                    // per ogni tabella vengono mostrati i dati, ritornati dalla richiesta
                    const tables = response.tables;
                    $('#TBL0').css('display', 'none');
                    $('#BTN1').css('display', 'none');
                    buffer.spreadsheet_names.forEach(tableName => {
                        if (tables[tableName] !== undefined){
                            
                            // tables[tableName][index_riga][index_colonna]
                            // tables[tableName][0][...] => NOMI COLONNE
                            // tables[tableName][1->n][...] => Righe effettive tabella
                            console.log(tables[tableName]);
                            page.showTable(tables[tableName], document);
                            //console.log(tables[tableName]);
                        }
                    });
                },
                error: (xhr) => {
                    document.body.innerHTML = JSON.stringify(xhr.responseJSON);
                }
            });
        });

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
        }

    </script>

</html>

<style>




    .sf {
        border: 0px solid black; 
        width: 80%;
        margin-left: auto;
        margin-right: auto;
    }

    p, h2 {
        font-size: 25px;
        text-align: center;
    }

    table {
        margin-left: auto;
        margin-right: auto;
        border: 2px solid black;
        width: 20%;
    }

    td, tr, button {
        margin-left: auto;
        margin-right: auto;
        width: 200px;
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
  background-color: rgba(255,255,255,0.3);
 }
.tbl-content{
  height:300px;
  overflow-x:auto;
  margin-top: 0px;
  border: 1px solid rgba(255,255,255,0.3);
}
th{
  padding: 20px 15px;
  text-align: left;
  font-weight: 500;
  font-size: 12px;
  color: #fff;
  text-transform: uppercase;
}
td{
  padding: 15px;
  text-align: left;
  vertical-align:middle;
  font-weight: 300;
  font-size: 12px;
  color: #fff;
  border-bottom: solid 1px rgba(255,255,255,0.1);
}


/* demo styles */

@import url(https://fonts.googleapis.com/css?family=Roboto:400,500,300,700);
body{
  background: -webkit-linear-gradient(left, #25c481, #25b7c4);
  background: linear-gradient(to right, #25c481, #25b7c4);
  font-family: 'Roboto', sans-serif;
}
section{
  margin: 50px;
}


/* follow me template */
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


/* for custom scrollbar for webkit browser*/

::-webkit-scrollbar {
    width: 6px;
} 
::-webkit-scrollbar-track {
    -webkit-box-shadow: inset 0 0 6px rgba(0,0,0,0.3); 
} 
::-webkit-scrollbar-thumb {
    -webkit-box-shadow: inset 0 0 6px rgba(0,0,0,0.3); 
}

</style>
