<?php

namespace TigerKit\Test\Services;

use TigerKit\Models;
use TigerKit\Services\BoardService;
use TigerKit\Services\ThreadService;
use TigerKit\Services\ImageService;
use TigerKit\Services\CommentService;
use TigerKit\Services\UserService;
use TigerKit\Test\TigerBaseTest;

class BoardServiceTest extends TigerBaseTest
{
    /** @var BoardService */
    private $boardService;
    /** @var ThreadService */
    private $threadService;
    /** @var UserService */
    private $userService;
    /** @var CommentService */
    private $commentService;

    /** @var Models\User[] */
    private $boardUserPool;

    private static $startTime;
    private static $endTime;

    public function setUp()
    {
        parent::setUp();
        $this->boardService = new BoardService();
        $this->userService = new UserService();
        $this->commentService = new CommentService();
        $this->threadService = new ThreadService();
        for ($i = 0; $i < 5; $i++) {
            $this->boardUserPool[] = $this->userService->createUser($this->faker->userName, $this->faker->name(), $this->faker->password, $this->faker->safeEmail);
        }
    }
    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        self::$startTime = microtime(true);
    }

    public static function tearDownAfterClass()
    {
        parent::tearDownAfterClass();
        self::$endTime = microtime(true);
        echo "Ran BoardServiceTest in " . number_format(self::$endTime - self::$startTime, 2) . " seconds\n";
    }

    /**
     * @return Models\User
     */
    private function randomBoardPoolUser()
    {
        shuffle($this->boardUserPool);
        return $this->boardUserPool[0];
    }

    public function testCreateNewBoard()
    {
        $boardName = $this->faker->company;
        $user = $this->randomBoardPoolUser();
        $board = $this->boardService->createBoard($boardName, $user);
        $this->assertTrue($board instanceof Models\Board);
        $this->assertEquals($boardName, $board->name);
        $this->assertEquals($user, $board->getCreatedUser());
        return $board;
    }
    /**
     * @depends testCreateNewBoard
     * @param Models\Board $board
     */
    public function testBoardSubscribe(Models\Board $board)
    {
        $this->assertEquals(0, $board->subscription_count);
        $this->boardService->subscribeUser($board, $this->randomBoardPoolUser());
        $this->assertEquals(1, $board->subscription_count);
    }

    /**
     * @depends testCreateNewBoard
     * @param Models\Board $board
     * @return Models\Thread[]
     */
    public function testCreateThreads(Models\Board $board)
    {
        $threads = [];
        for ($i = 1; $i <= 15; $i++) {
            $user = $this->randomBoardPoolUser();
            $title = $this->faker->catchPhrase;
            $content = $this->faker->boolean(50)?$this->faker->url:implode("\n\n", $this->faker->paragraphs(rand(1, 5)));
            $threads[$i] = $this->threadService->createThread($board, $user, $title, $content);
        }
        return $threads;
    }

    /**
     * @depends testCreateThreads
     * @param $threads
     * @returns array Array of Arrays of comments.
     */
    public function testCreateComments($threads)
    {
        $comments = [];
        foreach ($threads as $threadId => $thread) {
            $count = rand(1, 15);
            for ($i = 1; $i <= $count; $i++) {
                $user = $this->randomBoardPoolUser();
                $comment = $this->commentService->addCommentToThread($thread, $user, implode("\n\n", $this->faker->paragraphs(rand(1, 5))));
                $this->assertTrue($comment instanceof Models\Comment);
                $comments[$threadId][$i] = $comment;
            }
        }
        return $comments;
    }

    /**
     * @depends testCreateComments
     * @param $comments
     */
    public function testCreateSubcomments($comments)
    {
        foreach ($comments as $threadId => $commentsInThread) {
            foreach ($commentsInThread as $comment) {
                /** @var $comment Models\Comment */
                $this->assertTrue($comment instanceof Models\Comment);
            }
        }
    }
}
