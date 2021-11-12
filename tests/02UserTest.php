<?php

use App\Models\User;
use Faker\Factory as Faker;
use Firebase\JWT\JWT;
use Illuminate\Support\Facades\Hash;
use Laravel\Lumen\Testing\DatabaseMigrations;

class UserTest extends TestCase
{
    use DatabaseMigrations;

    private $faker;
    private $adminData;
    private $userData;

    public function __construct()
    {
        parent::__construct("User Test Case");
        $this->faker = Faker::create();
    }

    private function generateAdmin()
    {
        $this->adminData = new User([
            'name' => 'Administrator',
            'email' => 'admin@localhost',
            'password' => Hash::make('secret123'),
            'role' => 'admin',
        ]);
        $this->adminData->save();

        $this->adminData->token = JWT::encode(
            [
                'sub' => 'admin@localhost',
                'iss' => 'http://localhost:8080',
                'aud' => 'http://localhost:8080',
                'iat' => time(),
                'exp' => time() + 60 * 60,
                'role' => 'admin',
            ],
            env('JWT_KEY', 'secret'),
            'HS256'
        );
    }

    private function generateUser()
    {
        $this->userData = new User([
            'name' => $this->faker->name(),
            'email' => $this->faker->email(),
            'password' => Hash::make($this->faker->password()),
            'role' => 'user',
        ]);
        $this->userData->save();

        $this->userData->token = JWT::encode(
            [
                'sub' => $this->userData->email,
                'iss' => 'http://localhost:8080',
                'aud' => 'http://localhost:8080',
                'iat' => time(),
                'exp' => time() + 60 * 60,
                'role' => 'user',
            ],
            env('JWT_KEY', 'secret'),
            'HS256'
        );
    }

    private function beforeEach()
    {
        $this->generateAdmin();
        $this->generateUser();
    }

    public function testShouldReturn200SuccessfullyGetAllUsersByAdmin()
    {
        $this->beforeEach();

        // get all users
        $this->get('/users', [
            'Authorization' => "Bearer {$this->adminData->token}",
        ]);

        // assertions
        $this->assertResponseStatus(200);
        $this->response->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'users' => [
                    '*' => [
                        'id',
                        'name',
                        'email',
                    ],
                ],
            ],
        ]);
        $this->response->assertJsonPath('success', true);
    }

    public function testShouldReturn401UnauhtorizedGetAllUsersWithoutAuthorization()
    {
        $this->beforeEach();

        // get all users
        $this->get('/users');

        // assertions
        $this->assertResponseStatus(401);
        $this->response->assertJsonStructure([
            'success',
            'message',
        ]);
        $this->response->assertJsonPath('success', false);
    }

    public function testShouldReturn403ForbiddenGetAllUsersByUser()
    {
        $this->beforeEach();

        // get all users
        $this->get('/users', [
            'Authorization' => "Bearer {$this->userData->token}",
        ]);

        // assertions
        $this->assertResponseStatus(403);
        $this->response->assertJsonStructure([
            'success',
            'message',
        ]);
        $this->response->assertJsonPath('success', false);
    }

    public function testShouldReturn200SuccessfullyGetAUserByAdmin()
    {
        $this->beforeEach();

        // get all users
        $this->get("/users/{$this->userData->id}", [
            'Authorization' => "Bearer {$this->adminData->token}",
        ]);

        // assertions
        $this->assertResponseOk();
        $this->response->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'user' => [
                    'id',
                    'name',
                    'email',
                ],
            ],
        ]);
        $this->response->assertJsonPath('success', true);
    }

    public function testShouldReturn200SuccessfullyGetThemselfByUser()
    {
        $this->beforeEach();

        // get all users
        $this->get("/users/{$this->userData->id}", [
            'Authorization' => "Bearer {$this->userData->token}",
        ]);

        // assertions
        $this->assertResponseOk();
        $this->response->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'user' => [
                    'id',
                    'name',
                    'email',
                ],
            ],
        ]);
        $this->response->assertJsonPath('success', true);
    }

    public function testShouldReturn401UnauhtorizedGetAUserWithoutAuthorization()
    {
        $this->beforeEach();

        // get all users
        $this->get("/users/{$this->userData->id}");

        // assertions
        $this->assertResponseStatus(401);
        $this->response->assertJsonStructure([
            'success',
            'message',
        ]);
        $this->response->assertJsonPath('success', false);
    }

    public function testShouldReturn403ForbiddenGetOtherUserByUser()
    {
        $this->beforeEach();

        // get all users
        $this->get("/users/{$this->adminData->id}", [
            'Authorization' => "Bearer {$this->userData->token}",
        ]);

        // assertions
        $this->assertResponseStatus(403);
        $this->response->assertJsonStructure([
            'success',
            'message',
        ]);
        $this->response->assertJsonPath('success', false);
    }

    public function testShouldReturn404GetAnUndefinedUserByAdmin()
    {
        $this->beforeEach();

        // get all users
        $this->get("/users/100", [
            'Authorization' => "Bearer {$this->adminData->token}",
        ]);

        // assertions
        $this->assertResponseStatus(404);
        $this->response->assertJsonStructure([
            'success',
            'message',
        ]);
        $this->response->assertJsonPath('success', false);
    }

    public function testShouldReturn200SuccessfullyUpdateThemselfByAdmin()
    {
        $this->beforeEach();
        $newName = $this->faker->name();

        // get all users
        $this->put(
            "/users/{$this->adminData->id}",
            [
                "name" => $newName,
            ],
            [
                'Authorization' => "Bearer {$this->adminData->token}",
            ]
        );

        // assertions
        $this->assertResponseStatus(200);
        $this->response->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'user' => [
                    'id',
                    'name',
                    'email',
                ],
            ],
        ]);
        $this->response->assertJsonPath('success', true);
        $this->response->assertJsonPath('data.user.name', $newName);
    }

    public function testShouldReturn200SuccessfullyUpdateThemselfByUser()
    {
        $this->beforeEach();
        $newName = $this->faker->name();

        // get all users
        $this->put(
            "/users/{$this->userData->id}",
            [
                "name" => $newName,
            ],
            [
                'Authorization' => "Bearer {$this->userData->token}",
            ]
        );

        // assertions
        $this->assertResponseStatus(200);
        $this->response->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'user' => [
                    'id',
                    'name',
                    'email',
                ],
            ],
        ]);
        $this->response->assertJsonPath('success', true);
        $this->response->assertJsonPath('data.user.name', $newName);
    }

    public function testShouldReturn401UnauhtorizedUpdateAUserWithoutAuthorization()
    {
        $this->beforeEach();

        // get all users
        $this->put("/users/{$this->userData->id}", [
            'name' => $this->faker->name(),
        ]);

        // assertions
        $this->assertResponseStatus(401);
        $this->response->assertJsonStructure([
            'success',
            'message',
        ]);
        $this->response->assertJsonPath('success', false);
    }

    public function testShouldReturn403ForbiddenUpdateOtherUserByAdmin()
    {
        $this->beforeEach();
        $newName = $this->faker->name();

        // get all users
        $this->put(
            "/users/{$this->userData->id}",
            [
                "name" => $newName,
            ],
            [
                'Authorization' => "Bearer {$this->adminData->token}",
            ]
        );

        // assertions
        $this->assertResponseStatus(403);
        $this->response->assertJsonStructure([
            'success',
            'message',
        ]);
        $this->response->assertJsonPath('success', false);
    }

    public function testShouldReturn403ForbiddenUpdateOtherUserByUser()
    {
        $this->beforeEach();
        $newName = $this->faker->name();

        // get all users
        $this->put(
            "/users/{$this->adminData->id}",
            [
                "name" => $newName,
            ],
            [
                'Authorization' => "Bearer {$this->userData->token}",
            ]
        );

        // assertions
        $this->assertResponseStatus(403);
        $this->response->assertJsonStructure([
            'success',
            'message',
        ]);
        $this->response->assertJsonPath('success', false);
    }

    public function testShouldReturn200SuccessfullyDeleteThemselfByAdmin()
    {
        $this->beforeEach();

        // get all users
        $this->delete(
            "/users/{$this->adminData->id}",
            [],
            [
                'Authorization' => "Bearer {$this->adminData->token}",
            ]
        );

        // assertions
        $this->assertResponseStatus(200);
        $this->response->assertJsonStructure([
            'success',
            'message',
        ]);
        $this->response->assertJsonPath('success', true);
    }

    public function testShouldReturn200SuccessfullyDeleteThemselfByUser()
    {
        $this->beforeEach();

        // get all users
        $this->delete(
            "/users/{$this->userData->id}",
            [],
            [
                'Authorization' => "Bearer {$this->userData->token}",
            ]
        );

        // assertions
        $this->assertResponseStatus(200);
        $this->response->assertJsonStructure([
            'success',
            'message',
        ]);
        $this->response->assertJsonPath('success', true);
    }

    public function testShouldReturn401UnauhtorizedDeleteAUserWithoutAuthorization()
    {
        $this->beforeEach();

        // get all users
        $this->delete("/users/{$this->userData->id}");

        // assertions
        $this->assertResponseStatus(401);
        $this->response->assertJsonStructure([
            'success',
            'message',
        ]);
        $this->response->assertJsonPath('success', false);
    }

    public function testShouldReturn403ForbiddenDeleteOtherUserByAdmin()
    {
        $this->beforeEach();

        // get all users
        $this->delete(
            "/users/{$this->userData->id}",
            [],
            [
                'Authorization' => "Bearer {$this->adminData->token}",
            ]
        );

        // assertions
        $this->assertResponseStatus(403);
        $this->response->assertJsonStructure([
            'success',
            'message',
        ]);
        $this->response->assertJsonPath('success', false);
    }

    public function testShouldReturn403ForbiddenDeleteOtherUserByUser()
    {
        $this->beforeEach();

        // get all users
        $this->delete(
            "/users/{$this->adminData->id}",
            [],
            [
                'Authorization' => "Bearer {$this->userData->token}",
            ]
        );

        // assertions
        $this->assertResponseStatus(403);
        $this->response->assertJsonStructure([
            'success',
            'message',
        ]);
        $this->response->assertJsonPath('success', false);
    }
}
