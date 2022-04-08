<?php

use Phalcon\Mvc\Controller;


class SpotifyController extends Controller
{
    public function indexAction()
    {
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
     * detailsAction()
     * 
     * action to show location details
     *
     * @return void
     */
    public function profileAction()
    {
        $user =
            $this->spotify->getDetails('me');
        $this->view->user = $user;
        $this->view->playlists = $this->spotify->getDetails('me/playlists');

        $input = $this->request->get('playlist');
        if ($input) {
            $result =  $this->spotify->createPlaylist($input, $user['id']);
            if ($result) {
                $this->response->redirect('/spotify/profile');
            }
        }
    }

    public function addtrackAction()
    {
        $inputs = $this->request->get();
        $url = "playlists/" . $inputs['playlist'] . "/tracks";

        $result = $this->spotify->addTrack($url, urldecode($inputs['track']));
        if ($result) {
            $this->response->redirect('/spotify/profile');
        }
    }
    public function playlistAction()
    {
        $playlistId = $this->request->get('id');
        $trackid = $this->request->getPost('trackid');

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
                $this->response->redirect('/spotify/profile');
            }
        }
    }
}
