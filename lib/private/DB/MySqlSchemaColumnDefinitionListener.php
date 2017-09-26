<?php
/**
 * @author Viktar Dubiniuk <dubiniuk@owncloud.com>
 *
 * @copyright Copyright (c) 2017, ownCloud GmbH
 * @license AGPL-3.0
 *
 * This code is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License, version 3,
 * as published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License, version 3,
 * along with this program.  If not, see <http://www.gnu.org/licenses/>
 *
 */

namespace OC\DB;

use Doctrine\DBAL\Event\SchemaColumnDefinitionEventArgs;
use Doctrine\DBAL\Platforms\MySqlPlatform;
use Doctrine\DBAL\Schema\Column;
use Doctrine\DBAL\Types\Type;

class MySqlSchemaColumnDefinitionListener{

	public function __construct() {
		$this->_platform = new MySqlPlatform();
	}

	public function onSchemaColumnDefinition(SchemaColumnDefinitionEventArgs $eventArgs) {
		$tableColumn = $eventArgs->getTableColumn();
		$eventArgs->preventDefault();
		$column = $this->_getPortableTableColumnDefinition($tableColumn);
		$eventArgs->setColumn($column);
	}

	
	/**
	 * Given a table comment this method tries to extract a typehint for Doctrine Type, or returns
	 * the type given as default.
	 *
	 * @param string $comment
	 * @param string $currentType
	 *
	 * @return string
	 */
	public function extractDoctrineTypeFromComment($comment, $currentType)
	{
		if (preg_match("(\(DC2Type:([a-zA-Z0-9_]+)\))", $comment, $match)) {
			$currentType = $match[1];
		}

		return $currentType;
	}
	
	/**
	 * @param string $comment
	 * @param string $type
	 *
	 * @return string
	 */
	public function removeDoctrineTypeFromComment($comment, $type)
	{
		return str_replace('(DC2Type:'.$type.')', '', $comment);
	}
	
	protected function _getPortableTableColumnDefinition($tableColumn)
	{
		$tableColumn = array_change_key_case($tableColumn, CASE_LOWER);

		$dbType = strtolower($tableColumn['type']);
		$dbType = strtok($dbType, '(), ');
		if (isset($tableColumn['length'])) {
			$length = $tableColumn['length'];
		} else {
			$length = strtok('(), ');
		}

		$fixed = null;

		if ( ! isset($tableColumn['name'])) {
			$tableColumn['name'] = '';
		}

		$scale = null;
		$precision = null;

		$type = $this->_platform->getDoctrineTypeMapping($dbType);

		// In cases where not connected to a database DESCRIBE $table does not return 'Comment'
		if (isset($tableColumn['comment'])) {
			$type = $this->extractDoctrineTypeFromComment($tableColumn['comment'], $type);
			$tableColumn['comment'] = $this->removeDoctrineTypeFromComment($tableColumn['comment'], $type);
		}

		switch ($dbType) {
			case 'char':
			case 'binary':
				$fixed = true;
				break;
			case 'float':
			case 'double':
			case 'real':
			case 'numeric':
			case 'decimal':
				if (preg_match('([A-Za-z]+\(([0-9]+)\,([0-9]+)\))', $tableColumn['type'], $match)) {
					$precision = $match[1];
					$scale = $match[2];
					$length = null;
				}
				break;
			case 'tinytext':
				$length = MySqlPlatform::LENGTH_LIMIT_TINYTEXT;
				break;
			case 'text':
				$length = MySqlPlatform::LENGTH_LIMIT_TEXT;
				break;
			case 'mediumtext':
				$length = MySqlPlatform::LENGTH_LIMIT_MEDIUMTEXT;
				break;
			case 'tinyblob':
				$length = MySqlPlatform::LENGTH_LIMIT_TINYBLOB;
				break;
			case 'blob':
				$length = MySqlPlatform::LENGTH_LIMIT_BLOB;
				break;
			case 'mediumblob':
				$length = MySqlPlatform::LENGTH_LIMIT_MEDIUMBLOB;
				break;
			case 'tinyint':
			case 'smallint':
			case 'mediumint':
			case 'int':
			case 'integer':
			case 'bigint':
			case 'year':
				$length = null;
				break;
		}

		$length = ((int) $length == 0) ? null : (int) $length;

		$options = array(
			'length'		=> $length,
			'unsigned'	  => (bool) (strpos($tableColumn['type'], 'unsigned') !== false),
			'fixed'		 => (bool) $fixed,
			'default'	   => isset($tableColumn['default']) && !($tableColumn['default'] === 'NULL' && $tableColumn['null'] === 'YES') ? $tableColumn['default'] : null,
			'notnull'	   => (bool) ($tableColumn['null'] != 'YES'),
			'scale'		 => null,
			'precision'	 => null,
			'autoincrement' => (bool) (strpos($tableColumn['extra'], 'auto_increment') !== false),
			'comment'	   => isset($tableColumn['comment']) && $tableColumn['comment'] !== ''
				? $tableColumn['comment']
				: null,
		);

		if ($scale !== null && $precision !== null) {
			$options['scale'] = $scale;
			$options['precision'] = $precision;
		}

		$column = new Column($tableColumn['field'], Type::getType($type), $options);

		if (isset($tableColumn['collation'])) {
			$column->setPlatformOption('collation', $tableColumn['collation']);
		}

		return $column;
	}
}
