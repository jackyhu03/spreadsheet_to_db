
const ajaxReq = (method, url, data) => {

    $.ajax({
        type: method,
        url: url,
        data: data,
        success: (response) => {
			
			// Show table names
            {
				console.log(response);
				var i = 1;
				document.body.innerHTML += "<br><br><h2>TABELLE TROVATE</h2>";
				response.spreadsheet_names.forEach(element => {
					document.body.innerHTML += " <p>["+element+"]</p>";
				});
			}
			
        },
        error: (xhr) => {
            console.log(xhr);
        }
    });
}