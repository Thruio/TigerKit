<?php

namespace TigerKit\Test\Models;

use TigerKit\Models\User;
use TigerKit\Test\TigerBaseTest;

class UserTest extends TigerBaseTest
{
    public function testPasswordRehash()
    {
        $oldHash = '$1$tc6CkLWy$4m956zmpQEUd5hvLEdNRC.'; //;
        $user = new User();
        $user->username = $this->faker->userName;
        $user->displayname = $this->faker->name();
        $user->password = $oldHash;
        $this->assertTrue($user->checkPassword('rasmuslerdorf'), "Check password worked from the old hash");
        $this->assertNotEquals($oldHash, $user->password, "Check the password hash had changed");
        $this->assertTrue($user->checkPassword('rasmuslerdorf'), "Check new hashed password works too.");
    }
}
