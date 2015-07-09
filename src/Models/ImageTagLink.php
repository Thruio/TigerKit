<?php
namespace TigerKit\Models;

use \Thru\ActiveRecord\ActiveRecord;

/**
 * Class Tag
 * @package TigerKit\Models
 * @var $image_tag_id INTEGER
 * @var $tag_id INTEGER
 * @var $file_id TEXT
 */
class ImageTagLink extends UserRelatableObject
{
    protected $_table = "image_tag_links";

    public $image_tag_id;
    public $tag_id;
    public $file_id;
    public $deleted = "No";
}
