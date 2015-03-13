<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Users extends CI_Controller {


	function __construct()
	{
		parent::__construct();

		// can only be called from the command line
		// if (!$this->input->is_cli_request()) {
		//     exit('Direct access is not allowed');
		// }

		// can only be run in the development environment
		if (ENVIRONMENT !== 'development') {
			exit('Wowsers! You don\'t want to do that!');
		}

		// initiate faker
		$this->faker = Faker\Factory::create();

		$this->load->helper('url');

		// init
		$this->init();
	}

	function init()
	{
		$config['hostname'] = "localhost";
		$config['username'] = "coherendz";
		$config['password'] = "coherendz";
		$config['database'] = "testdb";
		$config['dbdriver'] = "mysqli";
		$config['dbprefix'] = "";
		$config['pconnect'] = TRUE;
		$config['db_debug'] = TRUE;
		$config['cache_on'] = FALSE;
		$config['cachedir'] = "";
		$config['char_set'] = "utf8";
		$config['dbcollat'] = "utf8_general_ci";

		$this->load->database($config);

		$this->table_users     		= "users";
		$this->table_profiles  		= "profiles";
	}

	/**
	 * seed local database
	 */
	function seed($limit=10000)
	{
		if (FALSE == $limit) {
		// purge existing data
			$this->_truncate_db();
		}

		// seed users
		$this->_seed_users($limit);
	}

	/**
	 * seed users
	 *
	 * @param int $limit
	 */
	function _seed_users($limit)
	{
		$this->benchmark->mark('code_start');

		$this->load->helper('string');

		$this->course 	= array("CS", "IT", "Mech.", "Civil");
		$this->degree 	= array("B.Tech", "B.E", "MBA", "PGDM");
		$this->in_name 	= array("High School", "University", "College", "Business School");


		// create a bunch of base buyer accounts
		for ($i = 0; $i < $limit; $i++) {

			printf("seeding %d of %d user(s)<br>\n",$i+1,$limit);

			$this->user_id 		= $this->generate_user_id();
			$this->email_id 	= $this->faker->email;
			$this->created_date = time();
			$this->updated_date = $this->created_date;
			$mt_rand_boolean	= mt_rand(0, 1);
			$this->site_id  	= mt_rand(0, 1) ? "151336557" : "151323689";

			$data = array(
			              'user_id'         => $this->user_id,
			              'password'        => md5("123456"),
			              'email_id'        => $this->email_id,
			              'status'          => mt_rand(1, 2),
			              'created_date'    => $this->created_date,
			              'updated_date'    => $this->updated_date,
			              );

			// create user
			$this->db->insert($this->table_users,$data);

			// create profile
			$profile_id = $this->_create_profile();

			// create user profile mapping
			// $this->_create_user_profile($user_id, $profile_id);

			// create profile email
			// $this->_create_profile_email($user_id, $profile_id, $email_id);

			// create member
			// $this->_create_member($user_id, $site_id);

			// email verifications (optional)
			// $this->_create_email_verify($user_id, $email_id);

			// create profile address
			// $this->_create_profile_address($user_id, $profile_id, $site_id);

			// create profile contact
			// $this->_create_profile_contact($user_id, $profile_id, $site_id);

			// create profile education
			// $this->_create_profile_education($user_id, $profile_id, $site_id);

			// create profile work
			// $this->_create_profile_work($user_id, $profile_id, $site_id);
		}

		$this->benchmark->mark('code_end');
		echo "Time (H M s) -> ";
		// seconds to hours
		echo $this->sec2hms($this->benchmark->elapsed_time('code_start','code_end'), TRUE);
		// memory
		echo "\n Memory -> ";
		echo $this->convert(memory_get_usage(TRUE));

		echo PHP_EOL;
	}

	public function _create_profile()
	{
		$mt_rand = mt_rand(0, 1);

		$p = array(
		           'user_id'        => $this->user_id,
		           'profile'		=> $this->get_profile(),
		           'address'		=> $this->get_address(),
		           'contact'		=> $this->get_contact(),
		           'education'		=> $this->get_education(),
		           'work'			=> $this->get_work(),
		           'timezone' 		=> $this->faker->timezone,
		           'created'  		=> $this->created_date,
		           'updated'   		=> $this->updated_date,
		           );

		$this->db->insert($this->table_profiles, $p);

		// index elasticsearch
		$this->index_elasticsearch($p);

		return $this->db->insert_id();
	}

	public function get_profile($json=TRUE)
	{
		$mt_rand = mt_rand(0, 1);
		$profile = array(
		                 'email_id' 	=> $this->email_id,
		                 'first_name'   => $this->faker->firstName,
		                 'middle_name'  => "",
		                 'last_name'    => $this->faker->lastName,
		                 'salutation'   => $this->faker->title,
		                 'birthday' 	=> $this->faker->dateTimeThisCentury->format('Y-m-d'),
		                 'gender' 		=> $mt_rand ? "M" : "F",
		                 'picture' 		=> $this->faker->imageURL(),
		                 'relationship' => mt_rand(0, 3),
		                 'headline' 	=> $this->faker->realText(150),
		                 'about_me' 	=> $this->faker->realText(150),
		                 'score' 		=> (5 % mt_rand(5, 10)) ? 5 * mt_rand(1, 26) : 10 * mt_rand(1, 13),
		                 );

		return ($json) ? json_encode($profile) : $profile;
	}

	public function get_address($json=TRUE)
	{
		$address =array();
		$mt_rand = mt_rand(1, 3);
		for ($i=0; $i < $mt_rand; $i++) {
			$address[$i] = array(
			                     'address' 		=> $this->faker->streetAddress,
			                     'city' 		=> $this->faker->city,
			                     'state' 		=> $this->faker->state,
			                     'country' 		=> $this->faker->country,
			                     'pincode' 		=> $this->faker->postcode,
			                     'type'	=> mt_rand(1, 2),
			                     'current'		=> mt_rand(0, 1),
			                     );
		}

		return ($json) ? json_encode($address) : $address;
	}

	public function get_contact($json=TRUE)
	{
		$contact =array();
		$mt_rand = mt_rand(1, 4);

		for ($i=0; $i < $mt_rand; $i++) {
			$contact[$i] = array(
			                     'number'	=> $this->faker->phoneNumber,
			                     'type'		=> $mt_rand,
			                     );
		}
		return ($json) ? json_encode($contact) : $contact;
	}

	public function get_education($json=TRUE)
	{
		$education =array();
		$mt_rand = mt_rand(0, 1);
		$e_rand = mt_rand(1, 4);
		$end_date = $this->faker->dateTimeThisCentury->format('Y');

		for ($i=0; $i < $e_rand; $i++) {
			$education[$i] = array(
			                       'name' 			=> $this->faker->state."-".$this->in_name[array_rand($this->in_name)],
			                       'type' 			=> $e_rand,
			                       'course'			=> $this->course[array_rand($this->course)],
			                       'degree'			=> $this->degree[array_rand($this->degree)],
			                       'current'		=> $mt_rand,
			                       'start_date'		=> $end_date - mt_rand(3, 4),
			                       'end_date'		=> $end_date,
			                       );
		}

		return ($json) ? json_encode($education) : $education;
	}

	public function get_work($json=TRUE)
	{
		$work = array();
		$mt_rand 	= mt_rand(0, 1);
		$w_rand 	= mt_rand(1, 4);
		$end_year 	= $this->faker->dateTimeThisCentury->format('Y');
		$end_month 	= $this->faker->dateTimeThisCentury->format('m');

		for ($i=0; $i <$w_rand ; $i++) {
			$work[$i] = array(
			                  'organization'=> $this->faker->company,
			                  'position'  	=> $this->faker->catchPhrase,
			                  'function'  	=> $this->faker->bs,
			                  'location'	=> $this->faker->city,
			                  'start_month'	=> $end_month - $mt_rand,
			                  'end_month'	=> $end_month,
			                  'start_year'	=> $end_year- mt_rand(1,20),
			                  'end_year'	=> $end_year,
			                  'current'		=> $mt_rand,
			                  );
		}

		return ($json) ? json_encode($work) : $work;
	}

	public function _create_user_profile($user_id='', $profile_id='')
	{
		$u_p = array(
		             'user_id' 		=> $user_id,
		             'profile_id' 	=> $profile_id,
		             'status'		=> mt_rand(1, 3),
		             );

		$this->db->insert($this->table_user_profiles, $u_p);
	}

	public function _create_profile_email($user_id='', $profile_id='', $email_id='')
	{
		$mt_rand = mt_rand(0, 1);

		$p_e =  array(
		              'user_id' 	=> $user_id,
		              'profile_id' 	=> $profile_id,
		              'email_id' 	=> $email_id,
		              'email_type'	=> mt_rand(1, 2),
		              'status'		=> mt_rand(1, 2),
		              'deleted'		=> $mt_rand,
		              'suggestion'	=> $mt_rand,
		              'show_flag'	=> $mt_rand,
		              'by_admin'	=> $mt_rand,
		              'created_date'=> $this->created_date,
		              'created_by'  => $user_id,
		              'updated_date'=> $this->updated_date,
		              'updated_by'  => $user_id,
		              );
		$this->db->insert($this->table_profile_emails, $p_e);
	}

	public function _create_member($user_id='', $site_id='')
	{
		$m = array(
		           'user_id' 		=> $user_id,
		           'user_type'		=> mt_rand(1, 3),
		           'label'			=> "Batch of ". $this->faker->dateTimeThisCentury->format('Y').",".$this->course[array_rand($this->course)],
		           'step'			=> mt_rand(1, 4),
		           'status'			=> mt_rand(-2, 3),
		           'verified'		=> mt_rand(1, 2),
		           'site_id'			=> $site_id,
		           'deleted'			=> mt_rand(0,1),
		           'approved_date'	=> $this->created_date,
		           'created_date'	=> $this->created_date,
		           'updated_date'	=> $this->updated_date,
		           );
		$this->db->insert($this->table_members, $m);
	}

	public function _create_profile_address($user_id='', $profile_id='', $site_id='')
	{
		$mt_rand = mt_rand(0, 1);
		$p_a =  array(
		              'user_id' 	=> $user_id,
		              'profile_id' 	=> $profile_id,
		              'address' 	=> $this->faker->streetAddress,
		              'city' 		=> $this->faker->city,
		              'state' 		=> $this->faker->state,
		              'country' 	=> $this->faker->country,
		              'pincode' 	=> $this->faker->postcode,
		              'address_type'=> mt_rand(1, 2),
		              'current'		=> $mt_rand,
		              'site_id'		=> $site_id,
		              'deleted'		=> $mt_rand,
		              'suggestion'	=> $mt_rand,
		              'show_flag'	=> $mt_rand,
		              'by_admin'	=> $mt_rand,
		              'created_date'=> $this->created_date,
		              'created_by'  => $user_id,
		              'updated_date'=> $this->updated_date,
		              'updated_by'  => $user_id,
		              );
		$this->db->insert($this->table_profile_address, $p_a);
	}

	public function _create_profile_contact($user_id='', $profile_id='', $site_id='')
	{
		$mt_rand = mt_rand(0, 1);

		$p_c = array(
		             'user_id' 		=> $user_id,
		             'profile_id' 		=> $profile_id,
		             'contact_number' 	=> $this->faker->phoneNumber,
		             'contact_type'	=> mt_rand(1, 4),
		             'site_id'			=> $site_id,
		             'deleted'			=> $mt_rand,
		             'suggestion'		=> $mt_rand,
		             'show_flag'		=> $mt_rand,
		             'by_admin'		=> $mt_rand,
		             'created_date'	=> $this->created_date,
		             'created_by'  	=> $user_id,
		             'updated_date'	=> $this->updated_date,
		             'updated_by'  	=> $user_id,
		             );
		$this->db->insert($this->table_profile_contact, $p_c);
	}

	public function _create_profile_education($user_id='', $profile_id='', $site_id='')
	{
		$mt_rand = mt_rand(0, 1);
		$end_date = $this->faker->dateTimeThisCentury->format('Y');

		$p_e = array(
		             'user_id' 		=> $user_id,
		             'profile_id' 		=> $profile_id,
		             'institute_name'  => $this->faker->state."-".$this->in_name[array_rand($this->in_name)],
		             'institute_type'  => $mt_rand,
		             'course'			=> $this->course[array_rand($this->course)],
		             'degree'			=> $this->degree[array_rand($this->degree)],
		             'current'			=> $mt_rand,
		             'start_date'		=> $end_date - mt_rand(3, 4),
		             'end_date'		=> $end_date,
		             'site_id'			=> $site_id,
		             'deleted'			=> $mt_rand,
		             'suggestion'		=> $mt_rand,
		             'show_flag'		=> $mt_rand,
		             'by_admin'		=> $mt_rand,
		             'created_date'	=> $this->created_date,
		             'created_by'  	=> $user_id,
		             'updated_date'	=> $this->updated_date,
		             'updated_by'  	=> $user_id,
		             );
		$this->db->insert($this->table_profile_education, $p_e);
	}

	public function _create_profile_work($user_id='',$profile_id='',$site_id='')
	{
		$mt_rand = mt_rand(0, 1);
		$end_year = $this->faker->dateTimeThisCentury->format('Y');
		$end_month = $this->faker->dateTimeThisCentury->format('m');

		$p_w = array(
		             'user_id' 		=> $user_id,
		             'profile_id' 		=> $profile_id,
		             'organization'  	=> $this->faker->company,
		             'position'  		=> $this->faker->catchPhrase,
		             'function'  		=> $this->faker->bs,
		             'location'		=> $this->faker->city,
		             'start_month'		=> $end_month - $mt_rand,
		             'end_month'		=> $end_month,
		             'start_year'		=> $end_year- mt_rand(1,20),
		             'end_year'		=> $end_year,
		             'current'			=> $mt_rand,
		             'site_id'			=> $site_id,
		             'deleted'			=> $mt_rand,
		             'suggestion'		=> $mt_rand,
		             'show_flag'		=> $mt_rand,
		             'by_admin'		=> $mt_rand,
		             'created_date'	=> $this->created_date,
		             'created_by'  	=> $user_id,
		             'updated_date'	=> $this->updated_date,
		             'updated_by'  	=> $user_id,
		             );
		$this->db->insert($this->table_profile_work, $p_w);
	}

	public function _create_email_verify($user_id='', $email_id='')
	{
		$e_v = array(
		             'user_id' 		=> $user_id,
		             'email_id'		=> $email_id,
		             'hash'			=> random_string('alnum', 32),
		             'type'			=> mt_rand(1, 5),
		             'status'			=> mt_rand(1, 2),
		             'created_date'	=> $this->created_date,
		             );

		$this->db->insert($this->table_email_verify, $e_v);
	}


	private function _truncate_db()
	{
		$this->db->truncate($this->table_users);
		$this->db->truncate($this->table_profiles);
	}

	function generate_user_id()
	{
		$user_id    =   random_string('numeric', 10) .  mt_rand(999, 99999);

		// check if the user id exist in v4_users, if present generate new user id
		$where["user_id"] = $user_id;

		$is_record_exist = $this->db->get_where($this->table_users,$where);

		if ($is_record_exist->num_rows() > 0) {
			$this->generate_user_id();
		} else {
			return $user_id;
		}
	}
	function convert($size)
	{
		$unit=array('b','kb','mb','gb','tb','pb');
		return @round($size/pow(1024,($i=floor(log($size,1024)))),2).' '.$unit[$i];
	}

	function sec2hms ($sec, $padHours = false)
	{

    	// start with a blank string
		$hms = "";

	    // do the hours first: there are 3600 seconds in an hour, so if we divide
    	// the total number of seconds by 3600 and throw away the remainder, we're
    	// left with the number of hours in those seconds
		$hours = intval(intval($sec) / 3600);

	    // add hours to $hms (with a leading 0 if asked for)
		$hms .= ($padHours)
		? str_pad($hours, 2, "0", STR_PAD_LEFT). ":"
		: $hours. ":";

    	// dividing the total seconds by 60 will give us the number of minutes
    	// in total, but we're interested in *minutes past the hour* and to get
	    // this, we have to divide by 60 again and then use the remainder
		$minutes = intval(($sec / 60) % 60);

	    // add minutes to $hms (with a leading 0 if needed)
		$hms .= str_pad($minutes, 2, "0", STR_PAD_LEFT). ":";

    	// seconds past the minute are found by dividing the total number of seconds
	    // by 60 and using the remainder
		$seconds = intval($sec % 60);

    	// add seconds to $hms (with a leading 0 if needed)
		$hms .= str_pad($seconds, 2, "0", STR_PAD_LEFT);

	    // done!
		return $hms;

	}

	public function prepare_index_elasticsearch($index="")
	{
		$elastic_index = "users/";
		$elastic_type  = "s/";
		$url = "http://localhost:9200/";

		$url = "http://localhost:9200/";
		$url .= $elastic_index.$elastic_type.$index["user_id"]."?pretty";

		$this->curl_elastic($url, json_encode($index), "PUT");
	}

	public function index_elasticsearch($index="")
	{
		echo "Elastic search indexing<br><br>\n\n";
		echo "<br>\n";

		$this->prepare_index_elasticsearch($index);

		echo "<br>\n";
	}

	public function curl_elastic($url="", $params="", $method="GET")
	{
		// set curl options
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,$url);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ch, CURLOPT_POSTFIELDS,$params);

		// catch the output
		$output = curl_exec ($ch);

		print_r($output);
		echo "<br>\n";

		// close the connection
		curl_close ($ch);
	}
}
