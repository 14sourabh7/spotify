<?php

namespace App\Handler;

use Users;
use Phalcon\Di\Injectable;

class EventHandler extends injectable
{


    public function access()
    {

        $user = Users::findFirst($this->session->get('user_id'));

        $data = array(
            'redirect_uri' => 'http://localhost:8080/index/api',
            'grant_type'   => 'refresh_token',
            'refresh_token'         => $user->refresh_token,
        );
        $ch            = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://accounts.spotify.com/api/token');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Authorization: Basic ' . base64_encode(
            $this->config->api->get('client_id') . ':' .
                $this->config->api->get(
                    'client_secret'
                )
        )));

        $result = json_decode(curl_exec($ch));
        $user->token = $result->access_token;
        $dbresult = $user->save();
        if ($dbresult)
            $this->response->redirect($this->session->get('uri'));
    }
}
