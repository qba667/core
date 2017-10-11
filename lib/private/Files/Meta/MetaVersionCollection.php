<?php
/**
 * @author Thomas Müller <thomas.mueller@tmit.eu>
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


namespace OC\Files\Meta;


use OC\Files\Node\AbstractFolder;
use OC\Files\Storage\IVersionedStorage;
use OC\Files\View;
use OCP\Files\FileInfo;
use OCP\Files\Folder;
use OCP\Files\InvalidPathException;
use OCP\Files\NotFoundException;
use OCP\Files\NotPermittedException;
use OCP\Files\Storage;

class MetaVersionCollection extends AbstractFolder {

	private $fileId;

	public function __construct($fileId) {
		$this->fileId = $fileId;
	}

	/**
	 * @inheritdoc
	 */
	public function isEncrypted() {
		return false;
	}

	/**
	 * @inheritdoc
	 */
	public function isShared() {
		return false;
	}

	/**
	 * get the content of this directory
	 *
	 * @throws \OCP\Files\NotFoundException
	 * @return \OCP\Files\Node[]
	 * @since 6.0.0
	 */
	public function getDirectoryListing() {
		$view = new View();
		$path = $view->getPath($this->fileId);
		/** @var Storage $storage */
		list($storage, $internalPath) = $view->resolvePath($path);
		if (!$storage->instanceOfStorage(IVersionedStorage::class)) {
			return [];
		}
		/** @var IVersionedStorage $storage */
		$versions = $storage->getVersions($internalPath);
		return array_map(function($version) use ($storage, $internalPath) {
			return new MetaFileVersionNode($this, $version['version'],$storage, $internalPath);
		}, $versions);
	}

	/**
	 * Get the node at $path
	 *
	 * @param string $path relative path of the file or folder
	 * @return \OCP\Files\Node
	 * @throws \OCP\Files\NotFoundException
	 * @since 6.0.0
	 */
	public function get($path) {
		$pieces = explode('/', $path);
		if (count($pieces) !== 1) {
			throw new NotFoundException();
		}
		$versionId = $pieces[0];
		$view = new View();
		$path = $view->getPath($this->fileId);
		/** @var Storage $storage */
		list($storage, $internalPath) = $view->resolvePath($path);
		if (!$storage->instanceOfStorage(IVersionedStorage::class)) {
			throw new NotFoundException();
		}
		/** @var IVersionedStorage $storage */
		$version = $storage->getVersion($internalPath, $versionId);
		if ($version === null) {
			throw new NotFoundException();
		}
		return new MetaFileVersionNode($this, $version['version'], $storage, $internalPath);
	}

	public function getId() {
		return $this->fileId;
	}
}
