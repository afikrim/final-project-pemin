## Projek Akhir PemIn

### How to run it?

1. Download this repository, you can download it by cloning it or download as a zip
2. If you are using zip, then extract your file first
3. Prepare your database, and update your credentials in .env, also don't forget to update the APP_KEY and add JWT_KEY if you want
4. Try to search something with keyword 'TODO: '. There are todos that you have to solve (ah... There is some spot where I forgot to add the 'TODO: ' comment, please search it on your own ^^)
5. After you think you done your task. Test your application by running this command `composer run unittest`
6. If you want to run a spesific test case, you can go to `tests` folder and choose whatever file you want to try, grab a function name or it's class name, then run `composer run unittest` with flag `--filter`. It will look like this,

    ```sh
    composer run unittest -- --filter=AuthTest
    ```

7. At the bottom, after the test is done, the summary will show. You can try to calculate your point by dividing `Tests` with `Tests - Failures`
8. I think that's all, you can ask us with this project anytime you want. We will kindly answer it! :D

### Entities

-   User

    | attribute | datatype | constraint |
    | --------- | -------- | ---------- |
    | id        | int      | pk         |
    | name      | string   |            |
    | email     | string   | unique     |
    | password  | string   |            |

-   Book

    | attribute   | datatype | constraint |
    | ----------- | -------- | ---------- |
    | id          | int      | pk         |
    | title       | string   |            |
    | description | string   |            |
    | author      | string   |            |
    | year        | string   |            |
    | synopsis    | string   |            |
    | stock       | string   |            |

-   Transaction

    | attribute | datatype     | constraint |
    | --------- | ------------ | ---------- |
    | id        | int          | pk         |
    | book_id   | int unsigned |            |
    | user_id   | int unsigned |            |
    | deadline  | date         |            |

### API Spec

```
POST /auth/register
request:
    - body:
        - name: string
        - email: string
        - password: string
response:
    - 201:
        - success: true
        - message: string
        - data:
            - token: string
    - 4xx:
        - success: false,
        - message: string
    - 5xx:
        - success: false,
        - message: Terjadi kesalahan pada server.

POST /auth/login
request:
    - body:
        - email: string
        - password: string
response:
    - 200:
        - success: true
        - message: string
        - data:
            - token: string
    - 4xx:
        - success: false,
        - message: string
    - 5xx:
        - success: false,
        - message: Terjadi kesalahan pada server.


GET /users [admin only]
request:
    - headers:
        - authorization: string
response:
    - 200:
        - success: true
        - message: string
        - data:
            - users: array of object
                - id: int
                - name: string
                - email: string
    - 4xx:
        - success: false,
        - message: string
    - 5xx:
        - success: false,
        - message: Terjadi kesalahan pada server.

GET /users/{userId} [admin can get every user, but a user can only get themself]
request:
    - headers:
        - authorization: string
response:
    - 200:
        - success: true
        - message: string
        - data:
            - user:
                - id: int
                - name: string
                - email: string
    - 4xx:
        - success: false,
        - message: string
    - 5xx:
        - success: false,
        - message: Terjadi kesalahan pada server.

PUT /users/{userId} [user only]
request:
    - headers:
        - authorization: string
    - body:
        - name: string
        - email: string
        - password: string
response:
    - 200:
        - success: true
        - message: string
        - data:
            - user:
                - id: int
                - name: string
                - email: string
    - 4xx:
        - success: false,
        - message: string
    - 5xx:
        - success: false,
        - message: Terjadi kesalahan pada server.

DELETE /users/{userId} [Soft Delete, user only]
request:
    - headers:
        - authorization: string
response:
    - 200:
        - success: true
        - message: string
    - 4xx:
        - success: false,
        - message: string
    - 5xx:
        - success: false,
        - message: Terjadi kesalahan pada server.


GET /books
request:
    -
response:
    - 200:
        - success: true
        - message: string
        - data:
            - books: array of object
                - id: int
                - title: string
                - description: string
                - author: string
                - year: string
                - synopsis: string
                - stock: string
    - 4xx:
        - success: false,
        - message: string
    - 5xx:
        - success: false,
        - message: Terjadi kesalahan pada server.

POST /books [admin only]
request:
    - headers:
        - authorization: string
    - body:
        - title: string
        - description: string
        - author: string
        - year: string
        - synopsis: string
        - stock: string
response:
    - 201:
        - success: true
        - message: string
        - data:
            - book:
                - id: int
                - title: string
                - description: string
                - author: string
                - year: string
                - synopsis: string
                - stock: string
    - 4xx:
        - success: false,
        - message: string
    - 5xx:
        - success: false,
        - message: Terjadi kesalahan pada server.

GET /books/{bookId}
request:
    -
response:
    - 200:
        - success: true
        - message: string
        - data:
            - book:
                - id: int
                - title: string
                - description: string
                - author: string
                - year: string
                - synopsis: string
                - stock: string
    - 4xx:
        - success: false,
        - message: string
    - 5xx:
        - success: false,
        - message: Terjadi kesalahan pada server.

PUT /books/{bookId} [admin only]
request:
    - headers:
        - authorization: string
    - body:
        - title: string
        - description: string
        - author: string
        - year: string
        - synopsis: string
        - stock: string
response:
    - 200:
        - success: true
        - message: string
        - data:
            - book:
                - id: int
                - title: string
                - description: string
                - author: string
                - year: string
                - synopsis: string
                - stock: string
    - 4xx:
        - success: false,
        - message: string
    - 5xx:
        - success: false,
        - message: Terjadi kesalahan pada server.

DELETE /books/{bookId} [Soft Delete, admin only]
request:
    - headers:
        - authorization: string
response:
    - 200:
        - success: true
        - message: string
    - 4xx:
        - success: false,
        - message: string
    - 5xx:
        - success: false,
        - message: Terjadi kesalahan pada server.


GET /transactions [admin can get all users, but user can only get their transactions]
request:
    - headers:
        - authorization: string
response:
    - 200:
        - success: true
        - message: string
        - data:
            - transactions: array of object
                - id: int
                - user:
                    - name: string
                    - email: string
                - book:
                    - title: string
                    - author: string
                - deadline: date
                - created_at: datetime
                - updated_at: datetime
    - 4xx:
        - success: false,
        - message: string
    - 5xx:
        - success: false,
        - message: Terjadi kesalahan pada server.

POST /transactions [user only]
request:
    - headers:
        - authorization: string
    - body:
        - book_id: int
response:
    - 201:
        - success: true
        - message: string
        - data:
            - transaction:
                - id: int
                - user:
                    - name: string
                    - email: string
                - book:
                    - title: string
                    - author: string
                - deadline: date
                - created_at: datetime
                - updated_at: datetime
    - 4xx:
        - success: false,
        - message: string
    - 5xx:
        - success: false,
        - message: Terjadi kesalahan pada server.

GET /transactions/{transactionId}
request:
    - headers:
        - authorization: string
response:
    - 200:
        - success: true
        - message: string
        - data:
            - transaction:
                - id: int
                - user:
                    - name: string
                    - email: string
                - book:
                    - title: string
                    - author: string
                - deadline: date
                - created_at: datetime
                - updated_at: datetime
    - 4xx:
        - success: false,
        - message: string
    - 5xx:
        - success: false,
        - message: Terjadi kesalahan pada server.

PUT /transactions/{transactionId} [admin only] (when you return the book)
request:
    - headers:
        - authorization: string
    - body:
        - deadline: null
response:
    - 200:
        - success: true
        - message: string
        - data:
            - transaction:
                - id: int
                - user:
                    - name: string
                    - email: string
                - book:
                    - title: string
                    - author: string
                - deadline: date
                - created_at: datetime
                - updated_at: datetime
    - 4xx:
        - success: false,
        - message: string
    - 5xx:
        - success: false,
        - message: Terjadi kesalahan pada server.
```
