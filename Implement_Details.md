### Task 1

Added a validation to the controller when trying to add a todo without description.
(Error Variable in Commit 1 and FlashBag in Commit 6)

### Task 2

- Wrote a database migration script in resources/ (run: cd resources; php migration.php).
- Added UI that allows the users to mark a todo as completed. (in /todo page)
- Added UI that allows the users to mark a todo as completed in the /todo/{id} page. (Commit: Task 2.1)

### Task 3

Added router and function that allow users view a todo in a JSON format via "/todo/{id}/json".

### Task 4

Added confirmation messages when users add/delete a todos by using session FlashBag.

### Task 5

Added todos pagination function.

### Task 6

- Added ORM database access layer.
- Added responses when a database request is failed.

### Task Extra 1

- Added a return link to the todo/{id} page
- Added an error message when user login failed. (Commit: Task 6)
- Changed Task 1's error message from "error variable" to FlashBag. (Commit: Task 6)

### Task Extra 2

Achieved that all users can only operate their own todos. (Commit: Task 6)

### Task Extra 3

Added Unit tests
