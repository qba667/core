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
use OC\MetaData;
use OCP\IMetaData;
use Sabre\DAV\File;

class MetaDataNode {

	private $metaVersionCollection;
	private $metaData;
	/**
	 * MetaDataNode constructor.
	 *
	 */
	public function __construct(MetaData $metaData) {
		$this->metaData = $metaData;
		$this->metaVersionCollection = new MetaVersionCollection($this->metaData->getFileId());
	}

	/**
	 * Check if file exists
	 *
	 * @param $fileName
	 * @return bool
	 */
	public function fileExists($fileName) {
		$filePath = $this->metaData->getFilePath($fileName);
		if (($filePath !== '') || ($filePath !== null)) {
			return true;
		}

		return false;
	}

	/**
	 * Get the fileVersion Node
	 *
	 * @param $fileName
	 * @return \OCP\Files\Node
	 */
	public function getFileVersion($fileName) {
		if ($this->fileExists($fileName)) {
			return $this->metaVersionCollection->get($fileName);
		}
	}

	/**
	 * Create version folder. The version folder will
	 * be inside 'meta' folder
	 */
	public function createVersionFolder() {
		$this->metaData->newMetaSubFolders('v');
	}

	/**
	 * Get the name of the file
	 *
	 * @return string
	 */
	public function getName() {
		return $this->metaVersionCollection->getName();
	}

	/**
	 * Returns the content of the file version
	 *
	 * @param $fileName
	 * @return false|string
	 */
	public function getFileVersionContent($fileName) {
		$getVersion = $this->getFileVersion($fileName);
		$path = $getVersion->getPath();
		return $this->metaVersionCollection->getStorage()->file_get_contents($path);
	}

	/**
	 * Returns a list of versions (path) associated with file
	 *
	 * @return array
	 */
	public function getVersionsOfFile() {
		$nodeVersionList = [];
		$fileList = $this->metaVersionCollection->getDirectoryListing();

		foreach ($fileList as $file) {
			array_push($nodeVersionList, $file->getInternalPath());
		}

		return $nodeVersionList;
	}

}
