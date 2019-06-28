<?php


//read config file
$conf_file = '../config/config.yml';
$conf = read_conf($conf_file);

// connect db
$db = conn_db($conf);
// add column to table
$res = addColumn($db, 'todos', 'completed', 'Boolean', 'False');

/**function: connect to database */
function conn_db($conf)
{
    $server = $conf['host'];
    $dbname = $conf['db'];
    $username = $conf['user'];
    $password =  $conf['password'];
    try {
        $db = new PDO("mysql:host=$server;dbname=$dbname", $username, $password);
        return $db;
    } catch (PDOException $e) {
        echo $e->getMessage();
    }
}

/**function: add column to table */
function addColumn($db, $table, $column, $type, $default)
{
    $sql = "ALTER TABLE $table ADD COLUMN $column $type DEFAULT $default";
    try {
        $db->exec($sql);
    } catch (PDOException $e) {
        if ($db->errorInfo()[1] == 1060) return 0;
        die(print_r($db->errorInfo(), true));
    }
    return 0;
}

/**function: read config from file */
function read_conf($file)
{
    $conf = array();
    $fh = fopen($file, "r") or die("Unable to open file!");
    $text =  fread($fh, filesize($file));
    fclose($fh);
    $conf['host'] = get_info('/host: (.+)/', $text);
    $conf['db'] = get_info('/dbname: (.+)/', $text);
    $conf['user'] = get_info('/user: (.+)/', $text);
    $conf['password'] = get_info('/password: (.+)/', $text);
    return $conf;
}

/**function: get information from text via regular expression */
function get_info($p, $text)
{
    if (preg_match($p, $text, $res)) {
        return $res[1];
    }
}
