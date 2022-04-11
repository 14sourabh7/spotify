<?php

namespace App\Handler;

use Users;
use Phalcon\Di\Injectable;


class EventHandler extends injectable
{


    public function access()
    {

        $user = Users::findFirst($this->session->get('user_id'));

        //getting access token 
        $result = $this->spotify->getToken(
            'refresh_token',
            $user->refresh_token
        );

        $user->token = $result->access_token;
        $dbresult = $user->save();
        if ($dbresult)
            $this->response->redirect($this->session->get('uri'));
    }
}
