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
 * @var $filesize INTEGER
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
  public $filesize;
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
    $class = get_called_class();
    /** @var File $object */
    $object = new $class();
    $object->filename = $uploadFile['name'];
    $object->filetype = $uploadFile['type'];
    $object->filesize = $uploadFile['size'];
    $object->save();

    $storage = TigerApp::getStorage();
    $stream = fopen($uploadFile['tmp_name'], 'r');
    $storage->putStream($object->filename, $stream);
    return $object;
  }

  public function save($automatic_reload = true){
    if(!$this->created){
      $this->created = date("Y-m-d H:i:s");
    }
    $this->updated = date("Y-m-d H:i:s");
    parent::save($automatic_reload);
  }


}
