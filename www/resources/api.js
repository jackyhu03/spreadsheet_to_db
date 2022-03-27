class page {

    // mostra i checkbox per poter selezionare le tabelle
    static showTablesCheckBox(table, tableNames){

        // Per ogni oggetto { [CHECKED] [NOME_TABELLA] [INTERVALLO] }
        // L'ID viene salvato in questo modo:
        // tr_[nometabella] { ...   ...    ...}
        // Dato che in fogli di google ogni foglio deve avere un nome univoco posso usarlo come id
        let h = "<tr><th style='font-size:1.3rem'>Visualizza</th><th style='font-size:1.3rem'>Nome tabella</th><th style='font-size:1.3rem'>Intervallo</th><tr>";
        tableNames.forEach(element => {
            h += "<tr id='NODE_"+element+"'>";
            
            h += "<td><input name='CHECKED' class='checkBoxClass' type='checkbox' checked=true value=true></td><td name='TITLE'>"+element+"</td><td><input class='intervalClass' style='text-align:center' type='text' placeholder='A1:B2' maxlength=name='INTERVAL'></td>";
            h += "</tr>";
        })
        h += "</table>";
        table.html(h);
    }

    static showTable(table){

      document.getElementById('FINAL_BOX').innerHTML += "<button class='downloadBtn'>Download SQL</button>";

      let h = "";
      h += '<div class="tbl-header"><table cellpadding="0" cellspacing="0" border="0"><thead><tr>';
      for(let j = 0; j < table[0].length; j++) {
        h += "<th>"+table[0][j]['value']+"</th>";
      }

      h += "</tr>";
      h += "<tr>";

      for(let j = 0; j < table[0].length; j++) {
        h += "<th><input type='text' class='type' value="+table[0][j]['type']+"></th>";
      }

      h += '</tr></thead></table></div><div class="tbl-content"><table cellpadding="0" cellspacing="0" border="0"><tbody>';
      var i=1;
      for(let i = 1; i < table.length; i++) {
        h += "<tr>";
        for(let j = 0; j < table[i].length; j++) {
          h += "<td>"+table[i][j]['value']+"</td>";
        }
        h += "</tr>";
      }
      h += '</tbody></table></div>';

      document.getElementById('FINAL_BOX').innerHTML += h;
    }
}

