<?php

    // METODI PUBBLICI (require 'DataStructures/class.googleAPI.php')

    // googleAPI::get_spreadsheet_settings(id foglio google) 
    //   -> Ritorna array associativo contenente dati generali foglio google
   
    // googleAPI::get_spreadsheet_id(url foglio google) 
    //   -> Ritorna ID foglio passando come parametro url foglio google
   
    // googleAPI::get_spreadsheet(id foglio google, nome foglio (foglio1, 2, 3...), intervallo opzionale)
    //   -> Ritorna tabella richiesta in tipo array (multidimensionale)
    
    require 'class.request.php';

    class googleAPI {

        // Google API available keys
        private const KEYS = array
        (
            0 => "/", 
        );

        private const API_LINK = "https://sheets.googleapis.com/v4/spreadsheets/";

        // Get table values (array)   $sheet_name = nome foglio, interval = [AN:BM]
        // -> vai a format data per vedere come accedere ai valori restituiti
        public static function get_spreadsheet(string $spreadsheet_id, string $sheet_name, string $interval = ''){
            if ($interval !== '') $interval = "!".$interval;
            $url = self::API_LINK.$spreadsheet_id."/values/".$sheet_name.$interval."?key=".self::KEYS[0];
            $response = request::GET($url);
            $data = self::format_data($response);
            if ($data === false) return false;
            $status = self::check_data($data);
            return $status ? $data : false;
        }

        // Global settings
        public static function get_spreadsheet_settings(string $spreadsheet_id){
            $url = self::API_LINK.$spreadsheet_id."?key=".self::KEYS[0];
            $response = request::GET($url);
            $array = json_decode($response, true);
            return gettype($array) === 'array' ? $array : false;
        }

        // Get googleSheet's ID from googleSheet's link
        public static function get_spreadsheet_id(string $url){
            if (!isset($url) || $url === "" || $url === null) return -1;
            $spreadsheet_id = explode("/", $url)[5];
            return $spreadsheet_id; // spreadsheet ID
        }

        // Versione 1: Controlla solo che il numero di colonne sia uguale per tutte le righe
        private static function check_data(array $array){
            $n = count($array[0]);
            for ($i=1; $i<count($array); $i++){
                if (count($array[$i]) !== $n){
                    // Trovate righe con numero di colonne differente
                    return false;
                }
            }
            return true;
        }

        public static function get_table_names(array $spreadsheet_settings){
            if (count($spreadsheet_settings['sheets']) < 1 || !isset($spreadsheet_settings['sheets'])) return false;
            $table_names = array();
            for ($i=0; $i<count($spreadsheet_settings['sheets']); $i++)
                $table_names[] = $spreadsheet_settings['sheets'][$i]['properties']['title'];
            return $table_names; 
        }

        // Rimuove righe e colonne vuote (in caso venga passata una tabella non posizionata in alto a sinistra del foglio google)
        // Ritorna array facilmente accessibile 
        private static function format_data(string $json){
            $array = json_decode($json, true);
            $t_array = array();

            foreach ($array as $key => $value){
                if($key !== 'values')
                    $t_array[$key] = $value; 
            }

            if (!isset($array['values'])){
                return false; // no data in the sheet
            }

            $array = $array['values'];

            for ($i=0; $i<count($array); $i++){
                for ($j=0; $j<count($array[$i]); $j++){
                    $array[$i][$j] = trim($array[$i][$j]);
                }
            }

            $size = count($array);
            // Delete empty rows
            for ($i=0; $i<$size; $i++){
                if (count($array[$i]) === 0)
                    unset($array[$i]);
            }
            unset($size);

            // Reset index 
            $array = array_values($array);
			
            $size0 = -1; // n columns
            // Delete empty columns 
            for ($i=0; $i<count($array); $i++){
                if ($i === 0) {
                    $size0 = count($array[0]);
                    for ($k=0; $k<$size0; $k++){
                        if ($array[0][$k]==="")
                            unset($array[0][$k]);
                    }
                    $size0 = count($array[0]);
                    $akeys = array_keys($array[0]);
                    $min = $akeys[0];
                    $max = $akeys[count($akeys)-1];
                    unset($akeys);
                    continue;
                }
                $ssize = count($array[$i]);
                for ($j=$min; $j<=$max; $j++){
                    if (!isset($array[$i][$j]))
                        $array[$i][$j] = "";
                }
                for ($j=0; $j<$ssize; $j++){
                    if ($array[$i][$j]===""){
                        if ($j < $min || $j > $max){
                            unset($array[$i][$j]);
                        }
                    }
                }
            }

            // Reset index 
            for ($i=0; $i<count($array); $i++)
                $array[$i] = array_values($array[$i]);
            
            return $array;
            /*
                array[0][0->n] record 0: column names
                array[1][0->n] record 1: data
                array[2][0->n] record 2: data
                [...]
            */
        }
    }

    // X testing (stampa matrice in chiaro)
    function print_table($table){
        $str = "";
        for ($i=0; $i<count($table); $i++){
            $str .= " | ";
            for ($j=0; $j<count($table[$i]); $j++) $str .= $table[$i][$j] . " | ";
            if ($i === 0) $str .= "<br>----------------------------------------------------------------<br>";
            else $str .= "<br>";
        }
        echo $str;
    }

?>