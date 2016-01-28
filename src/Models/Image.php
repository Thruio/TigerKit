<?php
namespace TigerKit\Models;

use Slim\Log;
use \Thru\ActiveRecord\ActiveRecord;
use Thru\Session\Session;
use TigerKit\TigerApp;

/**
 * Class Image
 * @package TigerKit\Models
 * @var $width INTEGER
 * @var $height INTEGER
 */
class Image extends File
{
    protected $_table = "images";

    public $width;
    public $height;

    /**
     * @param $uploadFile
     * @return Image
     */
    public static function CreateFromUpload($uploadFile)
    {
        /**
         * @var Image $object
*/
        $object = parent::CreateFromUpload($uploadFile);
        $size = getimagesize($uploadFile['tmp_name']);
        $object->width = $size[0];
        $object->height = $size[1];
        $object->save();
        return $object;
    }
}
