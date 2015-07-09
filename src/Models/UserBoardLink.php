<?php
namespace TigerKit\Models;

/**
 * Class Tag
 * @package TigerKit\Models
 * @var $user_board_id INTEGER;
 * @var $user_id INTEGER
 * @var $board_id INTEGER
 * @var $file_id TEXT
 */
class UserBoardLink extends UserRelatableObject
{
    protected $_table = "user_board_links";

    public $user_board_id;
    public $user_id;
    public $board_id;
    public $deleted = "No";
}
