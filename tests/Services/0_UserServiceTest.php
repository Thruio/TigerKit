<?php

namespace TigerKit\Test\Services;

use TigerKit\Services\UserService;
use TigerKit\Models;
use TigerKit\Test\TigerBaseTest;

class UserServiceTest extends TigerBaseTest
{
    /**
 * @var UserService
*/
    private $userService;

    public function setUp()
    {
        parent::setUp();
        $this->userService = new UserService();
    }

    public function testCreateUser()
    {
        $user = $this->userService->createUser(
            $this->faker->userName,
            $this->faker->name(),
            $this->faker->password,
            $this->faker->safeEmail
        );
        $this->assertTrue($user instanceof Models\User);
        $this->assertGreaterThan(0, $user->user_id);
        $this->assertFalse($user->isAdmin());
        $user->delete();
    }

    public function testCreateAdmin()
    {
        $user = $this->userService->createUser(
            $this->faker->userName,
            $this->faker->name(),
            $this->faker->password,
            $this->faker->safeEmail
        );
        $user->type = "Admin";
        $user->save();
        $this->assertTrue($user instanceof Models\User);
        $this->assertGreaterThan(0, $user->user_id);
        $this->assertTrue($user->isAdmin());
        $user->delete();
    }

    public function testDoLogin()
    {
        $this->assertTrue($this->userService->doLogin($this->testUser->username, $this->testUserPassword));
        $this->assertTrue($this->userService->doLogin($this->testUser->email, $this->testUserPassword));
        $this->assertFalse($this->userService->doLogin($this->testUser->username, "bogus"));
        $this->assertFalse($this->userService->doLogin($this->testUser->email, "bogus"));
        $this->assertFalse($this->userService->doLogin("bogus", $this->testUserPassword));
    }

    /**
     * @expectedException \TigerKit\TigerException
     * @expectedExceptionMessage Passwords must be 6 or more characters long.
     */
    public function testMinimumPasswordLength()
    {
        $this->assertTrue(
            $this->userService->createUser(
                $this->faker->userName,
                $this->faker->name(),
                "short",
                $this->faker->email
            )
            instanceof Models\User
        );
    }


    /**
     * @expectedException \TigerKit\TigerException
     * @expectedExceptionMessageRegExp /Username (.+) already in use\./
     */
    public function testEmailMustBeUnique()
    {
        $existingUser = $this->userService->createUser(
            $this->faker->userName,
            $this->faker->name(),
            $this->faker->password,
            "{$this->faker->userName}@example.com"
        );

        $this->userService->createUser(
            $existingUser->username,
            $existingUser->displayname,
            $existingUser->password,
            $existingUser->email
        );
    }

    public function testEmailValid()
    {
        $this->assertTrue(
            $this->userService->createUser(
                $this->faker->userName,
                $this->faker->name(),
                $this->faker->password,
                "{$this->faker->userName}@example.com"
            )
            instanceof Models\User
        );
    }

    /**
     * @expectedException \TigerKit\TigerException
     * @expectedExceptionMessage notvalid is not a valid email address.
     */
    public function testEmailInValid()
    {
        $this->assertTrue(
            $this->userService->createUser(
                $this->faker->userName,
                $this->faker->name(),
                $this->faker->password,
                "notvalid"
            )
            instanceof Models\User
        );
    }
}
