<?php


CakePlugin::load('FriendlyDateHelper');


class UsersController extends AppController
{
	public $name = 'Users';
	public $uses = array('User', 'Post', 'Pref'); //DB models
	public $components = array('Curl');
	public $helpers = array('FriendlyDateHelper.Friendlydate');


	public function beforeFilter()
	{
		parent::beforeFilter();

		//Limit some actions to only the admin
		$adminOnlyAccessableActions = array('view', 'update');
		if(in_array($this->action, $adminOnlyAccessableActions))
		{
			if($this->Session->read('permissions') < $this->Postpermission->getMaxPermission())
			{
				$this->Session->setFlash('<h4>' . __('Error')  . '!</h4>', 'flash_red');
				$this->redirect('/');
			}
		}

		//Limit some actions to only logged in users
		$loggedInOnlyAccessableActions = array('prefs');
		if(in_array($this->action, $loggedInOnlyAccessableActions))
		{
			if(!$this->Session->check('permissions'))
			{
				$this->Session->setFlash('<h4>' . __('You must be logged in') . '!</h4>', 'flash_red');
				$this->redirect('/');
			}
		}
	}


	public function prefs()
	{
		if($this->request->is('post'))
		{
			$saveThis = array();
			$saveThis['Pref'] = $this->request->data['User'];
			$saveThis['Pref']['user_id'] = $this->Session->read('id');
			$this->Pref->save($saveThis);

			$langArray = Configure::read('AVAILABLE_LANGS');
			$this->Session->write('Config.language', $langArray[$saveThis['Pref']['lang']][0]);

			$this->Session->setFlash('<h4>' . __('Preferences saved') . '!</h4>', 'flash_green');
		}
		$this->Pref->recursive = -1;
		$this->request->data = $this->Pref->find('first', array('conditions' => array('user_id' => $this->Session->read('id'))));
	}


	public function index()
    {
		$this->redirect('/');
	}


	public function login()
	{
		if($this->Session->check('id'))
		{
			$this->redirect('/');
		}
		$this->set('title_for_layout', __('Log in'));
	}


	public function view()
	{
		$this->set('title_for_layout', __('Users'));
		$this->set('allUsers', $this->User->find('all'));
	}


	public function update()
	{
		if($this->request->is('post'))
		{
			$this->User->save($this->request->data);
			$this->Session->setFlash('<h4>' . __('Updated permissions successfully') . '!</h4>', 'flash_green');
		}
		$this->redirect(array('action' => 'view'));
	}


	public function logout()
	{
		$this->Session->delete('id');
		$this->Session->delete('firstname');
		$this->Session->delete('email');
		$this->Session->delete('permissions');
		$this->Session->destroy();
		$this->Session->setFlash('<h4>' . __('Logged out successfully') . '!</h4><p>' . __('Come back soon') . '!</p>', 'flash_green');
		$this->redirect('/');
	}


	public function google()
	{
		require_once APP . 'Vendor' . DS . 'OpenId/OpenId.php';

		//Lets us use the Html helper in the controller.
		App::import('Helper', 'Html');
		$myHtmlHelper = new HtmlHelper(new View());

		$this->view = 'index';

		try
		{
			$openid = new LightOpenID(FULL_BASE_URL . $myHtmlHelper->url(array('controller' => 'users', 'action' => 'google')));
			if(!$openid->mode)
			{
				$openid->identity = 'https://www.google.com/accounts/o8/id';
				$openid->realm = FULL_BASE_URL;
				$openid->required = array('namePerson/first', 'namePerson/last', 'contact/email'); // 'namePerson/friendly',  , 'contact/country/home',  'pref/language');
				$this->redirect($openid->authUrl());
			} elseif($openid->mode == 'cancel')
			{
				$this->Session->setFlash('<h4>' . __('Could not authenticate') . '!</h4><p>' . __('User cancelled the log in process') . '!</p>', 'flash_yellow');
				$this->redirect(array('action' => 'login'));
			} else
			{
				if($openid->validate())
				{
					$idUrl = $openid->identity;
					preg_match('/id\?id=(.+)$/', $idUrl, $matches);
					$googleId = trim($matches[1]);
					$allData = $openid->getAttributes();

					//A pesar de que Google nos da un "identity", como en el futuro voy a permitir ingresar por
					//Facebook mejor solo uso el email como unique identifier.

					//Otro problema es que aparentemente el GoogleId es diferente si el usuario se loggea
					//desde www.robchava.com vs robchava.com. Entonces lo mas facil es simplemente revisar el email.
					//aunque esto es una solucion a ese problema: http://stackoverflow.com/questions/5453156/what-should-i-set-realm-to-using-lightopenid-for-google-urls-to-remain-consis

					//Primero buscamos a ver si existe
					$findUser = $this->User->find('first', array('conditions' => array('User.email' => $allData['contact/email'])));
					$saveData = array();
					$printThisFirstName = '';

					if(count($findUser) > 0)
					{
						//Ya existe
						$saveData = $findUser;
						$this->Session->write('id', $findUser['User']['id']);
						$this->Session->write('permissions', $findUser['User']['permissions']);
						$this->Session->write('firstname', $findUser['User']['firstname']);

						$langArray = Configure::read('AVAILABLE_LANGS');
						//echo "A-" . $langArray[$findUser['Pref']['lang']][0] . "-A";
						$this->Session->write('Config.language', $langArray[$findUser['Pref']['lang']][0]);

						$printThisFirstName = $findUser['User']['firstname'];
					} else
					{
						//New user, have to enter all the data
						$this->newUserAlertRupert($allData);
						$this->sendWelcomeEmail($allData);

						$saveData['User']['id'] = null;
						$saveData['User']['firstname'] = $allData['namePerson/first'];
						$saveData['User']['lastname']  = $allData['namePerson/last'];
						$saveData['User']['email']     = $allData['contact/email'];
						$saveData['User']['googleid']  = $googleId;
						$saveData['User']['token']     = '';

						//Default prefs
						$saveData['Pref']['emailfreq'] = Configure::read('PREF_EMAIL_FREQ');
						$saveData['Pref']['lang'] = Configure::read('PREF_LANG');

						$this->User->saveAll($saveData);
						$this->Session->write('id', $this->User->getLastInsertID());
						$this->Session->write('firstname', $saveData['User']['firstname']);
						$this->Session->write('permissions', 0); //Lowest permission for now.

						$langArray = Configure::read('AVAILABLE_LANGS');
						$this->Session->write('Config.language', $langArray[Configure::read('PREF_LANG')][0]);

						$printThisFirstName = $saveData['User']['firstname'];
					}

					$this->saveProfilePicGoogle();
					$this->Session->setFlash('<h4>' . __('Welcome') . ', ' . $printThisFirstName . '</h4>', 'flash_green');
					$this->redirect(array('action' => 'index'));

				} else
				{
					//Could not validate
					$this->Session->setFlash('<h4>' . __('Could not authenticate user') . '!</h4><p>' . __('Please try again later') . '.</p>', 'flash_red');
					$this->redirect(array('action' => 'login'));
				}
			} //mode
		} catch(ErrorException $e)
		{
			//Could not validate
			$this->Session->setFlash('<h4>' . __('Unknown error') . '!</h4><p>' . __('Please try again later') . '.</p>', 'flash_red');
			$this->redirect(array('action' => 'login'));
		}
	}


	public function facebook()
	{

		if(!Configure::read('FB_APP_ENABLED'))
		{
			$this->Session->setFlash('<h4>' . __('Error') . '!</h4>', 'flash_red');
			$this->redirect('login');
		}


		//Lets us use the Html helper in the controller.
		App::import('Helper', 'Html');
		$myHtmlHelper = new HtmlHelper(new View());

		if(empty($_REQUEST['code']))
		{
			//Inicio del proceso. Creamos una variable de session y nos vamos al URL
			$FbSessionState = md5(uniqid(rand(), TRUE));
			$this->Session->write('fbsessionstate', $FbSessionState);

			$dialog_url = "https://www.facebook.com/dialog/oauth?client_id=" . Configure::read('FB_APP_ID') .
						"&redirect_uri=" . urlencode(FULL_BASE_URL . $myHtmlHelper->url(array('action' => 'facebook'))) .
						"&state=" . $FbSessionState .
						"&scope=email";
			$this->redirect($dialog_url);
	   } else
	   {
		   //Tenemos el code, podemos exchange el code por un user access token "that can then be used to make API requests"
		   $code = $_REQUEST['code'];
		   $curFbSessionState = $this->Session->read('fbsessionstate');

		   if($curFbSessionState && ($curFbSessionState === $_REQUEST['state']))
		   {
				$token_url = "https://graph.facebook.com/oauth/access_token?" .
						"client_id=" . Configure::read('FB_APP_ID') .
						"&redirect_uri=" . urlencode(FULL_BASE_URL . $myHtmlHelper->url(array('action' => 'facebook'))) .
						"&client_secret=" . Configure::read('FB_APP_SECRET') .
						"&code=" . $code;

				$response = $this->Curl->download($token_url);

				$params = null;
				parse_str($response, $params);

				//The body of the response that your code will receive and parse will look like this:
				//access_token=USER_ACCESS_TOKEN&expires=NUMBER_OF_SECONDS_UNTIL_TOKEN_EXPIRES
				//You should persist this User access token in your database or in a session variable in order to make further requests to the API without having to renew the authentication of the user:

				//TEST
				$graph_url = "https://graph.facebook.com/me?fields=id,email,first_name,last_name&access_token=" . $params['access_token'];
				$user = json_decode($this->Curl->download($graph_url));

				$fbUserEmail = $user->email;

				//We need that email!
				if (empty($fbUserEmail)) {
					$this->Session->setFlash('<h4>' . __('Error') . '!</h4><p>' . __('Email is required to log in') . '</p>', 'flash_red');
					$this->redirect(array('action' => 'index'));
					return;
				}

				//Primero buscamos a ver si existe
				$findUser = $this->User->find('first', array('conditions' => array('User.email' => $fbUserEmail)));
				$saveData = '';

				if(count($findUser) > 0)
				{
					//Ya existe
					$saveData = $findUser;
					$this->Session->write('id', $findUser['User']['id']);
					$this->Session->write('permissions', $findUser['User']['permissions']);
					$this->Session->write('firstname', $findUser['User']['firstname']);

					$langArray = Configure::read('AVAILABLE_LANGS');
					$this->Session->write('Config.language', $langArray[$findUser['Pref']['lang']][0]);

					$printThisFirstName = $findUser['User']['firstname'];
				} else
				{
					//New user
					$this->newUserAlertRupert($user);
					$this->sendWelcomeEmail($user);

					$saveData['User']['id'] = null;
					$saveData['User']['firstname'] = $user->first_name;
					$saveData['User']['lastname']  = $user->last_name;
					$saveData['User']['email']     = $user->email;
					$saveData['User']['facebookid'] = $user->id;

					//Default prefs
					$saveData['Pref']['emailfreq'] = Configure::read('PREF_EMAIL_FREQ');
					$saveData['Pref']['lang'] = Configure::read('PREF_LANG');

					$this->User->saveAll($saveData);
					$this->Session->write('id', $this->User->getLastInsertID());
					$this->Session->write('firstname', $saveData['User']['firstname']);
					$this->Session->write('permissions', 0); //Lowest permission for now

					$langArray = Configure::read('AVAILABLE_LANGS');
					$this->Session->write('Config.language', $langArray[Configure::read('PREF_LANG')][0]);

					$printThisFirstName = $saveData['User']['firstname'];
				}

				$this->saveProfilePicFacebook($user->id);
				$this->Session->setFlash('<h4>' . __('Welcome') . ', ' . $printThisFirstName . '</h4>', 'flash_green');
				$this->redirect(array('action' => 'index'));
			} else
			{
			   	//Update esto segun el app
				$this->Session->setFlash('<h4>' . __('Error') . '!</h4>', 'flash_red');
				$this->redirect(array('action' => 'index'));
		   	}
	   	} //else
	}


	private function saveProfilePicFacebook($fbId)
	{
		$userId = $this->Session->read('id');
		$appConstant = Configure::read('App');
		$picSavePath = WWW_ROOT . $appConstant['imageBaseUrl'] . '/users/' . $userId . '.jpg';

		$image = file_get_contents('https://graph.facebook.com/' . $fbId . '/picture?type=large');

		if($image)
		{
			file_put_contents($picSavePath, $image);
		} else
		{
			//Imagen no existe
			if(!file_exists($picSavePath))
			{
				//No hay ninguna version anterior
				copy(WWW_ROOT . $appConstant['imageBaseUrl'] . '/users/default.jpg', $picSavePath);
			}
		}
	}


	private function saveProfilePicGoogle()
	{
		$userId = $this->Session->read('id');
		$userEmail = split('@', $this->Session->read('email'));
		$usernameGmail = $userEmail[0];
		$appConstant = Configure::read('App');
		$picSavePath = WWW_ROOT . $appConstant['imageBaseUrl'] . '/users/' . $userId . '.jpg';

		$headers = get_headers('https://s2.googleusercontent.com/s2/photos/profile/' . $usernameGmail, 1);

		if(isset($headers['Location']))
		{
			//Imagen existe
			$realPicUrl = $headers['Location'];

			$ch = curl_init($realPicUrl);
			curl_setopt($ch, CURLOPT_HEADER, 0);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_BINARYTRANSFER,1);
			$rawdata = curl_exec($ch);
			curl_close($ch);

			$fp = fopen($picSavePath, 'wb');
			if($fp)
			{
				fwrite($fp, $rawdata);
				fclose($fp);
			}
		} else
		{
			//Imagen no existe
			if(!file_exists($picSavePath))
			{
				//No hay ninguna version anterior
				copy(WWW_ROOT . $appConstant['imageBaseUrl'] . '/users/default.jpg', $picSavePath);
			}
		}
	}


	private function sendWelcomeEmail($userData)
	{
		App::uses('CakeEmail', 'Network/Email'); 
		$email = new CakeEmail();
		$userName = '';

		if(is_object($userData))
		{
			//Is a Fb login
			$firstName = $userData->first_name;
			$email->to(array($userData->email => $userData->first_name . ' ' . $userData->last_name));
		} else
		{
			//Is a Google login
			$firstName = $userData['namePerson/first'];
			$email->to(array($userData['contact/email'] => $userData['namePerson/first'] . ' ' . $userData['namePerson/last']));
		}

		//Since we don't know the user's preference at this point, we send it in English.
		$subject = 'Welcome to ' . Configure::read('SITE_NAME_SHORT');
		$email->emailFormat('html');
		$email->from(array(Configure::read('SITE_ADMIN_EMAIL') => Configure::read('SITE_ADMIN_NAME')));
		$email->replyTo(array(Configure::read('SITE_ADMIN_EMAIL') => Configure::read('SITE_ADMIN_NAME')));
		$email->subject('Welcome to ' . Configure::read('SITE_NAME_SHORT') . '!');

		try
		{
			$email->send('<br/><br/><br/>Hi ' . $firstName . '!<br/><br/><b>' . $subject . '</b><br/><br/>Remember you must wait until you get additional permissions to browse the site. You can only see a few posts at the moment :)<br/><br/><a href="' . FULL_BASE_URL . '">' . FULL_BASE_URL . '</a>');
		} catch(Exception $e) {}
	}


	private function newUserAlertRupert($userData)
	{
		App::uses('CakeEmail', 'Network/Email'); 
		$subject = __('New user in') . ' ' . Configure::read('SITE_NAME_SHORT') . '!';
		$email = new CakeEmail();
		$email->to(array(Configure::read('SITE_ADMIN_EMAIL') => Configure::read('SITE_ADMIN_NAME')));
		$email->from(array(Configure::read('SITE_ADMIN_EMAIL') => Configure::read('SITE_ADMIN_NAME')));
		$email->subject($subject);
		$email->emailFormat('html');

		try
		{
			if(is_object($userData))
			{
				//Is a Fb login
				$email->send("<br/><br/>\n\n" . $subject . "<br/><br/>\n\n" . $userData->first_name . ' ' . $userData->last_name . ' (' . $userData->email . ')' . "<br/><br/>\n\n<a href=\"" . FULL_BASE_URL . "\">" . FULL_BASE_URL . "</a>");
			} else
			{
				//Is a Google login
				$email->send("<br/><br/>\n\n" . $subject . "<br/><br/>\n\n" . $userData['namePerson/first'] . ' ' . $userData['namePerson/last'] . ' (' . $userData['contact/email'] . ')' . "<br/><br/>\n\n<a href=\"" . FULL_BASE_URL . "\">" . FULL_BASE_URL . "</a>");
			}
		}catch(Exception $e) {}
	}


}
