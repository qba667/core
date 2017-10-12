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

namespace OCP;
use OCP\Files\File;
use OCP\Files\NotFoundException;

/**
 * This class provides metadata functionality
 * @since 6.0.0
 */
interface IMetaData {

	/**
	 * Get the metadata array
	 *
	 * @since 10.0.4
	 * @return mixed
	 */
	public function getMetaData();

	/**
	 * Return the fileId, i.e the folder name
	 *
	 * @return mixed
	 */
	public function getFileId();

	/**
	 * Get the path to version folder using file id
	 *
	 * @param $fileId
	 * @return null|string
	 */
	public function getVersionPath($fileId);

	/**
	 * Get the path to preview folder using file id
	 *
	 * @param $fileId
	 * @return null|string
	 */
	public function getPreviewPath($fileId);

	/**
	 * Create fileId and folder
	 * @return mixed
	 */
	public function setFileId();

	/**
	 * Create new folder 'meta'
	 *
	 * @return mixed
	 */
	public function newMetaFolder();

	/**
	 * Create folders 'p' or 'v' inside 'meta' folder
	 * 'p' folder contains the preview of files
	 * 'v' folder contains the version of files
	 * @param $folderName
	 * @return mixed
	 */
	public function newMetaSubFolders($folderName);

	/**
	 * Get all versions of file from the fileId in Local Storage
	 *
	 * @param $fileId
	 * @return mixed
	 */
	public function getFileVersions($fileId);

	/**
	 * Get version of file from fileId in Local Storage
	 *
	 * @param $fileId
	 * @param $versionId
	 * @return mixed
	 */
	public function getFileVersion($fileId, $versionId);

	/**
	 * @param $fileName
	 * @return mixed
	 */
	public function getFilePath($fileName);
}
