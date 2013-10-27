#!/usr/bin/php
<?php

/* Include Box classes */
require_once(dirname(__FILE__) .'/Box.class.php');

/* Config */
define('DEFAULT_SRC_FOLDER', '/Volumes/LaCie/Music/');
define('DEFAULT_DST_FOLDER', 'Music');

/* Parse args */
$srcFolder = DEFAULT_SRC_FOLDER;
if(isset($argv[1])){
	$srcFolder = trim($argv[1]);
}

$max = 20;
$num = 0;

try{
	/* Init the Box class */
	$box = new Box();

	/* Get the folder id  */
	$dstFolderId = $box->loadFolderByName(DEFAULT_DST_FOLDER);
	if($dstFolderId === false){
		throw new Exception('Destination folder not found !', 404);
	}

	/* Check the src folder really exists */
	if(!file_exists($srcFolder) || filetype($srcFolder) !== 'dir'){
		throw new Exception('Invalid folder : ' .$srcFolder);
	}

	/* Checking the path ends with a slash */
	if(substr($srcFolder, -1) !== '/'){
		$srcFolder .= '/';
	}

	/* Retrieve list of entries in the in folder */
	$srcEntries = scandir($srcFolder);
	
	/* Loop all entries */
	foreach($srcEntries as $entry){

		/* Skip hidden files */
		if(substr($entry,0,1) != '.'){

			/* Set absolute path */
			$srcEntryName = $srcFolder .$entry;

			/* Get type */
			switch(filetype($srcEntryName)){
				case 'dir':
					echo 'Album : ' .$entry ."\n";
					
					/* Check if dst folder exists */
					$albumFolderId = $box->loadFolderByName($entry, $dstFolderId);
					if($albumFolderId === false){
						/* The album is not uploaded yet */
						$album = $box->createFolder($entry, $dstFolderId);
						// echo 'Album : ' .print_r($album,1) ."\n";
						$albumFolderId = $album['id'];
					}

					/* Retrieve the list of files in the folder */
					$dstFilesSignature = array();
					$dstFilesName = array();
					$items = $box->readFolder($albumFolderId);

					/* Parse to keep only the files */
					foreach($items['entries'] as $item){
						if($item['type'] == 'file'){
							$dstFilesSignature[$item['sha1']] = $item['id'];
							$dstFilesName[$item['name']] = $item['id'];
						}
					}

					/* Retrieve list of songs */
					$songs = scandir($srcEntryName);
					/* Loop all songs */
					foreach($songs as $song){
						/* Skip hidden file */
						if(substr($song,0,1) != '.'){
							$songFile = $srcEntryName .'/' .$song;
							$signature = sha1_file($songFile);

							/* Check file does not already exist (via sig) */
							if(isset($dstFilesSignature[$signature])){
								echo 'Song "' .$song .'"" already uploaded !' ."\n";
							}
							else{
								/* Check if a file with the same name already exists */
								if(isset($dstFilesName[$song])){
									/* Update file */
									echo 'Song to be updated !' ."\n";
								}
								else{
									/* Upload new file */
									$response = $box->uploadfile($songFile, $albumFolderId);
									echo 'Song "' .$song .'" uploaded as id ' .$response['entries'][0]['id'] ."\n";
								}
							}

							// $num++;

							if($num > $max){
								die("\n\n");
							}
						}
					}

					/* End of the album */
					echo '==========' ."\n\n";
					break;
			}
		}
	}
}
catch(BoxException $exception){
	echo 'Exception : ' .$exception->getMessage() .' (' .$exception->getCode() .')' ."\n";
	echo 'From : ' .$exception->getFile() .':' .$exception->getLine();
}
catch(Exception $exception){
	echo 'Exception : ' .$exception->getMessage() .' (' .$exception->getCode() .')' ."\n";
	echo 'From : ' .$exception->getFile() .':' .$exception->getLine();
}
/* End */
echo "End\n\n";