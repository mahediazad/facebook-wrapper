<?php
/**
 * Created by Md. Mahedi Azad
 * User: facebook wrapper
 * Date: 6/15/14
 * Time: 8:27 PM
 */

require_once ("vendor/autoload.php");

define("APP_ID", "Your application key");
define("APP_SECRET", "Your application secrate");

class FacebookBootstrap
{
    public $allScope = array(
        'public_profile',
        'email',
        'user_friends',
        'user_likes ',
        'publish_actions',
        'publish_stream',
        'publish_actions',
        'status_update',
        'photo_upload',
        'read_stream',
        'read_friendlists',
        'read_insights',
        'read_mailbox',
        'read_requests',
        'read_page_mailboxes',
        'user_online_presence',
        'friends_online_presence',
        'manage_pages'
    );

    public function __construct()
    {
        \Facebook\FacebookSession::setDefaultApplication(APP_ID, APP_SECRET);
    }


    /**
     * Getting token wth Grap Api
     *
     * @param $scope array
     * @param $redirectURl string
     * @return array
     */

    public function getAccessToken($redirectURl, $scope = null)
    {
        if ($scope == '')
            $scope = $this->allScope;

        $helper = new \Facebook\FacebookRedirectLoginHelper($redirectURl);

        try {
            $tokenInfo = json_encode(file_get_contents(
                'https://graph.facebook.com/oauth/access_token?' . http_build_query(
                    array(
                        'client_id' => APP_ID,
                        'client_secret' => APP_SECRET,
                        'redirect_uri' => $redirectURl,
                        'code' => $_GET['code']
                    )
                )
            ));
            $token = explode("=", $tokenInfo);
            $response['tokenExpireTime'] = $token[2];

            $key = explode("&expires", $token[1]);
            $response['token'] = $key[0];

            $session = new \Facebook\FacebookSession($key[0]);
            $response['logoutURL'] = $helper->getLogoutUrl($session, $redirectURl);
            $response['status'] = true;

            return $response;

        } catch (Exception $e) {
            $response['exceptionCode'] = $e->getCode();
            $response['exceptionMessage'] = $e->getMessage();
            $response['loginURL'] = $helper->getLoginUrl($scope);
            $response['status'] = false;

            return $response;

        }
    }


    /**
     * Token validation checking
     *
     * @param $token
     * @return mixed
     *
     */
    public function isTokenValidate($token)
    {
        $session = new \Facebook\FacebookSession($token);

        if ($session) {
            try {
                $request = new \Facebook\FacebookRequest($session, 'GET', '/me');
                $response = $request->execute()->getGraphObject()->asArray();
                return $response['verified'];

            } catch (\Facebook\FacebookRequestException $e) {
                $response['exceptionCode'] = $e->getCode();
                $response['exceptionMessage'] = $e->getMessage();
                $response['verified'] = false;

                return $response;

            }
        }

    }


    /**
     * Extend token life with grap api
     *
     * @param $oldToken
     * @return mixed
     */
    public function extendTokenTime($oldToken)
    {

        try {
            $tokenInfo = file_get_contents(
                'https://graph.facebook.com/oauth/access_token?' . http_build_query(
                    array(
                        'client_id' => APP_ID,
                        'client_secret' => APP_SECRET,
                        'grant_type' => 'fb_exchange_token',
                        'fb_exchange_token' => $oldToken

                    )
                )
            );


            $token = explode("=", $tokenInfo);
            $response['tokenExpireTime'] = $token[2];

            $key = explode("&expires", $token[1]);
            $response['token'] = $key[0];
            $response['status'] = true;

            return $response;

        } catch (Exception $e) {
            $response['exceptionCode'] = $e->getCode();
            $response['exceptionMessage'] = $e->getMessage();
            $response['status'] = false;

            return $response;
        }

    }

    /**
     * user profile information
     *
     * @param $token
     * @return mixed
     *
     */
    public function userProfile($token)
    {
        $session = new \Facebook\FacebookSession($token);

        if ($session) {

            try {

                $request = new \Facebook\FacebookRequest($session, 'GET', '/me');
                $response = $request->execute()->getGraphObject()->asArray();
                $response['status'] = true;

                return $response;

            } catch (\Facebook\FacebookRequestException $e) {

                $response['exceptionCode'] = $e->getCode();
                $response['exceptionMessage'] = $e->getMessage();
                $response['status'] = false;

                return $response;

            }
        }
    }

    /**
     * login url
     *
     * @param $redirectURl
     * @param null $scope
     * @return string
     *
     */
    public function loginURL($redirectURl, $scope = null)
    {
        if ($scope == '') $scope = $this->allScope;

        $helper = new \Facebook\FacebookRedirectLoginHelper($redirectURl);
        return $helper->getLoginUrl($scope);
    }

    /**
     * Get user's friends list information with token
     *
     * @param $token
     * @return mixed
     */
    public function friendsList($token)
    {
        $session = new \Facebook\FacebookSession($token);

        if ($session) {

            try {
                $request = new \Facebook\FacebookRequest($session, 'GET', '/me/friends');
                $response = $request->execute()->getGraphObject()->asArray();
                $response['status'] = true;

                return $response;

            } catch (\Facebook\FacebookRequestException $e) {

                $response['exceptionCode'] = $e->getCode();
                $response['exceptionMessage'] = $e->getMessage();
                $response['status'] = false;

                return $response;

            }
        }

    }


    /**
     * Taggable friends list with tagableID by user token
     *
     * Description: To get limited list add session url as /me/taggable_friends?limit=100
     *
     * @param $token
     * @return mixed
     */

    public function taggableFriendsList($token)
    {
        $session = new \Facebook\FacebookSession($token);

        if ($session) {

            try {
                $request = new \Facebook\FacebookRequest($session, 'GET', '/me/taggable_friends');
                $response = $request->execute()->getGraphObject()->asArray();
                $response['status'] = true;

                return $response;

            } catch (\Facebook\FacebookRequestException $e) {

                $response['exceptionCode'] = $e->getCode();
                $response['exceptionMessage'] = $e->getMessage();
                $response['status'] = false;

                return $response;

            }
        }

    }

    /**
     * Publish by access token only text
     *
     * @param $data
     * @return mixed
     *
     */
    public function publishPostByToken($data)
    {
        $session = new \Facebook\FacebookSession($data['token']);

        $session = \Facebook\FacebookSession::newAppSession();

        try {
            $response = (new \Facebook\FacebookRequest(
                $session, 'POST', "/me/feed",
                array(
                    'access_token' => $data['token'],
                    'message' => $data['message'],
                    'tags' =>  $data['tags'],
                    'place' => $data['place'] //page id of a location
                )
            ))->execute()->getGraphObject()->asArray();
            $response['successMessage'] = 'Post has been successfully executed';
            $response['status'] = true;

            return $response;

        } catch (\Facebook\FacebookRequestException $e) {

            $response['exceptionCode'] = $e->getCode();
            $response['exceptionMessage'] = $e->getMessage();
            $response['status'] = false;

            return $response;
        }
    }

    /**
     * Publish by access token with image
     *
     * @param $data $data['file'] must be real path of the computers
     * @return mixed
     */
    function photoUploadByToken($data)
    {
        $session = new \Facebook\FacebookSession($data['token']);

        $session = \Facebook\FacebookSession::newAppSession();
        try {
            $response = (new \Facebook\FacebookRequest(
                $session, 'POST', "/me/photos",
                array(
                    'access_token' => $data['token'],
                    'message' => $data['message'],
                    'source' => new CURLFile($data['files'], 'image/jpg'),
//                    'tags' =>  $data['tags'],
                    'place' => $data['place']  //dhaka bangladesh location
                )
            ))->execute()->getGraphObject()->asArray();

            $response['successMessage'] = 'Post has been successfully executed';
            $response['status'] = true;

            return $response;

        } catch (\Facebook\FacebookRequestException $e) {

            $response['exceptionCode'] = $e->getCode();
            $response['exceptionMessage'] = $e->getMessage();
            $response['status'] = false;

            return $response;
        }
    }


    /**
     * Tag a photo by taggable friends id and posted photo id.
     *
     * $data['token'] = user token
     *
     * @param $data
     * @param $fbPhotoID is an existing photo post id
     * @param $taggableFriendID  getting from taggableFriendsList()
     * @return mixed
     */
    function photoTagByToken($token, $fbPhotoID, $taggableFriendID)
    {
        $session = new \Facebook\FacebookSession($token);
        $session = \Facebook\FacebookSession::newAppSession();

        try {
            $response = (new \Facebook\FacebookRequest(
                $session, 'POST', "/{$fbPhotoID}/tags",
                array(
                    'access_token' => $token,
                    'to'=> $taggableFriendID,
                    'x'      => 0,
                    'y'      => 0
                )
            ))->execute()->getGraphObject()->asArray();
//            $response['status'] = true;
            return $response;

        } catch (\Facebook\FacebookRequestException $e) {
            $response['exceptionCode'] = $e->getCode();
            $response['exceptionMessage'] = $e->getMessage();
            $response['status'] = false;
            return $response;
        }
    }


    /**
     * Tag a post to your friends
     *
     * $data['tags'] is a comma-separated string of IDs taggable users id get from taggableFriendsList();
     * $data['place'] = place id pages
     *
     *
     * @param $data
     * @return mixed
     */
    function postWithFriendsTag($data)
    {
        $session = new \Facebook\FacebookSession($data['token']);

        $session = \Facebook\FacebookSession::newAppSession();
        try {
            $response = (new \Facebook\FacebookRequest(
                $session, 'POST', "/me/feed",
                array(
                    'access_token' => $data['token'],
                    'message' => $data['message'],
                    'source' => new CURLFile($data['files'], 'image/jpg'),
                    'tags' =>  $data['tags'],
                    'place' => $data['place'] //location page id
                )
            ))->execute()->getGraphObject()->asArray();
            $response['successMessage'] = 'Post has been successfully executed';
            $response['status'] = true;

            return $response;

        } catch (\Facebook\FacebookRequestException $e) {

            $response['exceptionCode'] = $e->getCode();
            $response['exceptionMessage'] = $e->getMessage();
            $response['status'] = false;

            return $response;
        }
    }


    /**
     * Get list of user's fan pages
     *
     * Description: Input users token and get response in array of data if status is not false.
     *
     * @param $token
     * @return mixed
     */
    public function userPageList($token)
    {
        $session = new \Facebook\FacebookSession($token);

        if ($session) {

            try {
                $request = new \Facebook\FacebookRequest($session, 'GET', '/me/accounts?fields=name,access_token,category,id&limit=10&imit=100');
                $response = $request->execute()->getGraphObject()->asArray();
                $response['data']['status'] = true;

                return $response['data'];

            } catch (\Facebook\FacebookRequestException $e) {

                $response['exceptionCode'] = $e->getCode();
                $response['exceptionMessage'] = $e->getMessage();
                $response['status'] = false;

                return $response;

            }
        }

    }

    /**
     * Publish on fan page by access token text or images jpg
     *
     * Type of post is different 1.Only text  2.Post jpg image 3.Video post
     *
     * @param $data indexs are token, pageID, pageName, message,link, files (should be root path)
     * @return mixed
     *
     */
    public function postOnPage($data)
    {
        $session = new \Facebook\FacebookSession($data['token']);

        $session = \Facebook\FacebookSession::newAppSession();

        try {
            if(!empty($data['files']) & file_exists($data['files'])){
                $response = (new \Facebook\FacebookRequest(
                    $session, 'POST', '/'.$data['pageID'].'/picture',
                    array(
                        'access_token' => $data['token'],
                        'message' => $data['message'],
                        'source' => new CURLFile($data['files'], 'image/jpg'),
                        'place' => $data['place']
                    )
                ))->execute()->getGraphObject()->asArray();

            } else{
                $response = (new \Facebook\FacebookRequest(
                    $session, 'POST', '/'.$data['pageID'].'/feed',
                    array(
                        'access_token' => $data['token'],
                        'message' => $data['message'],
                        'link' => $data['link']
                    )
                ))->execute()->getGraphObject()->asArray();
            }

            $response['successMessage'] = 'Post has been successfully executed on '.$data['pageName'];
            $response['status'] = true;

            return $response;

        } catch (\Facebook\FacebookRequestException $e) {

            $response['exceptionCode'] = $e->getCode();
            $response['exceptionMessage'] = $e->getMessage();
            $response['status'] = false;

            return $response;
        }
    }


    /**
     * Search on facebook
     *
     * $data['query'] is what do you want to find on facebook
     * $data['type'] it is the type of search. facebook allow [user, page, event, group, place]
     *
     * Allowable search in v4.0 php sdk:
     *    People: https://graph.facebook.com/search?q=mark&type=user
     *    Pages: https://graph.facebook.com/search?q=platform&type=page
     *    Events: https://graph.facebook.com/search?q=conference&type=event
     *    Groups: https://graph.facebook.com/search?q=programming&type=group
     *    Places: https://graph.facebook.com/search?q=coffee&type=place&center=37.76,122.427&distance=1000
     *
     * Not allowable search in v4.0 php sdk:
     *    Checkins: https://graph.facebook.com/search?type=checkin
     *    posts: https://graph.facebook.com/search?q=watermelon&type=post
     *
     * Making data:
     *    $data = array(
     *    'token'=>$token,
     *    'query' => 'place name',
     *    'type' => 'place',
     *    'limit' => 20
     *    );
     *
     * @param $data
     * @return mixed
     */
    public function search($data)
    {
        $session = new \Facebook\FacebookSession($data['token']);

        if ($session) {

            try {
                $request = new \Facebook\FacebookRequest($session, 'GET', '/search?q='.$data['query'].'&type='.$data['type'].'&limit='.$data['limit']);
                $response = $request->execute()->getGraphObject()->asArray();

                if($data['type'] == 'place' | $data['type'] == 'page' | $data['type'] == 'event' |  $data['type'] == 'user' | $data['type'] == 'group'){
                    if(count($response['data']) >0){
                        $response['status'] = true;
                        return $response['data'];
                    }
                }else{
                    $response['status'] = false;
                    return $response;
                }

            } catch (\Facebook\FacebookRequestException $e) {

                $response['exceptionCode'] = $e->getCode();
                $response['exceptionMessage'] = $e->getMessage();
                $response['status'] = false;

                return $response;

            }
        }
    }

    //check a link about information on facebook
    /**
     * Check a url information on facebook
     *
     * $data['token'] is the real-current token of user
     * $data['url'] = http://example.com
     *
     *
     * @param $data
     * @return mixed
     */
    public function urlInformation($data)
    {
        $session = new \Facebook\FacebookSession($data['token']);

        if ($session) {

            try {
                $request = new \Facebook\FacebookRequest($session, 'GET', '/?id='.$data['url']);
                $response = $request->execute()->getGraphObject()->asArray();
                $response['status'] = true;
                return $response;

            } catch (\Facebook\FacebookRequestException $e) {

                $response['exceptionCode'] = $e->getCode();
                $response['exceptionMessage'] = $e->getMessage();
                $response['status'] = false;

                return $response;

            }
        }
    }


    /**
     * Get page information by page id
     *
     * $data['pageID']= page id
     * $data['token'] = user token
     * @param $data
     * @return array
     */
    public function getPageInformation($data)
    {
        $session = new \Facebook\FacebookSession($data['token']);

        if ($session) {

            try {
                $request = new \Facebook\FacebookRequest($session, 'GET', '/'.$data['pageID']);
                $response = $request->execute()->getGraphObject()->asArray();
                $response['status'] = true;
                $result = array('link'=> $response['link'], 'location'=>$response['location'], 'name'=>$response['name']);
                return $result;

            } catch (\Facebook\FacebookRequestException $e) {

                $response['exceptionCode'] = $e->getCode();
                $response['exceptionMessage'] = $e->getMessage();
                $response['status'] = false;

                return $response;

            }
        }
    }




}
