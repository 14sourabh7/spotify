<?php


use Phalcon\Mvc\Controller;


class IndexController extends Controller
{



    public function indexAction()
    {


        $this->auth->checkLogin();
        $this->response->redirect('/spotify');
    }


    public function apiAction()
    {
        $this->auth->checkLogin();

        //getting url
        $OauthUrl = $this->auth->getAuthUrl();
        $this->view->OauthUrl = $OauthUrl;

        if ($this->request->get('code') != null) {
            $code = $this->request->get('code');
            if ($this->auth->getStatus($code)) {
                //redirection after successful token generation
                $this->response->redirect("/");
            }
        }
    }
}
