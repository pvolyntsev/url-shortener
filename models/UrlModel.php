<?php

/**
 * Class provides access to database table 'url' in object manner
 * That also called ORM (Object-relational mapping)
 */
class UrlModel extends AppModel {

	/**
	 * @var int
	 */
	public $id;

	/**
	 * @var string
	 */
	public $longurl;

	/**
	 * Method must return name of table
	 * @return string
	 */
	public static function getTableName() {
		return 'url';
	}

	/**
	 * Method must return the name of the column, which is the primary key of the table.
	 *    TODO Or array of names if primary key is complex
	 * @return string|string[]
	 */
	public static function getPrimaryKey() {
		return 'id';
	}

	/**
	 * Method must return names of all columns of the table
	 * @return string[]
	 */
	public static function getColumnNames() {
		return array('id', 'longurl');
	}

	/**
	 * @param $url
	 * @return bool|UrlModel
	 */
	public static function findOneByLongurl($url) {
		$columns = static::getColumnNames();
		$columnsCommaList = implode(',', $columns);
		$tableName = static::getTableName();

		$sql = "SELECT {$columnsCommaList} FROM {$tableName} WHERE longurl = ?";
		$row = App::db()->getRow($sql, array($url));

		if (is_array($row)) {
			$model = new static;
			$model->populate($row);
			return $model;
		}

		return false;
	}
}
