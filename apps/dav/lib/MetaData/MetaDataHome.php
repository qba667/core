<?php
/**
 * @author Sujith Haridasan <shariadsan@owncloud.com>
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


namespace OCA\DAV\MetaData;


use OC\Files\Meta\MetaVersionCollection;
use OCP\IMetaData;
use Sabre\DAV\Exception\Forbidden;
use Sabre\DAV\Exception\MethodNotAllowed;
use Sabre\DAV\Exception\NotFound;
use Sabre\DAV\ICollection;
use Sabre\HTTP\URLUtil;

class MetaDataHome implements ICollection {

	/** @var array */

	private $metaData;

	/**
	 * MetaDataHome constructor.
	 */
	public function __construct() {
	}

	function createFile($name, $data = null) {
		throw new Forbidden('Permission denied to create a file');
	}

	function createDirectory($name) {
		throw new Forbidden('Permission denied to create a folder');
	}

	function getChild($name) {
		$metaVersion = new MetaVersionCollection('');
		return new MetaDataNode($metaVersion);
	}

	function getChildren() {
		try {
			return [
				$this->getChild('')
			];
		} catch(NotFound $exception) {
			return [];
		}
	}

	function childExists($name) {
		try {
			$ret = $this->getChild($name);
			return !is_null($ret);
		} catch (NotFound $ex) {
			return false;
		} catch (MethodNotAllowed $ex) {
			return false;
		}
	}

	function delete() {
		throw new Forbidden('Permission denied to delete this folder');
	}

	function getName() {
		return "meta";
	}

	function setName($name) {
		throw new Forbidden('Permission denied to rename this folder');
	}

	/**
	 * Returns the last modification time, as a unix timestamp
	 *
	 * @return int
	 */
	function getLastModified() {
		return null;
	}


}
