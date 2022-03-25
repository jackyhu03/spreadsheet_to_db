<?php
    
    class ARequest {
    	
        private $curl;
        private $url;
        private $status;
		private $response;
        
        public function __construct($url, $access_token = false){

            $this->url = $url;
            $this->curl = curl_init($url);

            curl_setopt_array($this->curl, [
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "GET",
                CURLOPT_HTTPHEADER => [
                    "Content-Type: application/json",
                    $access_token? "Authorization: Bearer {$access_token}" : ""
                ],
            ]);

            //curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
            //curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            
            $this->status = 0;
        }
        
        public function send(){

            $this->response = curl_exec($this->curl);
    		curl_close($this->curl);
            $this->status = 1;

            $err = curl_error($this->curl);

            if ($err)
                $this->response = -1;

            return $this->response;
        }
        
        public function get_response(){
        	if ($this->status)
        		return $this->response;
            else
            	return 0;
        }
        
        public function get_url(){
        	return $this->url;
        }

        public function get_status(){
        	return $this->status;
        }
    }


    // classe statica per richieste veloci
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