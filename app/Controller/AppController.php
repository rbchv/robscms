<?php


App::uses('Controller', 'Controller');
App::import('Helper', 'Postpermission');


class AppController extends Controller
{

	public $components = array('Session', 'DebugKit.Toolbar');
	protected $Postpermission;


	public function beforeFilter()
    {

    	//Set values used around the different views
		$this->set('id', $this->Session->read('id'));
		$this->set('firstname', $this->Session->read('firstname'));
		$this->set('permissions', $this->Session->read('permissions'));


		//Used to read the different permissions
		$this->Postpermission = new PostpermissionHelper(new View());


		//Find background
		$localPost = ClassRegistry::init('Post');
		$localPost->recursive = -1;
		$findBg = $localPost->find('first', array(
			'conditions' => array('isBg' => 1, 'filename <> ' => "''"),
			'order' => 'id DESC'));

		if($findBg)
		{
			$bgPath = '/img/' . Configure::read('PIC_UPLOAD_DIRECTORY') . '/600/' . $findBg['Post']['filename'];
			if(file_exists(WWW_ROOT . $bgPath))
			{
				$this->set('bgImagePath', $bgPath);
			}
		}


		//Set the UI lang preference
		if($this->Session->check('Config.language'))
		{
			Configure::write('Config.language', $this->Session->read('Config.language'));
		}

    }

}
