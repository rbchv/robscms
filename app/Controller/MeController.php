<?php


class MeController extends AppController
{
	public $name = 'Me';
	public $uses = array('Me');



	public function beforeFilter()
	{
		parent::beforeFilter();

		//Limit some actions to only the admin
		$adminOnlyAccessableActions = array('index');
		if(in_array($this->action, $adminOnlyAccessableActions))
		{
			if($this->Session->read('permissions') < $this->Postpermission->getMaxPermission())
			{
				$this->Session->setFlash('<h4>' . __('Error') . '</h4>', 'flash_red');
				$this->redirect('/');
			}
		}
	}


	public function index()
	{
		$this->set('title_for_layout', __('About me'));

		if($this->request->is('post'))
		{
			$this->Me->save($this->request->data);
			$this->Session->setFlash('<h4>' . __('Saved') . '!</h4>', 'flash_green');
		} else
		{
			$this->request->data = $this->Me->find('first');
		}
	}

}
