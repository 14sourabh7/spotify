<?php



use Phalcon\Mvc\Controller;


class UserController extends Controller
{
    public function indexAction()
    {

        $check = $this->request->isPost();
        if ($check) {
            $email = $this->request->getPost('email');
            $password = $this->request->getPost('password');
            if ($email && $password) {
                $user = Users::findFirst(['conditions' => ["email = '$email' AND password = '$password'"]]);
                if ($user) {
                    $token = $user->token;
                    if ($token) {
                        $this->session->set('user_id', $user->user_id);
                        $this->response->redirect('/');
                    } else {
                        $this->session->set('user_id', $user->user_id);
                        $this->response->redirect('/index/api');
                    }
                }
            }
        }
    }
    public function signupAction()
    {
        $check = $this->request->isPost();
        if ($check) {
            $name = $this->request->getPost('name');
            $email = $this->request->getPost('email');
            $password = $this->request->getPost('password');
            $user = new Users();
            $user->name = $name;
            $user->email = $email;
            $user->password = $password;
            $result = $user->save();
            if ($result) {
                $this->response->redirect('/user');
            }
        }
    }

    public function dashboardAction()
    {

        $recommendations = $this->spotify->getDetails('recommendations?seed_artists=4NHQUGzhtTLFvgF5SZesLK&seed_genres=classical%2Ccountry&seed_tracks=0c6xIDDpzE81m2q797ordA');

        $this->view->recommendations = $recommendations;
    }

    public function logoutAction()
    {
        $this->session->set('user_id', 0);
    }
}
