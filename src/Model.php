<?php

use Symfony\Component\Yaml\Yaml;

class Model {
	private $table_name;
	private $app;

	public function __construct($app, $table_name) {
		$this->app = $app;
		$this->table_name = $table_name;
	}

	public function findBySql($sql, $conditions=[]) {
		try {
			$records = $this->app['db']->fetchAssoc($sql, $conditions);
			return $records;
		} catch (\Exception $e) {
			return false;
		}
	}

	public function findAll($conditions) {
		try {
			if (is_array($conditions)) {
				$where = [];
				$values = [];
				foreach ($conditions as $key=>$val) {
					if ($where) {
						$where .= " AND {$key} = ? ";
					} else {
						$where = " {$key} = ? ";
					}
					$values[] = $val;
				}
				$sql = "SELECT * FROM {$this->table_name} WHERE {$where}";
			} else {
				$conditions = intval($conditions);
				$sql = "SELECT * FROM {$this->table_name} WHERE id = ?";
				$values = [$conditions];
			}
			$records = $this->app['db']->fetchAll($sql, $values);
			return $records;
		}catch (\Exception $e) {
			return false;	
		}
	}

	public function findOne($conditions) {
		try {
			if (is_array($conditions)) {
				$where = '';
				$values = [];
				foreach ($conditions as $key=>$val) {
					if ($where) {
						$where .= " AND {$key} = ? ";
					} else {
						$where = " {$key} = ? ";
					}
					$values[] = $val;
				}
				$sql = "SELECT * FROM {$this->table_name} WHERE {$where} LIMIT 1";
			} else {
				$conditions = intval($conditions);
				$sql = "SELECT * FROM {$this->table_name} WHERE id = ? LIMIT 1";
				$values = [$conditions];
			}
			$records = $this->findBySql($sql, $values);
			return $records;
		}catch (\Exception $e) {
			return false;	
		}
	}


	public function deleteOne($conditions) {
		$where = [];
		$values = [];
		foreach ($conditions as $key=>$val) {
			if ($where) {
				$where .= " AND {$key} = ? ";
			} else {
				$where = " {$key} = ? ";
			}
			$values[] = $val;
		}

		$sql = "DELETE FROM {$this->table_name} WHERE {$where} LIMIT 1";
		$cnt = $this->app['db']->executeUpdate($sql, $values);
		return $cnt;
	}

	public function insertOne($conditions) {
		if (empty($conditions)) {
			return false;	
		}
		$fields = [];
		$values = [];
		$marks  = [];
		foreach ($conditions as $key=>$val) {
			$fields[] = $key;
			$values[] = $val;
			$marks[] = '?';
		}
		$fields = implode(', ', $fields);
		$marks = implode(', ', $marks);

		$sql = "INSERT INTO {$this->table_name} ($fields) VALUES ({$marks})";
		$cnt = $this->app['db']->executeUpdate($sql, $values);
		return $cnt;
	}

	public function updateAll($updates, $conditions) {
		if (empty($conditions)) {
			return false;	
		}
		$sets = [];
		foreach ($updates as $key=>$val) {
			if(is_int($val)) {
				$sets[] = "{$key} = {$val}";
			} elseif(is_string($val)) {
				$sets[] = "{$key} = '{$val}'";
			} elseif(is_bool($val)) {
				$sets[] = "{$key} = {$val}";
			} else {
				$sets[] = "{$key} = {$val}";
			}
		}
		$sets = implode(',', $sets);

		$where = [];
		foreach ($conditions as $key=>$val) {
			if(is_int($val)) {
				$value = $val;
			} elseif(is_string($val)) {
				$value = "'{$val}'";
			} elseif(is_bool($val)) {
				$value = $val;
			} else {
				$value = $val;
			}

			if ($where) {
				$where .= " AND {$key} =  {$value}";
			} else {
				$where = " {$key} = {$value} ";
			}
		}

		$sql = "UPDATE {$this->table_name} SET $sets WHERE {$where}";
		$cnt = $this->app['db']->executeUpdate($sql);
		return $cnt;
	}


}