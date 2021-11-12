<?php

use App\Models\Book;
use App\Models\Transaction;
use App\Models\User;
use Faker\Factory as Faker;
use Firebase\JWT\JWT;
use Illuminate\Support\Facades\Hash;
use Laravel\Lumen\Testing\DatabaseMigrations;

class TransactionTest extends TestCase
{
    use DatabaseMigrations;

    private $faker;
    private $adminData;
    private $userData;
    private $booksData;
    private $transactionsData;

    public function __construct()
    {
        parent::__construct("Transaction Test Case");
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

    private function generateTransactions()
    {
        $transactions = [];

        for ($i = 0; $i < 5; $i += 1) {
            $user = new User([
                'name' => $this->faker->name(),
                'email' => $this->faker->email(),
                'password' => Hash::make($this->faker->password()),
                'role' => 'user',
            ]);
            $user->save();

            $transaction = new Transaction([
                'user_id' => $user->id,
                'book_id' => $this->booksData[$i]->id,
                'deadline' => date('Y-m-d H:i:s', time() + 7 * 24 * 60 * 60),
            ]);
            $transaction->save();

            $transactions[] = $transaction;
        }

        $this->transactionsData = $transactions;
    }

    private function beforeEach()
    {
        $this->generateAdmin();
        $this->generateUser();
        $this->generateBooks();
        $this->generateTransactions();
    }

    public function testShouldReturn200SuccessfullyGetAllTransactionsByAdmin()
    {
        $this->beforeEach();

        $this->get('/transactions', [
            'Authorization' => "Bearer {$this->adminData->token}",
        ]);

        $this->assertResponseOk();
        $this->response->assertJsonStructure(
            [
                'success',
                'message',
                'data' => [
                    'transactions' => [
                        '*' => [
                            'book' => [
                                'title',
                                'author',
                            ],
                            'deadline',
                            'created_at',
                            'updated_at',
                        ],
                    ],
                ],
            ]
        );
        $this->response->assertJsonPath('success', true);
        $this->response->assertJsonCount(count($this->transactionsData), 'data.transactions');
    }

    public function testShouldReturn200SuccessfullyGetTheirOwnTransactionsByUser()
    {
        $this->beforeEach();

        $transaction = new Transaction([
            'user_id' => $this->userData->id,
            'book_id' => $this->booksData[0]->id,
            'deadline' => date('Y-m-d H:i:s', time() + 7 * 24 * 60 * 60),
        ]);
        $transaction->save();

        $this->get('/transactions', [
            'Authorization' => "Bearer {$this->userData->token}",
        ]);

        $this->assertResponseOk();
        $this->response->assertJsonStructure(
            [
                'success',
                'message',
                'data' => [
                    'transactions' => [
                        '*' => [
                            'book' => [
                                'title',
                                'author',
                            ],
                            'deadline',
                            'created_at',
                            'updated_at',
                        ],
                    ],
                ],
            ]
        );
        $this->response->assertJsonPath('success', true);
        $this->response->assertJsonCount(1, 'data.transactions');
    }

    public function testShouldReturn401UnAuthorizedGetAllTransactionsWithoutToken()
    {
        $this->beforeEach();

        $this->get('/transactions');

        $this->assertResponseStatus(401);
        $this->response->assertJsonStructure(
            [
                'success',
                'message',
            ]
        );
        $this->response->assertJsonPath('success', false);
    }

    public function testShouldReturn201SuccessfullyInsertATransactionByUser()
    {
        $this->beforeEach();

        $this->post(
            '/transactions',
            [
                'book_id' => $this->booksData[0]->id,
                'user_id' => $this->userData->id,
            ],
            [
                'Authorization' => "Bearer {$this->userData->token}",
            ]
        );

        $this->assertResponseStatus(201);
        $this->response->assertJsonStructure(
            [
                'success',
                'message',
                'data' => [
                    'transaction' => [
                        'book' => [
                            'title',
                            'author',
                        ],
                        'deadline',
                        'created_at',
                        'updated_at',
                    ],
                ],
            ]
        );
        $this->response->assertJsonPath('success', true);
        $this->response->assertJsonPath('data.transaction.book.title', $this->booksData[0]->title);
    }

    public function testShouldReturn401UnAuthorizedInsertATransactionWithoutToken()
    {
        $this->beforeEach();

        $this->post(
            '/transactions',
            [
                'book_id' => $this->booksData[0]->id,
                'user_id' => $this->userData->id,
            ]
        );

        $this->assertResponseStatus(401);
        $this->response->assertJsonStructure(
            [
                'success',
                'message',
            ]
        );
        $this->response->assertJsonPath('success', false);
    }

    public function testShouldReturn403ForbiddenInsertATransactionByAdmin()
    {
        $this->beforeEach();

        $this->post(
            '/transactions',
            [
                'book_id' => $this->booksData[0]->id,
                'user_id' => $this->userData->id,
            ],
            [
                'Authorization' => "Bearer {$this->adminData->token}",
            ]
        );

        $this->assertResponseStatus(403);
        $this->response->assertJsonStructure(
            [
                'success',
                'message',
            ]
        );
        $this->response->assertJsonPath('success', false);
    }

    public function testShouldReturn200SuccessfullyGetATransactionByAdmin()
    {
        $this->beforeEach();

        $this->get("/transactions/{$this->transactionsData[0]->id}", [
            'Authorization' => "Bearer {$this->adminData->token}",
        ]);

        $this->assertResponseOk();
        $this->response->assertJsonStructure(
            [
                'success',
                'message',
                'data' => [
                    'transaction' => [
                        'book' => [
                            'title',
                            'author',
                        ],
                        'deadline',
                        'created_at',
                        'updated_at',
                    ],
                ],
            ]
        );
        $this->response->assertJsonPath('success', true);
        $this->response->assertJsonPath('data.transaction.book.title', $this->booksData[0]->title);
    }

    public function testShouldReturn200SuccessfullyGetATransactionByUser()
    {
        $this->beforeEach();

        $transaction = new Transaction([
            'user_id' => $this->userData->id,
            'book_id' => $this->booksData[0]->id,
            'deadline' => date('Y-m-d H:i:s', time() + 7 * 24 * 60 * 60),
        ]);
        $transaction->save();

        $this->get("/transactions/{$transaction->id}", [
            'Authorization' => "Bearer {$this->userData->token}",
        ]);

        $this->assertResponseOk();
        $this->response->assertJsonStructure(
            [
                'success',
                'message',
                'data' => [
                    'transaction' => [
                        'book' => [
                            'title',
                            'author',
                        ],
                        'deadline',
                        'created_at',
                        'updated_at',
                    ],
                ],
            ]
        );
        $this->response->assertJsonPath('success', true);
        $this->response->assertJsonPath('data.transaction.book.title', $this->booksData[0]->title);
    }

    public function testShouldReturn401UnAuthorizedGetATransactionWithoutToken()
    {
        $this->beforeEach();

        $this->get("/transactions");

        $this->assertResponseStatus(401);
        $this->response->assertJsonStructure(
            [
                'success',
                'message',
            ]
        );
        $this->response->assertJsonPath('success', false);
    }

    public function testShouldReturn403ForbiddenGetOtherUserTransaction()
    {
        $this->beforeEach();

        $this->get("/transactions/{$this->transactionsData[0]->id}", [
            'Authorization' => "Bearer {$this->userData->token}",
        ]);

        $this->assertResponseStatus(403);
        $this->response->assertJsonStructure(
            [
                'success',
                'message',
            ]
        );
        $this->response->assertJsonPath('success', false);
    }

    public function testShouldReturn200SuccessfullyUpdateATransactionByAdmin()
    {
        $this->beforeEach();

        $this->put(
            "/transactions/{$this->transactionsData[0]->id}",
            [
                'deadline' => null,
            ],
            [
                'Authorization' => "Bearer {$this->adminData->token}",
            ]
        );

        $this->assertResponseStatus(200);
        $this->response->assertJsonStructure(
            [
                'success',
                'message',
                'data' => [
                    'transaction' => [
                        'book' => [
                            'title',
                            'author',
                        ],
                        'deadline',
                        'created_at',
                        'updated_at',
                    ],
                ],
            ]
        );
        $this->response->assertJsonPath('success', true);
        $this->response->assertJsonPath('data.transaction.book.title', $this->booksData[0]->title);
        $this->response->assertJsonPath('data.transaction.book.deadline', null);
    }

    public function testShouldReturn401UnAuthorizedUpdateATransactionWithoutToken()
    {
        $this->beforeEach();

        $this->put(
            "/transactions/{$this->transactionsData[0]->id}",
            [
                'deadline' => null,
            ]
        );

        $this->assertResponseStatus(401);
        $this->response->assertJsonStructure(
            [
                'success',
                'message',
            ]
        );
        $this->response->assertJsonPath('success', false);
    }

    public function testShouldReturn403ForbiddenUpdateATransactionByUser()
    {
        $this->beforeEach();

        $transaction = new Transaction([
            'user_id' => $this->userData->id,
            'book_id' => $this->booksData[0]->id,
            'deadline' => date('Y-m-d H:i:s', time() + 7 * 24 * 60 * 60),
        ]);
        $transaction->save();

        $this->put(
            "/transactions/{$transaction->id}",
            [
                'deadline' => null,
            ],
            [
                'Authorization' => "Bearer {$this->userData->token}",
            ]
        );

        $this->assertResponseStatus(403);
        $this->response->assertJsonStructure(
            [
                'success',
                'message',
            ]
        );
        $this->response->assertJsonPath('success', false);
    }
}
