<?php
namespace TigerKit\Models;

use TigerKit\Services\ImageService;
use Thru\ActiveRecord\ActiveRecord;

/**
 * Class Board
 * @package TigerKit\Models
 * @var $board_id INTEGER
 * @var $name TEXT
 * @var $subscription_count INTEGER
 * @var $thread_count INTEGER
 */
class Board extends UserRelatableObject
{
    protected $_table = "boards";

    public $board_id;
    public $name;
    public $subscription_count = 0;
    public $thread_count = 0;
}
