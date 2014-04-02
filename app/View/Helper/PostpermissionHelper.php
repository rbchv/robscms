<?php

class PostpermissionHelper extends AppHelper
{

	public function getMaxPermission()
	{
		$permissionsArray = $this->getPermissionArray();
		return count($permissionsArray) - 1;
	}

	public function getPermissionArray()
    {
		return array(
			0 => '0 - ' . __('Public'),
			1 => '1 - ' . __('Logged in'),
			2 => '2 - ' . __('Acquintances'),
			3 => '3 - ' . __('Friends'),
			4 => '4 - ' . __('Close friends'),
			5 => '5 - ' . Configure::read('SITE_ADMIN_FIRSTNAME'),
		);
    }
}
