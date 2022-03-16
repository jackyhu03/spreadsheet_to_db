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
        <br/><br/>

        <div class="sf">
            <input style="width:100%;text-align:center;font-size:20px;" id="spreadsheet_url" type="text"  placeholder="SPREADSHEET URL" required>
            <br><br>
            <center><button style="width:20%;text-align:center;font-size:20px;"id="BTN" >SEND</button></center>
        </div>

        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
        <script src="https://mywebs.altervista.org/spreadsheet_to_db/resources/api.js"></script>
    </body>

    <script>

        $('#BTN').on('click', () => {
            ajaxReq('GET', './server.php', {spreadsheet_url: $('#spreadsheet_url').val()} );
        });

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

</style>



