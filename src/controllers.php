<?php

/**
 *  Controllers Index:
 *  Line Number:    Controller Title:
 *                  User Login
 *                  User Log Out
 *                  Users Todos List
 *                  'Todos' Add
 *                  'Todos' Completed
 *                  'Todos' Reset
 *                  'Todos' Delete
 */

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

$app['twig'] = $app->share($app->extend('twig', function($twig, $app) {
    $twig->addGlobal('user', $app['session']->get('user'));

    return $twig;
}));

/**
 * Controller for the home page, passing the 'README.md' file.
 */
$app->get('/', function () use ($app) {

    // Render the indecx template page and pass through the README.md file.
    return $app['twig']->render('index.html', array(
        'readme' => file_get_contents('../README.md')
    ));
});

/**
 * Controller for 'User Login'.
 */
$app->match('/login', function (Request $request) use ($app) {

    // Set Variables from the the data inputted from the user on the Login Page
    $username       = $request->get('username');
    $password       = $request->get('password');

    // Confirm if there is a Username present in order to try log in a user.
    if ($username) {

        // Create an (unsafe) method for selecting the user where Username and Password match.
        $sql        = "SELECT COUNT(*) as 'total_users' FROM users WHERE username = '$username';";

        // Collect data from the statement created above.
        $user_count = $app['db']->fetchAssoc($sql);

        // If there is a user present in the $user variable set a Session variable which will state the current user in the session is logged in.
        if ($user_count['total_users'] == 1){

            // Get user's password and store it in a variable
            $user_password_sql  = "SELECT password FROM users WHERE username = '$username';";

            // Collect data from the statement created above.
            $user_password      = $app['db']->fetchAssoc($user_password_sql);

            // Check if the password is correct else redirect to login page.
            if(password_verify($password , $user_password['password'])){

                // Get the users data
                $sql = "SELECT * FROM users WHERE username = '$username';";

                // Run the query
                $user = $app['db']->fetchAssoc($sql);

                // Set Session variable 'user' with relevant data.
                $app['session']->set('user', $user);

                // Successful login with data population, return the user to their Todos list page.
                return $app->redirect('/todo');

            } else {

                // Set Session variable 'user' with relevant data.
                $app['session']->set('user', null);

                // If the password is incorrect, redirect user back to Login page with message.
                $app['session']->getFlashBag()->set('unsuccessful_message', 'Username or Password is incorrect');

                // Redirect the user back to the login page
                return $app->redirect('/login');
            }

        } else {

            // Set Session variable 'user' with relevant data.
            $app['session']->set('user', null);

            // If there is no user with that 'username' redirect them to the login page.
            $app['session']->getFlashBag()->set('unsuccessful_message', 'Username or Password is incorrect');

            // Redirect the user back to the login page
            return $app->redirect('/login');
        }
    }

    // If there is no Username variable present, redirect the user to the Login page.
    return $app['twig']->render('login.html', array());
});


/**
 * Controller for 'Login a User Out' - clearing the Session User variable.
 */
$app->get('/logout', function () use ($app) {

    // Force the Session variable 'user' to Null.
    $app['session']->set('user', null);

    // Redirect the user to the home/base directory.
    return $app->redirect('/');
});

/**
 * Controller for 'Viewing all List items'.
 * Validation if the List is Single all All items and if is to be viewed in JSON format.
 */
$app->get('/todo/{id}', function ($id) use ($app) {

    // Confirm if the user is logged in else redirect them to the login page.
    if (null === $user = $app['session']->get('user')) {
        return $app->redirect('/login');
    }

    // Confirm if there is a single List item requested.
    if ($id){

        // Create statement to select individual item from the Database.
        $sql = "SELECT * FROM todos WHERE id = '$id' AND user_id = '${user['id']}';";

        // Execute above statement to collect single item data.
        $todo = $app['db']->fetchAssoc($sql);

        // Confirm if there is a result for the logged in user
        if(!empty($todo)){

            // Render the template page and pass above data through.
            return $app['twig']->render('todo.html', array(
                'todo' => $todo,
            ));

        } else {

            // Render the template page and pass above data through.
            return $app->redirect('/todo');

        }

    } else {

        // Create Pagination for the Listings.
        // Calculate the total items in the database for this user.
        $total_todo_items_sql   = "SELECT COUNT(*) as 'total_count' FROM todos WHERE user_id = '${user['id']}' AND item_status != 2;";

        // Execute above statement to collect single item data.
        $total_todo_items       = $app['db']->fetchAssoc($total_todo_items_sql);

        // Define the limit per page
        $todo_items_limit       = 5;

        // Get total pages there will be
        $pages                  = ceil($total_todo_items['total_count'] / $todo_items_limit );

        // Set the current page variable
        $current_page           = 0;

        // Calculate the offset for the query
        $offset                 = 0;

        // Create statement to select all items for current logged in from the Database.
        $sql                    = "SELECT * FROM todos WHERE user_id = '${user['id']}' AND item_status != 2 ORDER BY id ASC LIMIT ".$todo_items_limit." OFFSET ".$offset.";";

        // Execute above statement to collect all items.
        $todos = $app['db']->fetchAll($sql);

        // Render the template page and pass above data through.
        return $app['twig']->render('todos.html', array(
            'todos'         => $todos,
            'total_pages'   => $pages,
            'current_page'  => 1
        ));
    }
})
    // Set the passing parameter value by default to Null to allow the route to be called with or without a value there.
    ->value('id', null);

/**
 * Controller for 'JSON Item View', view the Todos item in pure JSON format.
 */
$app->get('/todo/page/{page_number}', function ($page_number) use ($app) {

    // Confirm if the user is logged in else redirect them to the login page.
    if (null === $user = $app['session']->get('user')) {
        return $app->redirect('/login');
    }

    // Confirm if there is a single List item requested.
    if ($page_number){

        // Create Pagination for the Listings.
        // Calculate the total items in the database for this user.
        $total_todo_items_sql   = "SELECT COUNT(*) as 'total_count' FROM todos WHERE user_id = '${user['id']}' AND item_status != 2;";

        // Execute above statement to collect single item data.
        $total_todo_items       = $app['db']->fetchAssoc($total_todo_items_sql);

        // Define the limit per page
        $todo_items_limit       = 5;

        // Get total pages there will be
        $pages                  = ceil($total_todo_items['total_count'] / $todo_items_limit );

        // Set the current page variable
        $current_page           = ($page_number != '')? $page_number : 0 ;

        // Ensure the user always sees content correctly, if the {{ page number }} is manually set to higher number, redirect to Page 1.
        if($current_page > $pages){

            // Redirect user to Page 1.
            return $app->redirect('/todo/page/1');
        }

        // Calculate the offset for the query
        $offset                 = (($current_page == 0)? $current_page : $current_page - 1)  * $todo_items_limit;

        // Create statement to select all items for current logged in from the Database.
        $sql                    = "SELECT * FROM todos WHERE user_id = '${user['id']}' AND item_status != 2 ORDER BY id ASC LIMIT ".$todo_items_limit." OFFSET ".$offset.";";

        // Execute above statement to collect all items.
        $todos = $app['db']->fetchAll($sql);

        // Render the template page and pass above data through.
        return $app['twig']->render('todos.html', array(
            'todos'         => $todos,
            'total_pages'   => $pages,
            'current_page'  => $current_page
        ));

    } else {

        // Redirect user to the first page of paginated list.
        return $app->redirect('/todo/page/1');
    }
})
    // Set the passing parameter value by default to Null to allow the route to be called with or without a value there.
    ->value('page_number', null);
/**
 * Controller for 'JSON Item View', view the Todos item in pure JSON format.
 */
$app->get('/todo/json/{id}', function ($id) use ($app) {

    // Confirm if the user is logged in else redirect them to the login page.
    if (null === $user = $app['session']->get('user')) {
        return $app->redirect('/login');
    }

    // Confirm if there is a single List item requested.
    if ($id){

        // Create statement to select individual item from the Database.
        $sql = "SELECT * FROM todos WHERE id = '$id' AND user_id = '${user['id']}';";

        // Execute above statement to collect single item data.
        $todo = $app['db']->fetchAssoc($sql);

        // Confirm if there is a result for the logged in user
        if(!empty($todo)){

            // Convert requested data into JSON format for frontend.
            $todo_json = json_encode($todo);

            // Render the template page and pass above data through.
            return $app['twig']->render('todo.html', array(
                'todo' => $todo,
                'todo_json' => $todo_json,
            ));

        } else {

            // Render the template page and pass above data through.
            return $app->redirect('/todo');

        }

    } else {

        // Render the template page and pass above data through.
        return $app->redirect('/todo');
    }
})
    // Set the passing parameter value by default to Null to allow the route to be called with or without a value there.
    ->value('id', null);

/**
 * Controller for 'Adding List items' per the user's requrest.
 * Validation on the 'description' input field occurs.
 */
$app->post('/todo/add', function (Request $request) use ($app) {

    // Confirm if the user is logged in else redirect them to the login page.
    if (null === $user = $app['session']->get('user')) {
        return $app->redirect('/login');
    }

    // Define Usable Variables
    $user_id        = $user['id'];
    $description    = trim($request->get('description'));

    // Validate if the $description variable has a value or is empty
    if($description != ''){

        // Get the current time for create_date and mod_date column.
        $current_date_time  = date("Y-m-d H:i:s");

        // If there is a value, insert the new 'item' into the database.
        $sql                = "INSERT INTO todos (create_date, mod_date, user_id, description, item_status) VALUES ('$current_date_time','$current_date_time','$user_id', '$description',0)";

        $app['db']->executeUpdate($sql);

        // If new Todos item has been successfully added to the data, create a message for the user to see it's been added.
        $app['session']->getFlashBag()->set('success_message', 'You have successfully added a new item to your Todo list!');

        return $app->redirect('/todo');

    } else {

        // If there is no value, redirect user with FlashBag message, asking them to add a description.
        $app['session']->getFlashBag()->set('unsuccessful_message', 'Please add a description..');

        return $app->redirect('/todo');
    }

});

/**
 * Controller for 'Completing one of the Todos on the List'.
 */
$app->post('/todo/completed/{id}', function ($id) use ($app) {

    // Confirm if the user is logged in else redirect them to the login page.
    if (null === $user = $app['session']->get('user')) {
        return $app->redirect('/login');
    }

    // Get the current time for mod_date column.
    $current_date_time  = date("Y-m-d H:i:s");

    // Create and UPDATE statement for 'Todos' table
    $sql                = "UPDATE todos SET mod_date = '$current_date_time', item_status = 1 WHERE id = '$id' AND user_id = '${user['id']}';";

    // Execute sql command
    $app['db']->executeUpdate($sql);

    // If Todos item has been successfully updated to 'Completed' (1) status, create a message for the user to see it's been updated to Complete.
    $app['session']->getFlashBag()->set('success_message', 'You have updated the item on your Todo list to Completed.');

    // Return the User back to 'Todos' list.
    return $app->redirect('/todo');

});

/**
 * Controller for 'Resetting one of the Todos on the List'.
 */
$app->post('/todo/reset/{id}', function ($id) use ($app) {

    // Confirm if the user is logged in else redirect them to the login page.
    if (null === $user = $app['session']->get('user')) {
        return $app->redirect('/login');
    }

    // Get the current time for mod_date column.
    $current_date_time  = date("Y-m-d H:i:s");

    // Create and UPDATE statement for 'Todos' table
    $sql                = "UPDATE todos SET mod_date = '$current_date_time', item_status = 0 WHERE id = '$id' AND user_id = '${user['id']}';";

    // Execute sql command
    $app['db']->executeUpdate($sql);

    // If Todos item has been successfully updated to 'Live' (0) status, create a message for the user to see it's been updated to Live.
    $app['session']->getFlashBag()->set('success_message', 'You have updated and reset the item on your Todo list.');

    // Return the User back to 'Todos' list.
    return $app->redirect('/todo');

});

/**
 * Controller for 'Deleting one of the Todos on the List'.
 * I don't like deleting data and rather use a status column to either display or not display items.
 * This allows for better auditing support.
 */
$app->match('/todo/delete/{id}', function ($id) use ($app) {

    // Confirm if the user is logged in else redirect them to the login page.
    if (null === $user = $app['session']->get('user')) {
        return $app->redirect('/login');
    }

    // Get the current time for create_date and mod_date column.
    $current_date_time  = date("Y-m-d H:i:s");

    // Create a UPDATE 'Delete status' statement for 'Todos' table
    $sql = "UPDATE todos SET mod_date = '$current_date_time', item_status = 2 WHERE id = '$id' AND user_id = '${user['id']}';";

    // Execute the above statement
    $app['db']->executeUpdate($sql);

    // If Todos item has been successfully updated to 'Deleted' (2) status, create a message for the user to see it's been deleted.
    $app['session']->getFlashBag()->set('success_message', 'You have successfully removed the item from your Todo list!');

    // Redirect the user to their Todos list
    return $app->redirect('/todo');
});