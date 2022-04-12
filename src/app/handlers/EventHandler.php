<?php

namespace App\Handler;

use Phalcon\Di\Injectable;


class EventHandler extends injectable
{


    public function access()
    {
        //calling class function to generate access token
        $this->auth->getRefreshStatus();
        $this->response->redirect($this->session->get('uri'));
    }
}
