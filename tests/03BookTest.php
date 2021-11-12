<?php

use App\Models\Book;
use App\Models\User;
use Faker\Factory as Faker;
use Firebase\JWT\JWT;
use Illuminate\Support\Facades\Hash;
use Laravel\Lumen\Testing\DatabaseMigrations;

class BookTest extends TestCase
{
    use DatabaseMigrations;

    private $faker;
    private $adminData;
    private $userData;
    private $booksData;

    public function __construct()
    {
        parent::__construct("Book Test Case");
        $this->faker = Faker::create();

        // set reserved memory
        ini_set('memory_limit', '512M');
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

    private function generateBooks()
    {
        $books = [];

        for ($i = 0; $i < 10; $i += 1) {
            $book = new Book([
                'title' => $this->faker->title(),
                'description' => $this->faker->realText(500),
                'author' => $this->faker->name(),
                'year' => $this->faker->year(),
                'synopsis' => $this->faker->realText(500),
                'stock' => $this->faker->randomNumber(),
            ]);
            $book->save();

            $books[] = $book;
        }

        $this->booksData = $books;
    }

    private function beforeEach()
    {
        $this->generateAdmin();
        $this->generateUser();
        $this->generateBooks();
    }

    public function testShouldReturn200SuccessfullyGetAllBooksByAnyone()
    {
        $this->beforeEach();

        $this->get("/books");

        // assertions
        $this->assertResponseOk();
        $this->response->assertJsonStructure([
            'success',
            'message',
            'data' => [
                '*' => [
                    'id',
                    'title',
                    'description',
                    'author',
                    'year',
                    'synopsis',
                    'stock',
                    '*',
                ],
            ],
        ]);
        $this->response->assertJsonPath('success', true);
        $this->response->assertJsonCount(count($this->booksData), 'data');
    }

    public function testShouldReturn201SuccessfullyInsertABookByAdmin()
    {
        $this->beforeEach();

        $newBook = [
            'title' => $this->faker->title(),
            'description' => $this->faker->realText(500),
            'author' => $this->faker->name(),
            'year' => $this->faker->year(),
            'synopsis' => $this->faker->realText(500),
            'stock' => $this->faker->randomNumber(),
        ];

        $this->post(
            "/books",
            $newBook,
            [
                'Authorization' => "Bearer {$this->adminData->token}",
            ]
        );

        // assertions
        $this->assertResponseStatus(201);
        $this->response->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'id',
                'title',
                'description',
                'author',
                'year',
                'synopsis',
                'stock',
                '*',
            ],
        ]);
        $this->response->assertJsonPath('success', true);
        $this->seeInDatabase('books', $newBook);

        $this->get("/books");

        // assertions
        $this->assertResponseOk();
        $this->response->assertJsonStructure([
            'success',
            'message',
            'data' => [
                '*' => [
                    'id',
                    'title',
                    'description',
                    'author',
                    'year',
                    'synopsis',
                    'stock',
                    '*',
                ],
            ],
        ]);
        $this->response->assertJsonPath('success', true);
        $this->response->assertJsonCount(count($this->booksData) + 1, 'data');
    }

    public function testShouldReturn401UnAuthorizedInsertABookWithoutToken()
    {
        $this->beforeEach();

        $this->post(
            "/books",
            [
                'title' => $this->faker->title(),
                'description' => $this->faker->realText(500),
                'author' => $this->faker->name(),
                'year' => $this->faker->year(),
                'synopsis' => $this->faker->realText(500),
                'stock' => $this->faker->randomNumber(),
            ]
        );

        // assertions
        $this->assertResponseStatus(401);
        $this->response->assertJsonStructure([
            'success',
            'message',
        ]);
        $this->response->assertJsonPath('success', true);
    }

    public function testShouldReturn403ForbiddenInsertABookByOtherThanAdmin()
    {
        $this->beforeEach();

        $this->post(
            "/books",
            [
                'title' => $this->faker->title(),
                'description' => $this->faker->realText(500),
                'author' => $this->faker->name(),
                'year' => $this->faker->year(),
                'synopsis' => $this->faker->realText(500),
                'stock' => $this->faker->randomNumber(),
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
        $this->response->assertJsonPath('success', true);
    }

    public function testShouldReturn200SuccessfullyGetABookByAnyone()
    {
        $this->beforeEach();

        $this->get("/books/{$this->booksData[0]->id}");

        // assertions
        $this->assertResponseOk();
        $this->response->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'id',
                'title',
                'description',
                'author',
                'year',
                'synopsis',
                'stock',
                '*',
            ],
        ]);
        $this->response->assertJsonPath('success', true);
    }

    public function testShouldReturn200SuccessfullyUpdateABookByAdmin()
    {
        $this->beforeEach();

        $newTitle = $this->faker->title();
        $newDescription = $this->faker->realText(500);
        $newSynopsis = $this->faker->realText(500);

        $this->put(
            "/books/{$this->booksData[0]->id}",
            [
                'title' => $newTitle,
                'description' => $newDescription,
                'synopsis' => $newSynopsis,
            ],
            [
                'Authorization' => "Bearer {$this->adminData->token}",
            ]
        );

        // assertions
        $this->assertResponseOk();
        $this->response->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'id',
                'title',
                'description',
                'author',
                'year',
                'synopsis',
                'stock',
                '*',
            ],
        ]);
        $this->response->assertJsonPath('success', true);
    }

    public function testShouldReturn401UnAuthorizedUpdateABookWithoutToken()
    {
        $this->beforeEach();

        $newTitle = $this->faker->title();
        $newDescription = $this->faker->realText(500);
        $newSynopsis = $this->faker->realText(500);

        $this->put(
            "/books/{$this->booksData[0]->id}",
            [
                'title' => $newTitle,
                'description' => $newDescription,
                'synopsis' => $newSynopsis,
            ],
        );

        // assertions
        $this->assertResponseStatus(401);
        $this->response->assertJsonStructure([
            'success',
            'message',
        ]);
        $this->response->assertJsonPath('success', false);
    }

    public function testShouldReturn403forbiddenUpdateABookByUser()
    {
        $this->beforeEach();

        $newTitle = $this->faker->title();
        $newDescription = $this->faker->realText(500);
        $newSynopsis = $this->faker->realText(500);

        $this->put(
            "/books/{$this->booksData[0]->id}",
            [
                'title' => $newTitle,
                'description' => $newDescription,
                'synopsis' => $newSynopsis,
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

    public function testShouldReturn200SuccessfullyDeleteABookByAdmin()
    {
        $this->beforeEach();

        $this->put(
            "/books/{$this->booksData[0]->id}",
            [
                'Authorization' => "Bearer {$this->adminData->token}",
            ]
        );

        // assertions
        $this->assertResponseOk();
        $this->response->assertJsonStructure([
            'success',
            'message',
        ]);
        $this->response->assertJsonPath('success', true);
    }

    public function testShouldReturn401UnAuthorizedDeleteABookWithoutToken()
    {
        $this->beforeEach();

        $this->put(
            "/books/{$this->booksData[0]->id}",
        );

        // assertions
        $this->assertResponseStatus(401);
        $this->response->assertJsonStructure([
            'success',
            'message',
        ]);
        $this->response->assertJsonPath('success', false);
    }

    public function testShouldReturn403forbiddenDeleteABookByUser()
    {
        $this->beforeEach();

        $this->put(
            "/books/{$this->booksData[0]->id}",
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
