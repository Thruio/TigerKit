<?php
namespace TigerKit\Models;

use TigerKit\Services\ImageService;
use Thru\ActiveRecord\ActiveRecord;

/**
 * Class Tag
 * @package TigerKit\Models
 * @var $tag_id INTEGER
 * @var $name TEXT
 * @var $popularity_count INTEGER
 * @var $hidden ENUM("Yes","No")
 */
class Tag extends UserRelatableObject
{
    protected $_table = "tags";

    public $tag_id;
    public $name;
    public $popularity_count;
    public $hidden = "No";

    public function updatePopularityCount()
    {
        $imageService = new ImageService();
        $images = $imageService->getImagesByTag($this->name);
        $this->popularity_count = count($images);
        $this->save();
    }
}
