<?php
namespace TigerKit\Services;

use Slim;
use TigerKit\Models;
use TigerKit\TigerApp;
use Thru\Session\Session;

class UserService extends BaseService
{

    /**
   * @param $username
   * @param $password
   * @return bool
   */
    public function doLogin($username, $password)
    {
        // Support logging in with email address
        $user = Models\User::search()->where('email', $username)->execOne();

        // Support logging in with username
        if (!$user instanceof Models\User) {
            $user = Models\User::search()->where('username', $username)->execOne();
        }

        if (!$user instanceof Models\User) {
            TigerApp::log("No such user {$username}", Slim\Log::WARN);
            return false;
        } elseif ($user->checkPassword($password)) {
            Session::set("user", $user);
            return true;
        } else {
            TigerApp::log("Failed login for {$username}", Slim\Log::WARN);
            return false;
        }
    }

    /**
   * @param $username
   * @param $realname
   * @param $password
   * @param $email
   * @return Models\User
   */
    public function createUser($username, $realname, $password, $email)
    {
        $user = new Models\User();
        $user->username = $username;
        $user->displayname = $realname;
        $user->setPassword($password);
        $user->created = date("Y-m-d H:i:s");
        $user->email = $email;
        $user->save();
        return $user;
    }
}
