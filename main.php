<!DOCTYPE html>
<html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Document</title>
    </head>

    <body>
        <div>
            <input id="ins-link" type="text" id="spreadsheet_url" placeholder="googlesheet url" required>
            <button id="BTN" >manda link</button>
        </div>
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    </body>

    <script>

        $('#BTN').on('click', () => {
            alert("");
            alert( $('#spreadsheet_url').html() );
            req ( $('#spreadsheet_url').html() );
        });

        const req = (data) => {

            $.ajax({
                type: 'GET',
                url: './spreadtodb.php',
                data: data,
                success: (response) => {
                    console.log(response);
                },
                error: (xhr) => {
                    console.log(xhr);
            }

        });
        }

    </script>

</html>



