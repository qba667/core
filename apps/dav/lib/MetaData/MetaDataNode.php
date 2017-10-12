<?php
/**
 * @author Sujith Haridasan <sharidasan@owncloud.com>
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
use Sabre\DAV\Exception\MethodNotAllowed;
use Sabre\DAV\INode;

class MetaDataNode implements INode {

	/** @var MetaVersionCollection  */
	private $metaVersionCollection;

	private $nodeFolder;

	/**
	 * MetaDataNode constructor.
	 *
	 * @param MetaVersionCollection $metaVersionCollection
	 */
	public function __construct(MetaVersionCollection $metaVersionCollection) {
		$this->metaVersionCollection = $metaVersionCollection;
		$this->nodeFolder = \OC::$server->getRootFolder()->get('');
	}

	function getName() {
		$this->getMetaNodeFolder()->getName();
	}

	function setName($name) {
		throw MethodNotAllowed();
	}

	function delete() {
		throw MethodNotAllowed();
	}

	function getLastModified() {
		return null;
	}
	/**
	 * @return \OCP\Files\Node
	 */
	function getMetaNodeFolder() {
		return \OC::$server->getRootFolder()->get('meta');
	}

	/**
	 * @return \OCP\Files\Node
	 */
	function getFileIdFolder() {
		return \OC::$server->getRootFolder()->get('meta/' . $this->metaVersionCollection->getId());
	}

	/**
	 * @return \OCP\Files\Node
	 */
	function getMetaVersionFolder() {
		return \OC::$server->getRootFolder()->get('meta/'. $this->metaVersionCollection->getId() . '/v');
	}

	/**
	 * @return \OCP\Files\Node
	 */
	function getMetaPreviewFolder() {
		return \OC::$server->getRootFolder()->get('meta/'. $this->metaVersionCollection->getId() . '/p');
	}
}
