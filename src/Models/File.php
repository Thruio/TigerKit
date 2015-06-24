<?php
namespace TigerKit\Models;

use Slim\Log;
use \Thru\ActiveRecord\ActiveRecord;
use Thru\Session\Session;
use TigerKit\TigerApp;

/**
 * Class File
 * @package TigerKit\Models
 * @var $file_id INTEGER
 * @var $user_id INTEGER
 * @var $filename STRING
 * @var $filetype STRING
 * @var $size INTEGER
 * @var $created DATETIME
 * @var $updated DATETIME
 */
class File extends ActiveRecord
{
  protected $_table = "files";

  public $file_id;
  public $user_id;
  public $filename;
  public $filetype;
  public $size;
  public $created;
  public $updated;

  protected $_user;

  /**
   * @return User|false
   */
  public function getUser(){
    if(!$this->_user){
      $this->_user = User::search()->where('user_id',$this->user_id)->execOne();
    }
    return $this->_user;
  }

  static public function CreateFromUpload($uploadFile){
    \Kint::dump($uploadFile);

    exit;
  }


}
