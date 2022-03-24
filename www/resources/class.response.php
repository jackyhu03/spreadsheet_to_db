<?php
    
    // METODI PUBBLICI
    // response::client_error(codice errore, messaggio risposta facoltativo, json aggiuntivo nella risposta facoltativo => esempio: array("key" => "value", "key2" => "value2"))
    // response::server_error(codice errore, messaggio risposta facoltativo, json aggiuntivo nella risposta facoltativo)
    // response::successful(codice success, messaggio risposta facoltativo, json aggiuntivo nella risposta facoltativo) 

    class response {

        private const CT_JSON = "Content-Type: application/json; charset=utf-8";
        private const CT_TEXT = "Content-Type: text/plain; charset=utf-8";
        private const CT_HTML = "Content-Type: text/html; charset=UTF-8";

        private const ID_SUCCESSFUL = 200;
        private const ID_SERVER_ERROR = 500;
        private const ID_CLIENT_ERROR = 400;

        private const HTTP_RESPONSE_STATUS_CODES = array(

            self::ID_SUCCESSFUL => array(
                
                200 => "OK",
                201 => "Created",
                204 => "No Content"
            ),

            self::ID_CLIENT_ERROR => array(

                400 => "Bad Request",
                401 => "Unauthorized",
                403 => "Forbidden",
                404 => "Not Found",
                405 => "Method Not Allowed",
                429 => "Too Many Requests"
            ),

            self::ID_SERVER_ERROR => array(

                500 => "Internal Server Error",
                501 => "Not Implemented"
            )
        );

        public static function client_error(int $status_code, $status_msg = false, array $array = array()){

            if (!self::status_code_valid($status_code, self::ID_CLIENT_ERROR)) response::server_error(500);

            http_response_code($status_code);
            $status_msg = self::get_status_msg(self::ID_CLIENT_ERROR, $status_code, $status_msg);

            $json = json_encode(array_merge(array('success' => false,'status_code' => $status_code,'status_message' => $status_msg), $array), JSON_PRETTY_PRINT);

            self::send($json, true);
        }

        public static function server_error(int $status_code, $status_msg = false, array $array = array()){

            if (!self::status_code_valid($status_code, self::ID_SERVER_ERROR)) response::server_error(500);

            http_response_code($status_code);
            $status_msg = self::get_status_msg(self::ID_SERVER_ERROR, $status_code, $status_msg);

            $json = json_encode(array_merge(array('success' => false,'status_code' => $status_code,'status_message' => $status_msg), $array), JSON_PRETTY_PRINT);
            
            self::send($json, true);
        }

        public static function successful(int $status_code = 200, $status_msg = false, array $array = array()){

            if (!self::status_code_valid($status_code, self::ID_SUCCESSFUL)) response::server_error(500);

            http_response_code($status_code);
            $status_msg = self::get_status_msg(self::ID_SUCCESSFUL, $status_code, $status_msg);

            $json = json_encode(array_merge(array('success' => true,'status_code' => $status_code,'status_message' => $status_msg), $array), JSON_PRETTY_PRINT);

            self::send($json, false);
        }

        private static function ctype($option){

            switch (strtoupper($option)){
                case 'TEXT': {   
                    header(self::CT_TEXT);
                    break;
                }
                case 'JSON': default: {
                    header(self::CT_JSON);
                    break;
                }
                case 'HTML': {
                    header(self::CT_HTML);
                    break;
                }
            }
        }

        private static function status_code_valid(int $status_code, int $id){

            return ($status_code >= $id && $status_code <= $id + 99);
        }

        private static function get_status_msg(int $index, int $status_code, $status_msg){

            return ( 
                $status_msg !== false ? 
                    $status_msg : (@self::HTTP_RESPONSE_STATUS_CODES[$index][$status_code] === null ? 
                        "Status Message Not Available" : self::HTTP_RESPONSE_STATUS_CODES[$index][$status_code])
            );
        }

        private static function send($response, bool $exit){

            self::ctype('JSON');
            echo $response;

            if ($exit === true) exit;
        }

        // Il percorso del file passato (che si trova nei file del server) verrà scaricato sul lato client
        public static function download_file($filename, bool $exit = true){
            header("Cache-Control: public");
            header("Content-Description: file transfer");
            header("Content-Disposition: attachment; filename={$filename}");
            header("Content-Type: application/zip");
            header("Content-Transer-Encoding: binary");
            http_response_code(200);
            readfile($filename);
            unlink($filename);
            if ($exit === true) exit;
        }
    }
    
?>