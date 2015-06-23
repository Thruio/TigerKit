<?php
namespace TenderBid\Models;

use Slim\Log;
use \Thru\ActiveRecord\ActiveRecord;
use TigerKit\TigerApp;

/**
 * Class User
 * @package Tenderbid\Models
 * @var $user_id INTEGER
 * @var $username STRING
 * @var $displayname STRING
 * @var $password STRING(40)
 * @var $email STRING(320)
 * @var $created DATETIME
 * @var $updated DATETIME
 * @var $type ENUM("User","Admin")
 */
class User extends ActiveRecord
{
  protected $_table = "users";

  public $user_id;
  public $username;
  public $displayname;
  public $password;
  public $email;
  public $created;
  public $updated;
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
    return $this;
  }

  public function checkPassword($password)
  {
    $passwordInfo = password_get_info($this->password);
    $algo = $passwordInfo['algoName'];
    $testHash = password_hash($password, $algo);
    if ($testHash == $this->password) {
      // success. But check for needing to be rehashed.
      if (password_needs_rehash($this->password, $algo)) {
        $this->setPassword($password);
        TigerApp::log("Password for {$this->username} rehashed.");
      }
      return true;
    } else {
      return false;
    }
  }

  static public function checkLoggedIn()
  {
    if (self::getCurrent() instanceof User) {
      return false;
    } else {
      header("Location: /login");
      exit;
    }
  }

  /**
   * Get the current user.
   * @return User|false
   */
  static public function getCurrent()
  {
    if (isset($_SESSION['user'])) {
      if ($_SESSION['user'] instanceof User) {
        return User::search()->where('user_id', $_SESSION['user']->user_id)->execOne();
      }
    }
    return false;
  }


  public function save($automatic_reload = true)
  {
    if (!$this->created) {
      $this->created = date("Y-m-d H:i:s");
    }

    $this->updated = date("Y-m-d H:i:s");

    if (!$this->user_id) {
      ActiveRecord::log(Log::ALERT, "New user created: {$this->username} / {$this->displayname} / {$this->email}");
    }

    return parent::save($automatic_reload);
  }


}
