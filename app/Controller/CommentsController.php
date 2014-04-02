<?php


class CommentsController extends AppController
{
	public $name = 'Comments';
	public $uses = array('Post', 'Comment');



	public function beforeFilter()
	{
		parent::beforeFilter();

		//Limit some actions to only the admin
		$adminOnlyAccessableActions = array('delete');
		if(in_array($this->action, $adminOnlyAccessableActions))
		{
			if($this->Session->read('permissions') < $this->Postpermission->getMaxPermission())
			{
				$this->Session->setFlash('<h4>' . __('Error') . '</h4>', 'flash_red');
				$this->redirect('/');
			}
		}

		//Limit some actions to only logged in users
		$loggedInOnlyAccessableActions = array('write');
		if(in_array($this->action, $loggedInOnlyAccessableActions))
		{
			if(!$this->Session->check('permissions'))
			{
				$this->Session->setFlash('<h4>' . __('You must be logged in') . '!</h4>', 'flash_red');
				$this->redirect('/');
			}
		}
	}


	public function index()
	{
		$this->redirect('/');
	}


	public function delete($id)
	{
		//First find this comment's post id
		$postInfo = $this->Comment->find('first', array('conditions' => array('Comment.id' => $id)));
		if($postInfo)
		{
			$postId = $postInfo['Post']['id'];
			$this->Comment->delete($id);
			$this->Session->setFlash('<h4>' . __('Comment deleted') . '</h4>', 'flash_green');
			$this->redirect(array('controller' => 'posts', 'action' => 'view', $postId));
		} else
		{
			$this->Session->setFlash('<h4>' . __('Error') . '</h4>', 'flash_red');
			$this->redirect('/');
		}
	}


	public function write()
	{
		if($this->request->is('post'))
		{
			//Data cleanup.
			$this->request->data['Comment']['text'] = trim($this->request->data['Comment']['text']);
			$this->request->data['Post']['id'] = intval($this->request->data['Post']['id']);
	
			//No blank comments.
			if(!$this->request->data['Comment']['text'])
			{
				$this->Session->setFlash('<h4>' . __('You didn\'t write anything in the comment') . '!</h4>', 'flash_yellow');
				$this->redirect(array('controller' => 'posts', 'action' => 'view', $this->request->data['Post']['id']));
			}

			//Verify this user has permissions to comment on this post.
			$userCanPost = $this->Post->find('count', array('conditions' => array('Post.id' => $this->request->data['Post']['id'], 'Post.permissions <= ' => $this->Session->read('permissions'))));

			if($userCanPost)
			{
				$saveThisComment = array();
				$saveThisComment['Comment']['user_id'] = $this->Session->read('id');
				$saveThisComment['Comment']['post_id'] = $this->request->data['Post']['id'];
				$saveThisComment['Comment']['text'] = $this->request->data['Comment']['text'];
				$this->Comment->save($saveThisComment);
				$this->Session->setFlash('<h4>' . __('Comment saved') . '!</h4>', 'flash_green');
				$this->redirect(array('controller' => 'posts', 'action' => 'view', $this->request->data['Post']['id']));
			} else
			{
				$this->Session->setFlash('<h4>' . __('Cannot comment') . '!</h4>', 'flash_red');
				$this->redirect(array('controller' => 'posts', 'action' => 'view', $this->request->data['Post']['id']));
			}
		} else
		{
			$this->redirect('/');
		}
	}

}
