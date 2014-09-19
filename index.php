<?php
session_start();
require_once 'vendor/autoload.php';
require_once 'FacebookBootstrap.php';

echo '<h1>Facebook basic information</h1> ';

$facebook = new FacebookBootstrap();

$redirectURL = 'http://test.dev/facebookSDK'; //redirect page after login complete
$loginURL = $facebook->loginURL($redirectURL);

//login link
echo '<a href="'.$loginURL.'">Login</a>';

//Get token

$data = $facebook->getAccessToken($redirectURL);

if ($data['token']){
    echo '<h2>Token information</h2> ';
    //token details coms from $data = $facebook->getAccessToken($redirectURL);
    var_dump($data);

    //check is token valid, if valid return true, $data['token'] is token
    echo '<h2>Token validation status</h2> ';
    if ( $facebook->isTokenValidate($data['token']) )
    {
        echo 'Token is valid';

    } else {
       echo 'Token is not valid';
    }

    //Extend old token lifetime
    echo '<h2>Extend token life time</h2> ';
    $tokenTime = $facebook->extendTokenTime($data['token']);
    var_dump($tokenTime);

    //Get user profile
    echo '<h2>User profile by token</h2> ';
    $userProfile = $facebook->userProfile($data['token']);
    var_dump($userProfile);

    //Get user friends list
    echo '<h2>User friends list by token</h2> ';
    $userFriendsList = $facebook->friendsList($data['token']);
    var_dump($userFriendsList);

    //Taggable friends list
    echo '<h2>Taggable friends list</h2> ';
    $taggableFriends = $facebook->taggableFriendsList($data['token']);
    var_dump($taggableFriends);

    echo '<h1>Advance operation:</h1>';
    echo '<p>Facebook advance operation like post, post with tag, post with photo, post with location are in FacebookStrap class in details.</p>';
    echo '<p>Facebook photo post need a location page. Find location page by name of location by search function</p>';
    echo '<p></p>';


    echo '<br><h2>Log out link: </h2> ';
    echo '<a href="'.$data['logoutURL'].'">Logout</a>';
}
