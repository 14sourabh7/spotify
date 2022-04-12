<?php

use Phalcon\Mvc\Controller;


class SpotifyController extends Controller
{
    public function indexAction()
    {
        $this->auth->checkLogin();
    }

    /**
     * searchAction()
     * 
     * action to show search location results
     *
     * @return void
     */
    public function searchAction()
    {
        $this->auth->checkLogin();

        $inputs = $this->request->get();
        $url = "";
        if (count($inputs) == 2 && $inputs['q']) {
            $url .= "search?q=" . urlencode($inputs['q']) . "&type=track,";
            $track = 'track';
        } else {
            foreach ($inputs as $key => $value) {
                if ($key == '_url') {
                    continue;
                }
                if ($key == 'q') {
                    $url .= "search?q=" . urlencode($inputs['q']) . "&type=";
                } else {

                    $url .= urldecode($value) . ',';
                }
            }
            $track = $this->request->get('track');
        }


        //fetching locations from api
        $result = $this->spotify->getDetails(substr($url, 0, -1));

        if ($track) {
            $playlist = $this->spotify->getDetails('me/playlists');
        }
        $this->view->playlist = $playlist;
        $this->view->response = $result;
    }


    /**
     * action to add tracks to playlist
     *
     * @return void
     */
    public function addtrackAction()
    {
        $inputs = $this->request->get();
        $url = "playlists/" . $this->escaper->sanitize($inputs['playlist']) . "/tracks";

        $result = $this->spotify->addTrack($url, urldecode($inputs['track']));
        if ($result) {
            $this->response->redirect($this->request->get("/user/dashboard"));
        }
    }



    /**
     * action to display playlists 
     * */
    public function playlistAction()
    {
        $this->auth->checkLogin();

        $playlistId = $this->escaper->sanitize($this->request->get('id'));
        $trackid = $this->escaper->sanitize($this->request->getPost('trackid'));

        if ($playlistId) {
            $url = "playlists/" . $playlistId;

            $result = $this->spotify->getDetails($url);
            $this->view->name = $result['name'];
            $this->view->list = $result['tracks']['items'];
        }

        if ($trackid) {
            $url = "playlists/$playlistId/tracks";
            $track = urldecode($trackid);
            $result = $this->spotify->deleteTrack($url, $track);
            if ($result) {

                $this->response->redirect($this->request->get('/user/dashboard'));
            }
        }
    }
}
