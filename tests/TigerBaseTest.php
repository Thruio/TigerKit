<?php

namespace TigerKit\Test;

use Slim\Slim;
use Slim\Environment;
use Slim\Http\Response;
use TigerKit\TigerApp;
use Faker;
use TigerKit\Models\User;

abstract class TigerBaseTest extends \PHPUnit_Framework_TestCase {
  /** @var TigerApp */
  protected $tiger;
  /** @var Faker\Factory */
  protected $faker;

  protected $testUserUsername;
  protected $testUserPassword;
  /** @var User */
  protected $testUser;

  public function setUp()
  {
    $this->tiger = TigerApp::run();
    parent::setUp();
    $_SESSION = array();

    // Initialise Faker
    $this->faker = Faker\Factory::create();
    $this->faker->addProvider(new Faker\Provider\en_US\Person($this->faker));
    $this->faker->addProvider(new Faker\Provider\en_US\Address($this->faker));
    $this->faker->addProvider(new Faker\Provider\en_US\PhoneNumber($this->faker));
    $this->faker->addProvider(new Faker\Provider\en_US\Company($this->faker));
    $this->faker->addProvider(new Faker\Provider\Lorem($this->faker));
    $this->faker->addProvider(new Faker\Provider\Internet($this->faker));

    // Create Test user.
    $this->testUserUsername = $this->faker->userName;
    $this->testUserPassword = $this->faker->password;
    $this->testUser = new User();
    $this->testUser->username = $this->testUserUsername;
    $this->testUser->displayname = $this->faker->name();
    $this->testUser->email = $this->faker->safeEmail;
    $this->testUser->setPassword($this->testUserPassword);
    $this->testUser->save();
  }

  public function tearDown(){
    if(isset($this->testUser) && $this->testUser instanceof User) {
      $this->testUser->delete();
    }
  }
}
