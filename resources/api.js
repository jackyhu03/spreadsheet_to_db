
class page {

    // mostra i checkbox per poter selezionare le tabelle
    static showTablesCheckBox(table, tableNames){

        // Per ogni oggetto { [CHECKED] [NOME_TABELLA] [INTERVALLO] }
        // L'ID viene salvato in questo modo:
        // tr_[nometabella] { ...   ...    ...}
        // Dato che in fogli di google ogni foglio deve avere un nome univoco posso usarlo come id
        let h = "";
        tableNames.forEach(element => {
            h += "<tr id='NODE_"+element+"'>";
            h += "<td><input name='CHECKED' type='checkbox' checked=true value=true></td><td name='TITLE'>"+element+"</td><td><input type='text' placeholder='INTERVAL' name='INTERVAL'></td>";
            h += "</tr>";
        })
        h += "</table>";
        table.html(h);
        table.css('display', 'block');
    }
}