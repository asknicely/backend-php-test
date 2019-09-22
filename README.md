# AskNicely PHP backend skill test

## Application

The TODO App allows a user to add reminders of thing he needs to do. Here are the requirement for the app.

* Users can add, delete and see their todos.
* All the todos are private, users can't see other user's todos.
* Users must be logged in order to add/delete/see their todos.

Credentials:

* username: **user1**
* password: **user1**

### Installation

```sh
php composer.phar install
cp config/config.yml.dist config/config.yml
mysql -u root <database> < resources/database.sql
mysql -u root <database> < resources/fixtures.sql
mysql -u root <database> < resources/add_is_completed_column_to_todos_table.sql
php -S localhost:1337 -t web/ web/index.php
```

You can change the database connection from the file `config/config.yml`.
