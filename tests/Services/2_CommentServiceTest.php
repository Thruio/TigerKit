<?php

namespace TigerKit\Test\Services;

use TigerKit\Models\ImageCommentLink;
use TigerKit\Models\User;
use TigerKit\Models\Comment;
use TigerKit\Services\ImageService;
use TigerKit\Services\CommentService;
use TigerKit\Services\UserService;
use TigerKit\Test\TigerBaseTest;

class CommentServiceTest extends TigerBaseTest
{
  /** @var ImageService */
    private $imageService;
  /** @var UserService */
    private $userService;
  /** @var CommentService */
    private $commentService;

  /** @var User[] */
    private $commentUserPool;

    public function setUp()
    {
        parent::setUp();
        $this->imageService = new ImageService();
        $this->userService = new UserService();
        $this->commentService = new CommentService();
        for ($i = 0; $i < 5; $i++) {
            $this->commentUserPool[] = $this->userService->createUser($this->faker->userName, $this->faker->name(), $this->faker->password, $this->faker->safeEmail);
        }
    }

  /**
   * @return User
   */
    private function randomCommentPoolUser()
    {
        shuffle($this->commentUserPool);
        return $this->commentUserPool[0];
    }

    public function testAddCommentToImage()
    {
        $image = $this->imageService->getRandomImage();
        $comment = new Comment();
        $comment->comment = $this->faker->text(rand(100, 500));
        $comment->created_user_id = $this->randomCommentPoolUser()->user_id;
        $this->assertTrue($this->commentService->addCommentToImage($comment, $image) instanceof ImageCommentLink);

        $this->assertEquals(1, count($this->commentService->getComments($image)));
    }
}
