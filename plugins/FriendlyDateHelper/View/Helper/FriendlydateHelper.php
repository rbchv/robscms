<?php

/**
* FriendlydateHelper.php
* A CakePHP helper to show user friendly dates from SQL DATETIME fields.
* 
* The current version will show strings in English (default) or Spanish if
* the Configuration value 'Config.language' is set to 'esp'.
*
* @author     Roberto Chavarria
* @version    1.0
* 
*/

App::uses('AppHelper', 'View/Helper');

class FriendlydateHelper extends AppHelper
{

	private $whichLang;

	const SECONDS_PER_YEAR = 31536000;		// 365 * 24 * 60 * 60
	const SECONDS_PER_MONTH = 2592000;		// 30 * 24 * 60 * 60
	const SECONDS_PER_DAY = 86400;			// 24 * 60 * 60
	const SECONDS_PER_HOUR = 3600;			// 60 * 60
	const SECONDS_PER_MINUTE = 60;			// 60

	function __construct()
	{
		$this->whichLang = Configure::read('Config.language');
	}

	/**
	 * Converts a SQL DATETIME variable into a user friendly time such as "3 weeks ago";
	 *
	 * @param DATETIME $timeStamp MySQL DATETIME value
	 * @return string
	 */
	public function convert($timeStamp)
	{
		//Get current time. Make sure you've set your prefered timezone previously,
		//for example with `date_default_timezone_set('America/Costa_Rica');`
		$currentTime = time();

		//Get post time
		$postTime = strtotime($timeStamp);

		//Calculate difference here
		$diff = $currentTime - $postTime;

		//First, a couple of simple cases
		if($diff < 0)
		{
			if($this->whichLang == 'esp')
			{
				return 'en el futuro';
			} else
			{
				return 'in the future';
			}
		} elseif ($diff == 0)
		{
			if($this->whichLang == 'esp')
			{
				return 'ahora';
			} else
			{
				return 'now';
			}
		}


		$years 		= floor(  $diff / self::SECONDS_PER_YEAR);
		$months 	= floor( ($diff - ($years * self::SECONDS_PER_YEAR)) / self::SECONDS_PER_MONTH);
		$days 		= floor( ($diff - ($years * self::SECONDS_PER_YEAR)  - ($months * self::SECONDS_PER_MONTH)) / self::SECONDS_PER_DAY);
		$hours 		= floor( ($diff - ($years * self::SECONDS_PER_YEAR)  - ($months * self::SECONDS_PER_MONTH) - ($days * self::SECONDS_PER_DAY)) / self::SECONDS_PER_HOUR);
		$minutes 	= floor( ($diff - ($years * self::SECONDS_PER_YEAR)  - ($months * self::SECONDS_PER_MONTH) - ($days * self::SECONDS_PER_DAY) - ($hours * self::SECONDS_PER_HOUR)) / self::SECONDS_PER_MINUTE);


		$mainDescription = '';
		$additionalDescription = '';

		if($years >= 1)
		{
			//Look for fraction so we can round
			if( ($months >= 3.5) && ($months <= 9.5) )
			{
				if($this->whichLang == 'esp')
				{
					$additionalDescription = ' y medio';
				} else
				{
					$additionalDescription = ' and a half';
				}	
			} if($months > 9.5)
			{
				$years++;
			}

			if($this->whichLang == 'esp')
			{
				$mainDescription = "hace $years a&ntilde;o" . ($years == 1 ? '' : 's') . $additionalDescription;
			} else
			{
				$mainDescription = "$years " . $additionalDescription . " year" . (($additionalDescription == '' && $years <= 1) ? '' : 's') . " ago";
			}
		} else
		{

			//Same for months
			if($months >= 1)
			{
				//Look for fraction so we can round
				if( ($days >= 7) && ($days <= 21) )
				{
					if($this->whichLang == 'esp')
					{
						$additionalDescription = ' y medio';
					} else
					{
						$additionalDescription = ' and a half';
					}
				} elseif($days > 21)
				{
					$months++;
				}

				if($this->whichLang == 'esp')
				{
					$mainDescription = "hace $months mes" . ($months == 1 ? '' : 'es') . $additionalDescription;
				} else
				{
					$mainDescription = "$months month" . ($months == 1 ? '' : 's') . $additionalDescription . ' ago';
				}
			} else
			{
				//Days
				if($days >= 1)
				{
					if($this->whichLang == 'esp')
					{
						$mainDescription = "hace $days d&iacute;a" . ($days == 1 ? '' : 's');
					} else
					{
						$mainDescription = "$days day" . ($days == 1 ? '' : 's') . ' ago';
					}
				} else
				{
					//Hours
					if($hours >= 1)
					{
						if($this->whichLang == 'esp')
						{
							$mainDescription = "hace $hours hora" . ($hours == 1 ? '' : 's');
						} else
						{
							$mainDescription = "$hours hour" . ($hours == 1 ? '' : 's') . ' ago';
						}
					} else
					{
						//Minutes
						if($minutes >= 1)
						{
							if($this->whichLang == 'esp')
							{
								$mainDescription = "hace $minutes minuto" . ($minutes == 1 ? '' : 's');
							} else
							{
								$mainDescription = "$minutes minute" . ($minutes == 1 ? '' : 's') . ' ago';
							}
						} else
						{
							if($this->whichLang == 'esp')
							{
								$mainDescription = "hace unos segundos";
							} else
							{
								$mainDescription = "a few seconds ago";
							}
						}
					}
				}
			}
		}
		return $mainDescription;
    }
}
