<?php namespace VacStatus\Update;

use VacStatus\Update\BaseUpdate;

use Cache;
use Carbon;
use DateTime;

use VacStatus\Models\UserListProfile;

/*

	STEPS TO GET LATEST TRACKED USERS

*************************************************************************************************

	->	Grab 20 rows from the 'user_list_profile' table
		->	Order By DESC
		->	The '20' is some nice number I came up with. It could be always changed around
*/

/*

	RETURN FORMAT

*************************************************************************************************

	return [
		[ (There should be many of these) (SORTED BY DESC) (NO SPECIFIC INDEX VALUE)
			profile.id
			profile.display_name
			profile.avatar_thumb

			profile_ban.vac
				-> this is the number of vac bans
			profile_ban.vac_banned_on
				-> see to convert date
					https://github.com/jung3o/VacStatus/tree/c6e626d8f8ab5f8c99db80f904275c185698c645/app/models/Profile.php#L131
			profile_ban.community
			profile_ban.trade

			users.site_admin
				-> color name (class: .admin-name)
			users.donation
				-> color name (class: .donator-name)
			users.beta
				-> color name (class: .beta-name)
		]
	]

*/

class LatestTracked extends BaseUpdate
{
	function __constructor()
	{
		$this->cacheLength = 10;
		$this->cacheName = "latestTracked";
	}

	public function getLatestTracked()
	{
		if(!$this->canUpdate()) {
			$return = $this->grabCache();
			if($return !== false) return $return;
		}

		return $this->grabFromDB();
	}

	private function grabFromDB()
	{

		$userListProfiles = UserListProfile::orderBy('user_list_profile.id', 'desc')
			->leftjoin('profile', 'user_list_profile.profile_id', '=', 'profile.id')
			->leftjoin('profile_ban', 'profile.id', '=', 'profile_ban.profile_id')
			->leftjoin('users', 'profile.small_id', '=', 'users.small_id')
			->take(20)
			->get([
				'profile.id',
				'profile.display_name',
				'profile.avatar_thumb',
				'profile.small_id',

				'profile_ban.vac',
				'profile_ban.vac_banned_on',
				'profile_ban.community',
				'profile_ban.trade',

				'users.site_admin',
				'users.donation',
				'users.beta',
			]);

		$return = [];

		foreach($userListProfiles as $userListProfile)
		{
			$vacBanDate = new DateTime($userListProfile->vac_banned_on);
			$return[] = [
				'id'			=> $userListProfile->id,
				'display_name'	=> $userListProfile->display_name,
				'avatar_thumb'	=> $userListProfile->avatar_thumb,
				'small_id'		=> $userListProfile->small_id,
				'vac'			=> $userListProfile->vac,
				'vac_banned_on'	=> $vacBanDate->format("M j Y"),
				'community'		=> $userListProfile->community,
				'trade'			=> $userListProfile->trade,
				'site_admin'	=> $userListProfile->site_admin?:0,
				'donation'		=> $userListProfile->donation?:0,
				'beta'			=> $userListProfile->beta?:0
			];
		}

		$this->updateCache($return);
		return $return;
	}

	function testTime()
	{
		$time = microtime();
		$time = explode(' ', $time);
		$time = $time[1] + $time[0];
		$start = $time;

		for($i = 0; $i <= 100; $i++)
		{
			$this->grabFromDB();
		}

		$time = microtime();
		$time = explode(' ', $time);
		$time = $time[1] + $time[0];
		$finish = $time;
		$total_time = round(($finish - $start), 4);

		var_dump('db', $total_time);

		///////////
		$time = microtime();
		$time = explode(' ', $time);
		$time = $time[1] + $time[0];
		$start = $time;

		for($i = 0; $i <= 100; $i++)
		{
			$this->grabCache();
		}

		$time = microtime();
		$time = explode(' ', $time);
		$time = $time[1] + $time[0];
		$finish = $time;
		$total_time = round(($finish - $start), 4);

		var_dump('cache', $total_time);
		dd();
	}
}