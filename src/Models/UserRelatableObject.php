<?php
namespace TigerKit\Models;

use Slim\Log;
use \Thru\ActiveRecord\ActiveRecord;
use Thru\ActiveRecord\VersionedActiveRecord;
use Thru\Session\Session;
use TigerKit\TigerApp;

/**
 * Class UserRelatableObject
 * @package TigerKit\Models
 * @var $created DATETIME
 * @var $created_user_id INTEGER
 * @var $updated DATETIME
 * @var $updated_user_id INTEGER
 */
abstract class UserRelatableObject extends VersionedActiveRecord
{
    public $created;
    public $created_user_id;
    public $updated;
    public $updated_user_id;
    protected $_created_user;
    protected $_updated_user;

    public function save($automatic_reload = true)
    {
        $currentUser = User::getCurrent();

        // Set the created date & user
        if (!$this->created) {
            $this->created = date("Y-m-d H:i:s");
            if ($currentUser instanceof User) {
                $this->created_user_id = $currentUser->user_id;
            }
        }

        // Set the Updated date & user
        $this->updated = date("Y-m-d H:i:s");
        if ($currentUser instanceof User) {
            $this->updated_user_id = $currentUser->user_id;
        }

        return parent::save($automatic_reload);
    }

    /**
     * @return User|false
     */
    public function getCreatedUser()
    {
        if (!$this->_created_user) {
            $this->_created_user = User::search()->where('user_id', $this->created_user_id)->execOne();
        }
        return $this->_created_user;
    }

    /**
     * @return User|false
     */
    public function getUpdatedUser()
    {
        if (!$this->_updated_user) {
            $this->_updated_user = User::search()->where('user_id', $this->updated_user_id)->execOne();
        }
        return $this->_updated_user;
    }
}
