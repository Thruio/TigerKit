<?php
namespace TigerKit\Services;

use TigerKit\Models;

class BoardService extends BaseService
{

    /**
     * @param $name
     * @param Models\User $user
     * @return Models\Board
     */
    public function createBoard($name, Models\User $user)
    {
        $board = new Models\Board();
        $board->name = $name;
        $board->created_user_id = $user->user_id;
        $board->updated_user_id = $user->user_id;
        $board->save();
        return $board;
    }

    /**
     * Get Board by Name or Slug.
     * @param $name
     * @return Models\Board
     */
    public function getBoard($name)
    {
        $byName = Models\Board::search()
          ->where('name', $name)
          ->execOne();
        $bySlug = Models\Board::getBySlug($name);
        return $bySlug instanceof Models\Board ? $bySlug : $byName;
    }

    /**
     * Get All Boards
     * @return Models\Board[]
     */
    public function getBoards()
    {
        return Models\Board::search()
          ->order('subscription_count', 'DESC')
          ->exec();
    }

    /**
     * @param Models\Board $board
     * @param Models\User  $user
     * @return Models\UserBoardLink
     */
    public function subscribeUser(Models\Board $board, Models\User $user)
    {
        $userBoardLink = new Models\UserBoardLink();
        $userBoardLink->user_id = $user->user_id;
        $userBoardLink->board_id = $board->board_id;
        $userBoardLink->save();
        $this->calculateSubscriptionCounts($board);
        return $userBoardLink;
    }

    /**
     * @param Models\Board $board
     * @return Models\Board
     */
    public function calculateSubscriptionCounts(Models\Board $board)
    {
        $board->subscription_count = Models\UserBoardLink::search()
            ->where("board_id", $board->board_id)
            ->where('deleted', 'No')
            ->count();
        $board->save();
        return $board;
    }

    /**
     * @param Models\Board $board
     * @return Models\Board
     */
    public function calculateThreadCounts(Models\Board $board)
    {
        $board->thread_count = Models\Thread::search()
            ->where("board_id", $board->board_id)
            ->where('deleted', 'No')
            ->count();
        $board->save();
        return $board;
    }
}
