<?php
 
    class request {

        // Ritorna JSON string
        public static function GET(string $url, $ct = 'Content-Type:application/json'){

            $ch = curl_init($url); 
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array($ct));
            $data = curl_exec($ch);
            curl_close($ch);
            return $data;
        }

        // Ritorna JSON string
        public static function POST(string $url, array $data, $ct = 'Content-Type:application/json'){

            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $payload = json_encode($data);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array($ct));
            $data = curl_exec($ch);
            curl_close($ch);
            return $data;
        }
    }

?>