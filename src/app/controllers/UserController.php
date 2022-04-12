<?php



use Phalcon\Mvc\Controller;


class UserController extends Controller
{
    public function indexAction()
    {
        if ($this->session->get('login')) {
            $this->response->redirect('/user/dashboard');
        };



        $check = $this->request->isPost();
        if ($check) {
            $email = $this->escaper->sanitize($this->request->getPost('email'));
            $password = $this->escaper->sanitize($this->request->getPost('password'));
            if ($email && $password) {
                $user = $this->user->checkUser($email, $password);

                if ($user) {
                    $token = $user[0]->token;

                    if ($token) {
                        $this->session->set('user_id', $user[0]->user_id);
                        $this->session->set('login', 1);
                        $this->response->redirect('/user/dashboard');
                    } else {
                        $this->session->set('user_id', $user[0]->user_id);
                        $this->session->set('login', 1);
                        $this->response->redirect('/index/api');
                    }
                }
            }
        }
    }


    /**
     * signup action
     *
     * @return void
     */
    public function signupAction()
    {
        if ($this->session->get('login')) {
            $this->response->redirect('/user/dashboard');
        };

        $check = $this->request->isPost();
        if ($check) {
            $name = $this->escaper->sanitize($this->request->getPost('name'));
            $email = $this->escaper->sanitize($this->request->getPost('email'));
            $password = $this->escaper->sanitize($this->request->getPost('password'));
            $result = $this->user->addUser($name, $email, $password);
            if ($result) {
                $this->response->redirect('/user');
            }
        }
    }


    /**
     * action to display dashboard
     *
     * @return void
     */
    public function dashboardAction()
    {
        $this->auth->checkLogin();

        //getting details from api
        $user =
            $this->spotify->getDetails('me');
        $this->view->user = $user;
        $this->view->playlists = $this->spotify->getDetails('me/playlists');

        $input = $this->request->get('playlist');

        if ($input) {
            //function to create playlist
            $result =  $this->spotify->createPlaylist($input, $user['id']);
            if ($result) {
                $this->response->redirect($this->request->get('_url'));
            }
        }

        $recommendations = $this->spotify->getDetails(
            'recommendations?
            seed_artists=3TVXtAsR1Inumwj472S9r4
            &seed_genres=classical%2Ccountry&
            seed_tracks=0c6xIDDpzE81m2q797ordA'
        );

        $this->view->recommendations = $recommendations;
    }


    /**
     * logout action
     *
     * @return void
     */
    public function logoutAction()
    {

        $this->session->set('login', 0);
        $this->response->redirect('/user');
    }
}
