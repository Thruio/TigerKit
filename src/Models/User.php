<?php
namespace TigerKit\Models;

use Slim\Log;
use \Thru\ActiveRecord\ActiveRecord;
use Thru\Session\Session;
use TigerKit\TigerApp;

/**
 * Class User
 * @package TigerKit\Models
 * @var $user_id INTEGER
 * @var $username STRING
 * @var $displayname STRING
 * @var $password STRING(60)
 * @var $email STRING(320)
 * @var $type ENUM("User","Admin")
 */
class User extends UserRelatableObject
{
    protected $_table = "users";

    public $user_id;
    public $username;
    public $displayname;
    public $password;
    public $email;
    public $type = "User";

    public function isAdmin()
    {
        if ($this->type == 'Admin') {
            return true;
        }
        return false;
    }

    public function setPassword($password)
    {
        $this->password = password_hash($password, PASSWORD_DEFAULT);
        $this->save();
        return $this;
    }

    public function checkPassword($password)
    {
        $passwordInfo = password_get_info($this->password);
        // Check for legacy unsalted SHA1
        if (strlen($this->password) == 40 && $passwordInfo['algoName'] == "unknown"){
            if(hash("SHA1", $password) == $this->password){
                $this->setPassword($password);
                TigerApp::log("Password for {$this->username} rehashed (Legacy).");
                return true;
            }
        }
        if (password_verify($password, $this->password)) {
            // success. But check for needing to be rehashed.
            if (password_needs_rehash($this->password, PASSWORD_DEFAULT)) {
                $this->setPassword($password);
                TigerApp::log("Password for {$this->username} rehashed ({$passwordInfo['algoName']}).");
            }
            return true;
        } else {
            return false;
        }
    }

    public static function checkLoggedIn()
    {
        if (self::getCurrent() instanceof User) {
            return true;
        } else {
            TigerApp::getSlimApp()->response()->redirect("/login");
        }
    }

    /**
   * Get the current user.
   * @return User|false
   */
    public static function getCurrent()
    {
        if (Session::get('user') && Session::get('user') instanceof User) {
            return User::search()->where('user_id', Session::get('user')->user_id)->execOne();
        }
        return false;
    }

    /**
   * Set the current user.
   * @param User $user
   * @return bool
   */
    public static function setCurrent(User $user = null)
    {
        Session::set('user', $user);
        return true;
    }


    public function save($automatic_reload = true)
    {
        if (!$this->user_id) {
            TigerApp::log(Log::ALERT, "New user created: {$this->username} / {$this->displayname} / {$this->email}");
        }

        return parent::save($automatic_reload);
    }
}
