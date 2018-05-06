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
		$config['username'] = "root";
		$config['password'] = "";
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

			$this->email_id 	= $this->faker->email;
			$this->created_date = time();
			$this->updated_date = $this->created_date;
			$mt_rand_boolean	= mt_rand(0, 1);
			$this->site_id  	= mt_rand(0, 1) ? "151336557" : "151323689";

			// create profile
			$profile_id = $this->_create_profile();
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

		$p["user_id"] = $this->db->insert_id();

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


	private function _truncate_db()
	{
		$this->db->truncate($this->table_users);
		$this->db->truncate($this->table_profiles);
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
