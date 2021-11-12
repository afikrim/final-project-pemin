<?php

use Faker\Factory as Faker;
use Laravel\Lumen\Testing\DatabaseMigrations;

class AuthTest extends TestCase
{
    use DatabaseMigrations;

    private $faker;

    public function __construct()
    {
        parent::__construct("Auth Test Case");
        $this->faker = Faker::create();
    }

    public function testShouldReturn201SuccessfullyRegister()
    {
        $user = [
            'name' => $this->faker->name(),
            'email' => $this->faker->email(),
            'password' => $this->faker->password(),
        ];

        // Register new user
        $this->post('/auth/register', $user);

        // assertion
        $this->assertResponseStatus(201);
        $this->response->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'token',
            ],
        ]);
        $this->response->assertJsonPath('success', true);
        $this->response->assertJsonPath('data.token', 'wkwkwkw');
        $this->seeInDatabase('users', [
            'name' => $user['name'],
            'email' => $user['email'],
        ]);
    }

    public function testShouldReturn400EmailAlreadyExists()
    {
        $user = [
            'name' => $this->faker->name(),
            'email' => $this->faker->email(),
            'password' => $this->faker->password(),
        ];

        $user2 = [
            'name' => $this->faker->name(),
            'email' => $user['email'],
            'password' => $this->faker->password(),
        ];

        // Register new user
        $this->post('/auth/register', $user);
        // assertion
        $this->assertResponseStatus(201);
        $this->response->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'token',
            ],
        ]);
        $this->response->assertJsonPath('success', true);
        $this->seeInDatabase('users', [
            'name' => $user['name'],
            'email' => $user['email'],
        ]);

        // Register second user
        $this->post('/auth/register', $user2);
        // assertion
        $this->assertResponseStatus(400);
        $this->response->assertJsonStructure([
            'success',
            'message',
        ]);
        $this->response->assertJsonPath('success', false);
        $this->notSeeInDatabase('users', [
            'name' => $user2['name'],
            'email' => $user2['email'],
        ]);
    }

    public function testShouldReturn200SuccessfullyLogin()
    {
        $user = [
            'name' => $this->faker->name(),
            'email' => $this->faker->email(),
            'password' => $this->faker->password(),
        ];

        $credential = [
            'email' => $user['email'],
            'password' => $user['password'],
        ];

        // Register new user
        $this->post('/auth/register', $user);
        // assertion
        $this->assertResponseStatus(201);
        $this->response->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'token',
            ],
        ]);
        $this->response->assertJsonPath('success', true);
        $this->seeInDatabase('users', [
            'name' => $user['name'],
            'email' => $user['email'],
        ]);

        // Login a registered user
        $this->post('/auth/login', $credential);

        // assertion
        $this->assertResponseOk();
        $this->response->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'token',
            ],
        ]);
        $this->response->assertJsonPath('success', true);
    }

    public function testShouldReturn400CredentialNotMatch()
    {
        $user = [
            'name' => $this->faker->name(),
            'email' => $this->faker->email(),
            'password' => $this->faker->password(),
        ];

        $credential = [
            'email' => $user['email'],
            'password' => $user['password'],
        ];

        $wrongCredential = [
            'email' => $user['email'],
            'password' => $this->faker->password(),
        ];

        // Register new user
        $this->post('/auth/register', $user);
        // assertion
        $this->assertResponseStatus(201);
        $this->response->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'token',
            ],
        ]);
        $this->response->assertJsonPath('success', true);
        $this->seeInDatabase('users', [
            'name' => $user['name'],
            'email' => $user['email'],
        ]);

        // Login a registered user
        $this->post('/auth/login', $credential);
        // assertion
        $this->assertResponseStatus(200);
        $this->response->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'token',
            ],
        ]);
        $this->response->assertJsonPath('success', true);

        // Login a registered user
        $this->post('/auth/login', $wrongCredential);
        // assertion
        $this->assertResponseStatus(400);
        $this->response->assertJsonStructure([
            'success',
            'message',
        ]);
        $this->response->assertJsonPath('success', false);
    }

    public function testShouldReturn404EmailNotFound()
    {
        $user = [
            'name' => $this->faker->name(),
            'email' => $this->faker->email(),
            'password' => $this->faker->password(),
        ];

        $credential = [
            'email' => $user['email'],
            'password' => $user['password'],
        ];

        $wrongCredential = [
            'email' => $this->faker->email(),
            'password' => $this->faker->password(),
        ];

        // Register new user
        $this->post('/auth/register', $user);
        // assertion
        $this->assertResponseStatus(201);
        $this->response->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'token',
            ],
        ]);
        $this->response->assertJsonPath('success', true);
        $this->seeInDatabase('users', [
            'name' => $user['name'],
            'email' => $user['email'],
        ]);

        // Login a registered user
        $this->post('/auth/login', $credential);
        // assertion
        $this->assertResponseStatus(200);
        $this->response->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'token',
            ],
        ]);
        $this->response->assertJsonPath('success', true);

        // Login a registered user
        $this->post('/auth/login', $wrongCredential);
        // assertion
        $this->assertResponseStatus(404);
        $this->response->assertJsonStructure([
            'success',
            'message',
        ]);
        $this->response->assertJsonPath('success', false);
    }
}
