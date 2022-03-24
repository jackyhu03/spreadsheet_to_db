<?php
 
    class request {

        // Ritorna JSON string
        public static function GET(string $url, $ct = 'Content-Type: application/json'){

            $curl = curl_init();
            curl_setopt_array($curl, [
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "GET",
                CURLOPT_HTTPHEADER => [
                    "Content-Type: application/json"
                ],
            ]);
            $response = curl_exec($curl);
            $err = curl_error($curl);
            curl_close($curl);

            if ($err) {
                echo "error request";exit;
                return array("success" => false, "error" => "cURL Error #:" . $err);
            } else {
                return $response;
            }
        }

        // Ritorna JSON string
        public static function POST(string $url, array $data, $ct = 'Content-Type: application/json'){

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