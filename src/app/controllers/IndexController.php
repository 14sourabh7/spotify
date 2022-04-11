<?php

use Phalcon\Mvc\Controller;


class IndexController extends Controller
{
    public function indexAction()
    {
        //redirection to search page
        $this->response->redirect('/spotify');
    }


    public function apiAction()
    {
        //redirection to search page
        $url = "https://accounts.spotify.com/authorize?";

        $client_id = $this->config->api->get('client_id');
        $client_secret = $this->config->api->get('client_secret');
        $headers = [
            'client_id' => $client_id,
            'client_secret' => $client_secret,
            'redirect_uri' => 'http://localhost:8080/index/token',
            'scope' => 'playlist-modify-public playlist-read-private playlist-modify-private',
            'response_type' => 'code'
        ];

        $OauthUrl = $url . http_build_query($headers);
        $this->response->redirect($OauthUrl);
    }
    public function tokenAction()
    {
        $client_id = $this->config->get('client_id');
        $client_secret = $this->config->get('client_secret');
        if ($this->request->get('code') != null) {
            $code = $this->request->get('code');
            $data = array(
                'redirect_uri' => 'http://localhost:8080/index/token',
                'grant_type'   => 'authorization_code',
                'code'         => $code,
            );
            $ch            = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'https://accounts.spotify.com/api/token');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Authorization: Basic ' . base64_encode($client_id . ':' . $client_secret)));

            $result = json_decode(curl_exec($ch));
            $this->session->set('key', $result->access_token);
            $this->response->redirect("/");
        }
    }
}
