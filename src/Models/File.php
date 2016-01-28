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
 */
class File extends UserRelatableObject
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
    public function getUser()
    {
        if (!$this->_user) {
            $this->_user = User::search()
                ->where('user_id', $this->user_id)
                ->execOne();
        }
        return $this->_user;
    }

    /**
     * @param $uploadFile
     * @return File
     */
    public static function CreateFromUpload($uploadFile)
    {
        $class = get_called_class();
        /**
         * @var File $object
*/
        $object = new $class();
        $object->filename = $uploadFile['name'];
        $object->filetype = $uploadFile['type'];
        $object->filesize = $uploadFile['size'];
        $object->save();

        $stream = fopen($uploadFile['tmp_name'], 'r');
        $object->putDataStream($stream);
        return $object;
    }

    public function getDataStream()
    {
        $storage = TigerApp::getStorage();
        return $storage->readStream($this->filename);
    }

    public function getData()
    {
        $storage = TigerApp::getStorage();
        return $storage->read($this->filename);
    }

    public function putDataStream($stream)
    {
        $storage = TigerApp::getStorage();
        $success = $storage->putStream($this->filename, $stream);
        $this->filesize = $storage->getSize($this->filename);
        $this->save();
        return $success;
    }

    public function putData($data)
    {
        $storage = TigerApp::getStorage();
        $this->filesize = strlen($data);
        $success = $storage->put($this->filename, $data);
        $this->save();
        return $success;
    }
}
