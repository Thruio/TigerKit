<?php
namespace TigerKit\Services;

use TigerKit\Models;

class ThreadService extends BaseService
{
    /**
     * @var $boardService BoardService
*/
    public $boardService;

    public function __construct()
    {
        $this->boardService = new BoardService();
    }

    /**
     * @param Models\Board $board
     * @param Models\User  $user
     * @param $title
     * @param $contentOrUrl
     * @return Models\Thread
     */
    public function createThread(Models\Board $board, Models\User $user, $title, $contentOrUrl)
    {
        $thread = new Models\Thread();
        $thread->created_user_id = $user->user_id;
        $thread->title = $title;
        $thread->board_id = $board->board_id;
        if (!filter_var($contentOrUrl, FILTER_VALIDATE_URL) === false) {
            $thread->url = $contentOrUrl;
        } else {
            $thread->body = $contentOrUrl;
        }
        $thread->save();
        $this->boardService->calculateThreadCounts($board);
        return $thread;
    }

    /**
     * @param Models\Board $board
     * @return Models\Thread[]
     */
    public function getThreads(Models\Board $board)
    {
        $threads = Models\Thread::search()->where('board_id', $board->board_id)->where('deleted', 'No')->exec();
        return $threads;
    }
}
