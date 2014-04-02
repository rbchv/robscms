<?php


CakePlugin::load('FriendlyDateHelper');


class PostsController extends AppController
{
	public $name = 'Posts';
	public $uses = array('Post', 'Me');
	public $helpers = array('FriendlyDateHelper.Friendlydate');

	private $randomKeyLength = 40;


	public function beforeFilter()
	{
		parent::beforeFilter();

		//Limit some actions to only the admin
		$adminOnlyAccessableActions = array('newkey', 'delete', 'edit');
		if(in_array($this->action, $adminOnlyAccessableActions))
		{
			if($this->Session->read('permissions') < $this->Postpermission->getMaxPermission())
			{
				$this->Session->setFlash('<h4>' . __('Error') . '!</h4>', 'flash_red');
				$this->redirect('/');
			}
		}
	}


	public function newkey($id)
	{
		$this->layout = 'ajax';

		$newKey = $this->generateRandomString($this->randomKeyLength);
		$updateMe = array();
		$updateMe['id'] = intval($id);
		$updateMe['key'] = $newKey;

		if($this->Post->save($updateMe))
		{
			App::import('Helper', 'Html');
			$htmlHelper = new HtmlHelper(new View());
			echo FULL_BASE_URL . $htmlHelper->url(array('controller' => 'posts', 'action' => 'view', $id, $newKey));
		} else
		{
			echo '-1';
		}
	}


	private function generateRandomString($length = 10)
	{
		$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$randomString = '';
		for($i = 0; $i < $length; $i++)
		{
			$randomString .= $characters[rand(0, strlen($characters) - 1)];
		}
		return $randomString;
	}


	public function map()
	{
		$this->set('title_for_layout', __('Map'));
		$this->Post->recursive = -1;
		$visiblePosts = $this->Post->find('all', array(
			'conditions' => array(
				'Post.permissions <= ' => ($this->Session->read('permissions') == null) ? 0 : $this->Session->read('permissions'),
				'Post.isDeleted' => 0,
				'Post.lat <>' => '')));

		if(count($visiblePosts))
		{
			//Determine the distance to closest other picture. This will determine the minimum
			//zoom required to show the picture in the map.
			foreach($visiblePosts as $key => $thisPost)
			{
				$fromLat = $thisPost['Post']['lat'];
				$fromLong = $thisPost['Post']['long'];
				$minDistance = 40075000; //Circumference of the Earth, so really the min is half this
	
				if($fromLat)
				{
					foreach($visiblePosts as $loopPost)
					{
						$toLat = $loopPost['Post']['lat'];
						$toLong = $loopPost['Post']['long'];

						if(($toLat) && ($fromLat != $toLat) && ($fromLong != $toLong))
						{
							$thisDistance = $this->vincentyGreatCircleDistance($fromLat, $fromLong, $toLat, $toLong);
							if($thisDistance < $minDistance)
							{
								$minDistance = $thisDistance;
							}
						}
					}
				}
				$visiblePosts[$key]['Post']['minZoom'] = $this->getMinMapZoom($minDistance);
			}
		} else
		{
			$this->Session->setFlash('<h4>' . __('There are no posts with map data') . '!</h4>', 'flash_yellow');
		}
		$this->set('visiblePosts', $visiblePosts);
	}


	private function getMinMapZoom($dist)
	{
		//Minimum zoom for a click to show you the actual photo. This is in
		//case two or more markers are very close together.
		if($dist > 150000)
		{
			$minZoom = 0;
		} elseif($dist > 75000)
		{
			$minZoom = 3;
		} elseif($dist > 40000)
		{
			$minZoom = 7;
		} elseif($dist > 20000)
		{
			$minZoom = 9;
		} elseif($dist > 1500)
		{
			$minZoom = 12;
		} else
		{
			$minZoom = 16;
		}
		return $minZoom;
	}


	public function index()
    {
		$this->set('meData', $this->Me->find('first'));

		$visiblePosts = $this->Post->find('all', array(
			'conditions' => array(
				'Post.permissions <= ' => ($this->Session->read('permissions') == null) ? 0 : $this->Session->read('permissions'),
				'Post.isDeleted' => 0),
			'order' => 'Post.id DESC'));
		$this->set('visiblePosts', $visiblePosts);
	}


	public function view($thisId, $postKey = null)
	{
		$thisId = intval($thisId);
		$this->Post->Behaviors->load('Containable');
		$this->Post->contain(array('Comment.User'));
		$thisPost = $this->Post->find('first', array(
			'conditions' => array(
				'Post.permissions <= ' => ($this->Session->read('permissions') == null) ? 0 : $this->Session->read('permissions'),
				'Post.isDeleted' => 0,
				'Post.id' => $thisId)));

		//If he doesn't have permissions, but $postKey is set, we check for that
		if( (count($thisPost) == 0) && ($postKey) )
		{
			$this->Post->Behaviors->load('Containable');
			$this->Post->contain(array('Comment.User'));
			$thisPost = $this->Post->find('first', array(
			'conditions' => array(
				'Post.key' => $postKey,
				'Post.isDeleted' => 0,
				'Post.id' => $thisId)));
		}

		$this->set('thisPost', $thisPost);
	}


	public function delete($postId = null)
	{
		if(!$postId)
		{
			$this->Session->setFlash('<h4>' . __('Error') . '!</h4>', 'flash_red');
			return;
		}

		//For now we just mark it as deleted in the DB.
		$updatePost = array();
		$updatePost['Post']['id'] = intval($postId);
		$updatePost['Post']['isDeleted'] = 1;
		$this->Post->save($updatePost);
		$this->Session->setFlash('<h4>' . __('Deleted') . '!</h4>', 'flash_green');
		$this->redirect('/');
	}


	public function edit($postId = null)
	{
		$this->set('title_for_layout', __('Edit'));

		//We're saving.
		if($this->request->is('post') || $this->request->is('put'))
		{
			//Clean data.
			$postData = $this->request->data['Post'];
			$postId = intval($postData['id']);
			$title = trim($postData['title']);
			$text = trim($postData['text']);
			$filename = trim($postData['filename']);
			$isBg = intval($postData['isBg']);
			$isQuote = intval($postData['isQuote']);
			$isStatus = intval($postData['isStatus']);
			$permissions = intval($postData['permissions']);
			$picName = trim($postData['pic']['name']);
			//$lat, $long = We don't check these. If they are there, fine.


			//No pic, title or text found.
			if(($filename == '') && ($picName == '') && ($title == '') && ($text == ''))
			{
				if($postId)
				{
					//Check if the user just wants to delete the existing pic. In this case we add it back to $this->request->data
					$hadPic = $this->Post->find('first', array('conditions' => array('Post.id' => $postId)));
					$this->request->data['Post']['filename'] = $hadPic['Post']['filename'];
					$this->Session->setFlash('<h4>' . __('You must include a title and/or text, or delete the post') . '!</h4>', 'flash_red');
				} else
				{
					$this->Session->setFlash('<h4>' . __('You must include a title and/or text') . '!</h4>', 'flash_red');
				}
				return;
			}

			//Tried to upload file but has error.
			if($picName && ($postData['pic']['error'] != 0))
			{
				$this->Session->setFlash('<h4>' . __('Error with uploaded file') . '!</h4>', 'flash_red');
				return;
			}

			//Set isBg but isn't an image.
			if($isBg && ($picName == '') && ($filename == ''))
			{
				$isBg = 0;
				$this->request->data['Post']['isBg'] = 0;
			}

			//There was already a pic and wants to upload a new one.
			if($filename && $picName)
			{
				$this->deletePic($filename);
				$this->request->data['Post']['filename'] = '';
				$this->request->data['Post']['exif'] = '';
			}

			//Check if just wants to delete pic from existing post.
			if(($filename == '') && ($picName == '') && $postId)
			{
				$oldPic = $this->Post->find('first', array('conditions' => array('Post.id' => $postId)));
				if($oldPic)
				{
					if($oldPic['Post']['filename'] != '')
					{
						$this->deletePic($oldPic['Post']['filename']);
						$this->request->data['Post']['exif'] = '';
					}
				}
			}

			//New pic, so we try to process it.
			if($picName)
			{
				$newFilename = $this->uploadPicture($postData['pic']);
				if($newFilename)
				{
					//Update filename
					$this->request->data['Post']['filename'] = $newFilename;
					$this->request->data['Post']['exif'] = $this->getExifData($newFilename);
				} else
				{
					$this->Session->setFlash('<h4>' . __('Not saved')  . '!</h4><p>' . __('Error with uploaded file') . '!</p>', 'flash_red');
					return;
				}
			}

			//Is new, so we create a new key
			if(!$postId)
			{
				$this->request->data['Post']['key'] = $this->generateRandomString($this->randomKeyLength);
			}

			//Save the data.
			$this->Post->save($this->request->data);

			//Get ID
			if($postId)
			{
				$lastInsertId = $postId;
			} else
			{
				$lastInsertId = $this->Post->getLastInsertID();
			}


			//Unset isBg from all other posts if this was selected as bg.
			if($isBg)
			{
				$this->Post->updateAll(array('isBg' => 0), array('id != ' => $lastInsertId));
			}

			$this->Session->setFlash('<h4>' . __('Post was successfully saved')  . '!</h4>', 'flash_green');
			$this->redirect(array('controller' => 'posts', 'action' => 'view', $lastInsertId));

		} else
		{
			if($postId != null)
			{
				$postId = intval($postId);
				$this->request->data = $this->Post->find('first', array(
					'conditions' => array('Post.id' => $postId, 'Post.isDeleted' => '0')));
			}
		}

	}


	private function deletePic($filename)
	{
		$picUploadDirectory = Configure::read('PIC_UPLOAD_DIRECTORY');

		//Delete original pic.
		unlink(IMAGES . $picUploadDirectory . '/original/' . $filename);

		//Delete thumbnails
		$thumbnailMaxDims = Configure::read('PIC_THUMBNAIL_SIZES');
		foreach($thumbnailMaxDims as $thisThumbnailDim)
		{
			unlink(IMAGES . $picUploadDirectory . '/'. $thisThumbnailDim . '/' . $filename);
		}
	}


    private function uploadPicture($picData)
    {
		ini_set('memory_limit', Configure::read('PIC_MEMORY_LIMIT'));
		require_once(APP . 'Vendor' . DS. 'php_image_magician.php');

		//Valid file extensions.
		$validExtensions = array('.jpg', '.jpeg', '.png');
		$extension = strrchr($picData['name'], '.');
		$extension = strtolower($extension);

		//Error checking.
		if(!is_array($picData) || ($picData['error'] != 0) || !in_array($extension, $validExtensions))
		{
			return null;
		}

		//Define a random unique name for the file and check if it already exists. Use of the ID or similar
		//is avoided to deter just typing in the image URL to circumvent permissions.
		do
		{
			$newFilename = rand(20, 70) . md5(time()) . $extension; //Randomish
			$findNameCount = $this->Post->find('count', array('conditions' => array('Post.filename' => $newFilename)));
		} while($findNameCount > 0);

		$picUploadDirectory = Configure::read('PIC_UPLOAD_DIRECTORY');

		//Move original picture
		move_uploaded_file($picData['tmp_name'], IMAGES . $picUploadDirectory . '/original/' . $newFilename);

		//Create each thumbnail (if needed) and move to respective directory.
		$thumbnailMaxDims = Configure::read('PIC_THUMBNAIL_SIZES');
		foreach($thumbnailMaxDims as $thisThumbnailDim)
		{
			$magicianObj = new imageLib(IMAGES . $picUploadDirectory . '/original/' . $newFilename);
			if( ($magicianObj->getOriginalWidth() > $thisThumbnailDim) || ($magicianObj->getOriginalHeight() > $thisThumbnailDim))
			{
				$magicianObj->resizeImage($thisThumbnailDim, $thisThumbnailDim, 'auto', false);
			}
			$magicianObj->saveImage(IMAGES . $picUploadDirectory . DS. $thisThumbnailDim . DS . $newFilename, 75);
			unset($magicianObj);
		}

		return $newFilename;
	}


	private function getExifData($filename)
	{
		$picUploadDirectory = Configure::read('PIC_UPLOAD_DIRECTORY');
		$filename = IMAGES . $picUploadDirectory . '/original/' . $filename;

		//Make sure it's JPG or TIFF
		$picType = exif_imagetype($filename);
		if($picType != IMAGETYPE_JPEG)
		{
			return '';
		}

		//Generate exif data
		$exifArray = exif_read_data($filename);

		//@ because it's not a biggy if it doesn't work.
		@$iso = $this->getExifParam($exifArray['ISOSpeedRatings']);
		@$aperture = $this->getExifParam($exifArray['FNumber']);
		@$focalDist = $this->getExifParam($exifArray['FocalLength']);
		@$shutterSpeed = $this->getExifParam($exifArray['ExposureTime']);

		$exifData = '';
		$exifData .= 'Make: ' . ($exifArray['Make'] ? $exifArray['Make'] : '---') . '<br />';
		$exifData .= 'Model: ' . ($exifArray['Model'] ? $exifArray['Model'] : '---') . '<br />';
		$exifData .= 'ISO: ' . ($iso ? $iso : '---') . '<br />';
		$exifData .= 'Aperture: ' . ($aperture ? 'f/' . $aperture : '---') . '<br />';
		$exifData .= 'Focal dist: ' . ($focalDist ? $focalDist . 'mm' : '---') . '<br />';
		$exifData .= 'Shutter speed: ' . ($shutterSpeed ? $shutterSpeed . 's' : '---') . '<br />';

		return $exifData;
	}


	private function getExifParam($thisParam)
	{
		$retVal = '';
		if(strpos($thisParam, '/'))
		{
			list($num, $den) = $this->simplifyFraction(explode('/', $thisParam));
			if(($den == 1) || ($den == 10))
			{
				$retVal = $num / $den;
			} else
			{
				$retVal = $num . '/' . $den;
			}
		} else
		{
			$retVal = $thisParam;
		}
		return $retVal;
	}


	private function simplifyFraction($numDenArray)
	{
		$g = $this->gcd($numDenArray[0], $numDenArray[1]);
		return array($numDenArray[0] / $g, $numDenArray[1] / $g);
	}


	private function gcd($a, $b)
	{
		$a = abs($a);
		$b = abs($b);

		if($a < $b)
		{
			list($b,$a) = array($a, $b);
		}

		if($b == 0)
		{
			return $a;
		}

		$r = $a % $b;
		while($r > 0)
		{
			$a = $b;
			$b = $r;
			$r = $a % $b;
		}
		return $b;
	}

	/**
	* Taken from: http://stackoverflow.com/a/10054282
	* Calculates the great-circle distance between two points, with
	* the Vincenty formula.
	* @param float $latitudeFrom Latitude of start point in [deg decimal]
	* @param float $longitudeFrom Longitude of start point in [deg decimal]
	* @param float $latitudeTo Latitude of target point in [deg decimal]
	* @param float $longitudeTo Longitude of target point in [deg decimal]
	* @param float $earthRadius Mean earth radius in [m]
	* @return float Distance between points in [m] (same as earthRadius)
	*/
	private function vincentyGreatCircleDistance($latitudeFrom, $longitudeFrom, $latitudeTo, $longitudeTo, $earthRadius = 6371000)
	{
		//Convert from degrees to radians
		$latFrom = deg2rad($latitudeFrom);
		$lonFrom = deg2rad($longitudeFrom);
		$latTo = deg2rad($latitudeTo);
		$lonTo = deg2rad($longitudeTo);

		$lonDelta = $lonTo - $lonFrom;
		$a = pow(cos($latTo) * sin($lonDelta), 2) +
		pow(cos($latFrom) * sin($latTo) - sin($latFrom) * cos($latTo) * cos($lonDelta), 2);
		$b = sin($latFrom) * sin($latTo) + cos($latFrom) * cos($latTo) * cos($lonDelta);

		$angle = atan2(sqrt($a), $b);
		return $angle * $earthRadius;
	}


}
