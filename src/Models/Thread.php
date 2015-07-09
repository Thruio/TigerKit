<?php
namespace TigerKit\Models;

use TigerKit\Services\ImageService;
use Thru\ActiveRecord\ActiveRecord;

/**
 * Class Thread
 * @package TigerKit\Models
 * @var $thread_id INTEGER
 * @var $board_id INTEGER
 * @var $title VARCHAR(140)
 * @var $url TEXT NULLABLE
 * @var $body TEXT NULLABLE
 */
class Thread extends UserRelatableObject
{
    protected $_table = "threads";

    public $thread_id;
    public $board_id;
    public $title;
    public $url = '';
    public $body = '';
}
