<?php

    require_once '../../googleTools.php';

    if (isset($_GET['code']))
    {
        $client = client_google::get_object();
        $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);
        
        try {
            $client->setAccessToken($token['access_token']);
        } catch (Exception $e){
            response::client_error(400, "Invalid URL");
        };

        $google_oauth = new Google_Service_Oauth2($client);
        $google_account_info = $google_oauth->userinfo->get();
        $email =  $google_account_info->email;
        $name =  $google_account_info->name;
        $data = array($email, $name);
        //header("Location: pvt.php");
    }
    else
    {	
        // quando l'utente fa una r.get alla pagina di login
        $client = client_google::get_object();
    }


?>