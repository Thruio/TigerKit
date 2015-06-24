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

}
