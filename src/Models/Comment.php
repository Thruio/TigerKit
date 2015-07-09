<?php
namespace TigerKit\Models;

use TigerKit\Services\ImageService;
use Thru\ActiveRecord\ActiveRecord;

/**
 * Class Tag
 * @package TigerKit\Models
 * @var $comment_id INTEGER
 * @var $comment TEXT
 * @var $child_of_comment_id INTEGER NULLABLE
 */
class Comment extends UserRelatableObject
{
    protected $_table = "comments";

    public $comment_id;
    public $comment;
    public $child_of_comment_id;
}
