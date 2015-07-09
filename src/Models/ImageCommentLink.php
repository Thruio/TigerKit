<?php
namespace TigerKit\Models;

use \Thru\ActiveRecord\ActiveRecord;

/**
 * Class ImageCommentLink
 * @package TigerKit\Models
 * @var $image_comment_id INTEGER
 * @var $file_id INTEGER
 * @var $comment_id INTEGER
 * @var $deleted ENUM("Yes","No")
 */
class ImageCommentLink extends ActiveRecord
{
    protected $_table = "image_comment_links";

    public $image_comment_id;
    public $file_id;
    public $comment_id;
    public $deleted = "No";
}
