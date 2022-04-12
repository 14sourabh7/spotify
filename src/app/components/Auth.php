<?php
//class to handle api requests
namespace App\Components;

use Phalcon\Di\Injectable;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7;
use GuzzleHttp\Exception\ClientException;
use Users;

class Auth extends injectable
{
    private $client_id;
    private $client_secret;
    private $client;
    public function __construct()


    {
        $this->client_id
            = $this->config->api->get('client_id');
        $this->client_secret = $this->config->api->get('client_secret');
        $this->client = $this->setClient();
    }

    /**
     * setClient()
     * function to set the client
     *
     * @return object
     */
    private function setClient()
    {
        $client = new Client([
            'base_uri' => "https://accounts.spotify.com",
            'timeout'  => 2.0,
            'headers' => array(
                'Authorization' => "Basic "  . base64_encode($this->client_id . ':' . $this->client_secret),
                'Content-Type' => 'application/x-www-form-urlencoded'
            )
        ]);

        return $client;
    }

    /**
     * setAuthUrl()
     * function to set the auth url for authorization code
     *
     * @return string
     */
    private function setAuthUrl()
    {
        $url = "https://accounts.spotify.com/authorize?";
        $headers = [
            'client_id' => $this->client_id,
            'client_secret' => $this->client_secret,
            'redirect_uri' => 'http://localhost:8080/index/api',
            'scope' => 'playlist-modify-public playlist-read-private playlist-modify-private 
            user-read-playback-state',
            'response_type' => 'code'
        ];
        return
            $url . http_build_query($headers);
    }


    /**
     * tokenRequest($data)
     * 
     * function to handle token post request
     *
     * @param [type] $data
     * @return object
     */
    private function tokenRequest($data)
    {
        try {
            $result =  $this->client->request(
                'POST',
                "/api/token",
                [
                    'body' => http_build_query($data)
                ]
            );
            return
                json_decode($result->getBody());
        } catch (ClientException $e) {
            echo Psr7\Message::toString($e->getRequest());
            echo Psr7\Message::toString($e->getResponse());
            die;
        }
    }

    /**
     * function setting code
     *
     * @param [type] $code
     * @return void
     */
    private function setCode($code)
    {
        $data = array(
            'grant_type'   => "authorization_code",
            'code'         => $code,
            'redirect_uri' => 'http://localhost:8080/index/api',
        );

        $result = $this->tokenRequest($data);

        $this->session->set('key', $result->access_token);
        $dbresult =  $this->user->addTokens(
            $this->session->get('user_id'),
            $result->access_token,
            $result->refresh_token
        );
        return $dbresult;
    }

    /**
     * refreshToken()
     * 
     * function to set refresh access token
     *
     * @return void
     */
    private function refreshToken()
    {
        $user = Users::findFirst($this->session->get('user_id'));

        $data = array(
            'redirect_uri' => 'http://localhost:8080/index/api',
            'grant_type'   =>  'refresh_token',
            'refresh_token'  => $user->refresh_token,
        );
        $result = $this->tokenRequest($data);
        $user->token = $result->access_token;
        $dbresult = $user->save();
        return $dbresult;
    }


    /**
     * getAuthUrl()
     * 
     * function to return auth url to get auth code
     *
     * @return void
     */
    public function getAuthUrl()
    {
        return $this->setAuthUrl();
    }


    /**
     * function returning token creation status
     *
     * @param [type] $code
     * @return bool
     */
    public function getStatus($code)
    {
        return $this->setCode($code);
    }

    /**
     * function returning access token with refresh token  status
     *
     * @return bool
     */
    public function getRefreshStatus()
    {
        $this->refreshToken();
    }

    /**
     * function to check login
     *
     * @return void
     */
    public function checkLogin()
    {
        if (!$this->session->get('login')) {
            $this->response->redirect('/user');
        };
    }
}
