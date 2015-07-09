<?php
namespace TigerKit\Models;

use TigerKit\Services\ImageService;
use Thru\ActiveRecord\ActiveRecord;

/**
 * Class Board
 * @package TigerKit\Models
 * @var $post_id INTEGER
 * @var $board_id INTEGER
 * @var $title VARCHAR(140)
 * @var $url TEXT
 * @var $body TEXT
 */
class Post extends UserRelatableObject
{
    protected $_table = "posts";

    public $post_id;
    public $board_id;
    public $title;
    public $url;
    public $body;
}
