<?php
namespace TigerKit\Services;

use TigerKit\Models;

class ThreadService extends BaseService
{

    /**
     * @param Models\Board $board
     * @param Models\User $user
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
        return $thread;
    }
}
