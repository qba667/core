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

namespace OC;

use OC\Files\Filesystem;
use OC\Files\Storage\Local;
use OC\Files\View;
use OC\User\User;
use OCP\Files\Folder;
use OCP\IL10N;
use OCP\ILogger;
use OCP\IMetaData;


class MetaData implements IMetaData {
	/** @var Folder */
	private $folder;
	/** @var IL10N */
	private $l;
	/** @var ILogger  */
	private $logger;

	/** @var array contains meta data information */
	private $metadataInfo = [];

	/** @var string path to meta folder  */
	private $metaPath;

	/**
	 * MetaData constructor.
	 *
	 * @param IL10N $l
	 * @param ILogger $logger
	 */
	public function __construct (IL10N $l, ILogger $logger) {
		$this->l = $l;
		$this->logger = $logger;
		$this->metadataInfo = [
			'fileId' => '',
			'uuid_fileid' => ''
		];
		$this->metaPath = '/meta/';
		$this->folder = \OC::$server->getUserFolder();
	}

	/**
	 * Generate uuid
	 *
	 * @return string
	 */
	protected function generate_uuid() {
		return sprintf( '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
			mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ),
			mt_rand( 0, 0xffff ),
			mt_rand( 0, 0x0fff ) | 0x4000,
			mt_rand( 0, 0x3fff ) | 0x8000,
			mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff )
		);
	}

	/**
	 * Returns the metadataInfo array
	 *
	 * @return array
	 */
	public function getMetaData() {
		return $this->metadataInfo;
	}

	/**
	 * Return the fileId, i.e the folder name
	 *
	 * @return mixed
	 */
	public function getFileId() {
		return $this->metadataInfo['fileId'];
	}

	/**
	 * Get the path to version folder using file id
	 *
	 * @param $fileId
	 * @return null|string
	 */
	public function getVersionPath($fileId) {
		if (($this->metadataInfo[$fileId] !== '') &&
			(($this->metaPath !== '') && ($this->metaPath !== null))) {
			return $this->metaPath . $this->metadataInfo[$fileId] . '/v';
		}

		return null;
	}

	/**
	 * Get the path to preview folder using file id
	 *
	 * @param $fileId
	 * @return null|string
	 */
	public function getPreviewPath($fileId) {
		if (($this->metadataInfo[$fileId] !== '') &&
			(($this->metaPath !== '') && ($this->metaPath !== null))) {
			return $this->metaPath . $this->metadataInfo[$fileId] . '/p';
		}

		return null;
	}

	/**
	 * Create fileId and folder
	 */
	public function setFileId() {
		if (!$this->folder->nodeExists($this->metadataInfo['fileId'])) {
			$this->metadataInfo['uuid_fileid'] = $this->generate_uuid();
			$folderPath = $this->metaPath . $this->metadataInfo['uuid_fileid'];
			$this->metadataInfo['fileId'] = $this->folder->getId();
			$this->folder->newFolder($this->metaPath . $this->metadataInfo['uuid_fileid']);
			$this->metadataInfo['fileId'] = $this->folder->get($this->metaPath . $this->metadataInfo['uuid_fileid'])->getId();
		}
	}

	/**
	 * Create new folder 'meta'
	 */
	public function newMetaFolder() {
		if (!$this->folder->nodeExists($this->metaPath)) {
			$this->folder->newFolder($this->metaPath);
		}
	}

	/**
	 * Get path of the file in the user home
	 * @param $fileName
	 * @return string
	 */
	public function getFilePath($fileName) {
		return $this->folder->get($fileName)->getInternalPath();
	}

	/**
	 * Create folders 'p' or 'v' inside 'meta' folder
	 * 'p' folder contains the preview of files
	 * 'v' folder contains the version of files
	 *
	 * @param $folderName
	 */
	public function newMetaSubFolders($folderName) {
		if (($folderName === 'p') || ($folderName === 'v')) {
			if (!$this->folder->nodeExists($folderName)) {
				$this->folder->newFolder($this->metaPath . $folderName);
			}
		}
	}

	/**
	 * Get all versions of file from the fileId in Local Storage
	 *
	 * @param $fileId
	 * @return array
	 */
	public function getFileVersions($fileId) {
		$path = $this->getVersionPath($fileId);
		if ($path !== null) {
			$mount = Filesystem::getMountManager()->find($path);
			$storage = $mount->getStorage();
			if ($storage->instanceOfStorage(Local::class)) {
				return $storage->getVersions($path);
			}
		}

		return [];
	}

	/**
	 * Get version of file from fileId in Local Storage
	 *
	 * @param $fileId
	 * @param $versionId
	 * @return array
	 */
	public function getFileVersion($fileId, $versionId) {
		$path = $this->getVersionPath($fileId);
		if ($path !== null) {
			$mount = Filesystem::getMountManager()->find($path);
			$storage = $mount->getStorage();
			if ($storage->instanceOfStorage(Local::class)) {
				return $storage->getVersion($path, $versionId);
			}
		}

		return [];
	}
}
