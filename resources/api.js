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
    static showTables(table){
      let h = "";
      h += '<div class="tbl-header"><table cellpadding="0" cellspacing="0" border="0"><thead><tr>';
      for(let j = 0; j < table[0].length; j++) {
        h += "<th>"+table[0][j]+"</th>";
      }
      h += '</tr></thead></table></div><div class="tbl-content"><table cellpadding="0" cellspacing="0" border="0"><tbody>';
      var i=1;
      for(let i = 1; i < table.length; i++) {
        h += "<tr>";
        for(let j = 0; j < table[i].length; j++) {
          h += "<td>"+table[i][j]+"</td>";
          console.log(table[i][j]);
        }
        h += "</tr>";
      }
      h += '</tbody></table></div>';
      table.html(h);
      table.css('display', 'block');
    }
}
