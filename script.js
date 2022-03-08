$('#BTN').on('click', () => {
    req ('GET', './spreadtodb.php', { spreadsheet_url: $('#spreadsheet_url').val()} );
});

const req = (method, url, data) => {

    $.ajax({
        type: method,
        url: url,
        data: data,
        success: (response) => {
            console.log(response);
        },
        error: (xhr) => {
            console.log(xhr);
    }

});
}