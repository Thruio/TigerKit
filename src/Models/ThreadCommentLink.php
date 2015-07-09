<?php
namespace TigerKit\Models;

use \Thru\ActiveRecord\ActiveRecord;

/**
 * Class ThreadCommentLink
 * @package TigerKit\Models
 * @var $thread_comment_id INTEGER
 * @var $thread_id INTEGER
 * @var $comment_id INTEGER
 * @var $deleted ENUM("Yes","No")
 */
class ThreadCommentLink extends ActiveRecord
{
    protected $_table = "thread_comment_links";

    public $thread_comment_id;
    public $thread_id;
    public $comment_id;
    public $deleted = "No";
}
