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
            0 => "AIzaSyAxrhJVQNqFD43MjLPLlj55OlLXs7yUqJw", 
        );

        private const API_LINK = "https://sheets.googleapis.com/v4/spreadsheets/";

        // Get table values (array)   $sheet_name = nome foglio, interval = [AN:BM]
        // -> vai a format data per vedere come accedere ai valori restituiti
        public static function get_spreadsheet(string $spreadsheet_id, string $sheet_name, string $interval = ''){
            if ($interval !== '') $interval = "!".$interval;
            $url = self::API_LINK.$spreadsheet_id."/values/".$sheet_name.$interval."?key=".self::KEYS[0];
            $response = request::GET($url);
            $data = self::format_data($response);
            $status = self::check_data($data);
            return $status ? $data : false;
        }

        // Global settings
        public static function get_spreadsheet_settings(string $spreadsheet_id){
            $url = self::API_LINK.$spreadsheet_id."?key=".self::KEYS[0];
            $response = request::GET($url);
            $data = self::format_data($response);
            $status = self::check_data($data);
            return $status ? $data : false;
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

        // Rimuove righe e colonne vuote (in caso venga passata una tabella non posizionata in alto a sinistra del foglio google)
        // Ritorna array facilmente accessibile 
        private static function format_data(string $json){
            $array = json_decode($json, true);
            $t_array = array();

            foreach ($array as $key => $value){
                if($key !== 'values')
                    $t_array[$key] = $value; 
            }

            $array = $array['values'];

            // Delete empty rows 
            $n = 0;
            while (count($array[$n])===0){
                unset($array[$n]);
                $n++; 
            }
            unset($n);

            // Reset index 
            $array = array_values($array);

            // Delete empty columns 
            for ($i=0; $i<count($array); $i++){
                $ssize = count($array[$i]);
                for ($j=0; $j<$ssize; $j++){
                    if ($array[$i][$j]==="")
                        unset($array[$i][$j]);
                }
                $array[$i] = array_values($array[$i]);
            }
            
            // Reset index 
            $array = array_values($array);
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