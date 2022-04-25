<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="stylesheet" href="www/css/index.css">
        <link rel="stylesheet" href="www/css/doc.css">
        <title>GS2DB-Documentation</title>
    </head>
    </body>
        <ul id="ul-nb">
          <li><a style='color:var(--dc);text-decoration-line: none;' href="service">service</a></li>
          <li><a style='color:var(--dc);text-decoration-line: none;' href="documentation">documentation</a></li>
        </ul>
        <br><br>
        <h1 class='TN' style="text-align:center;">GS2DB-Documentation</h1>
        <div class='doc-body'>
            <p class='parGen'>Il servizio permette la conversione da fogli di Google a SQL</p>
            <h1 class="titleGen">Come si utilizza</h1>
                <p class='parGen'>
                    L'applicazione rileva in automatico le tabelle se all'interno dei fogli selezionati<br>
                    non sono presenti celle compilate esterne alla tabella.
                    <br><br><br>Formato ideale<br></p>
                    <center><img class='myImg' src='https://mywebs.altervista.org/GS2DB/www/resources/img/G201.JPG'></center>
                    <br><br>
                    <p class='parGen'>Caso alternativo<br></p>
                    <center><img class='myImg' src='https://mywebs.altervista.org/GS2DB/www/resources/img/G202.JPG'></center>
                    <p class='parGen'>In questo caso va specificato<br>
                    il range a cui appartiene la tabella, se questa regola non viene rispettata verra' rispetta<br>
                    i dati non saranno corretti.</p>
            <h1 class="titleGen">Autenticazione</h1>
                <p class='parGen'>
                    L'accesso ai dati non richiede autorizzazioni se il foglio di Google selezionato<br>
                    non e' privato.<br>Se il foglio di Google e' privato sara' richiesto l'accesso tramite Google.
                    <br>La durata di una sessione e' di un ora, dopo di che sara' necessario rieffettuare il login.
                </p>
            <h1 class="titleGen">Tipi di dati</h1>
                <p class='parGen'>
                    Il servizio e' in grado di riconoscere i tipi di dati SQL, se una colonna contiene<br>
                    tipi di dati differenti verranno impostati come VARCHAR, la cui lunghezza sara' determinata<br>
                    dall'attributo maggiore.
                </p>
            <h1 class="titleGen">Chiva primaria</h1>
                <p class='parGen'>
                    Le chiavi primarie vengono riconosciute se una colonna presenta dei dati univoci.<br>
                    Se piu' colonne presentano dati univoci verra' selezionata la prima colonna partendo<br>
                    da sinistra.<br> Nel caso la chiave primaria venga impostata in un campo non desiderato, e'<br>
                    possibile cambiarla tramite la input box, nella preview globale.
                </p>
        </div>

    <body>
</html>
