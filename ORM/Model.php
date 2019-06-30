<?php

namespace ORM {

    class Model
    {
        // Find data via ID
        public static function find($db, $id)
        {
            $sql = "SELECT * FROM " . static::$table . " WHERE id = $id";
            return $db->fetchAssoc($sql);
        }
        // Add data
        public static function add()
        {
            $params = func_get_args();
            $db = array_shift($params);
            $field_names_str = join(", ", static::$fields);
            $field_values_str = "'" . join("', '", $params) . "'";
            $sql = "INSERT INTO todos ($field_names_str) VALUES ($field_values_str)";
            return $db->executeUpdate($sql);
        }
        // Delete data
        public static function delete($db, $id, array $conditions)
        {
            $sql = "DELETE FROM todos WHERE id = '2'";
            $c = array_map(function ($k, $v) {
                return $k . " = '" . $v . "'";
            }, array_keys($conditions), $conditions);
            array_unshift($c, "id = $id");
            $conditions_str = join(" and ", $c);
            $sql = "DELETE FROM " . static::$table . " WHERE $conditions_str";
            return $db->executeUpdate($sql);
        }

        // Try to get row from database
        public static function exists()
        {
            $params = func_get_args();
            $db = array_shift($params);
            $sql = "SELECT * FROM " . static::$table . " WHERE ";
            $c = array_map(function ($k, $v) {
                return $k . " = '" . $v . "'";
            }, static::$fields, $params);
            $sql .= join(" and ", $c);
            return $db->fetchAssoc($sql);
        }
        // Update the data
        public static function update($db, $id, array $params)
        {
            $sql = "UPDATE " . static::$table . " SET ";
            $c = array_map(function ($k, $v) {
                return $k . " = " . $v;

            }, array_keys($params), $params);
            $sql .= join(", ", $c);
            $sql .= " WHERE id = '" . $id . "'";
            return $db->executeUpdate($sql);
        }
        // Get Children data via foreign key
        public static function hasMany($db, $id, $table, $fk_field)
        {
            $sql = "SELECT * FROM $table WHERE $fk_field = $id";
            return $db->fetchAll($sql);
        }
        public static function belongsTo($db, $id, $table, $pk_field)
        {}
    }
}
