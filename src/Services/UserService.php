<?php
namespace TigerKit\Services;

use Slim;
use TigerKit\Models;
use TigerKit\TigerApp;
use Thru\Session\Session;
use TigerKit\TigerException;

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
        }else {
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
        if (Models\User::search()->where('username', $username)->count()) {
            throw new TigerException("Username {$username} already in use.");
        }

        if (Models\User::search()->where('email', $email)->count()) {
            throw new TigerException("Email {$email} already in use.");
        }

        if (strlen($password) <= 5) {
            throw new TigerException("Passwords must be 6 or more characters long.");
        }

        if (filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            throw new TigerException("{$email} is not a valid email address.");
        }

        $user = new Models\User();
        $user->username = $username;
        $user->displayname = $realname;
        $user->setPassword($password);
        $user->created = date("Y-m-d H:i:s");
        $user->email = $email;
        $user->save();
        return $user;
    }

    public function findUserByUUID($uuid)
    {
        $user = Models\User::search()->where('user_uuid', $uuid)->execOne();
        if (!$user) {
            throw new TigerException("Cannot find User by UUID");
        }
        return $user;
    }
}
