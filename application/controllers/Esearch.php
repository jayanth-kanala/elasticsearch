<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Esearch extends CI_Controller {

	function __construct()
	{
		parent::__construct();
	}

	/**
	 * Index Page for this controller.
	 *
	 * Maps to the following URL
	 * 		http://example.com/index.php/welcome
	 *	- or -
	 * 		http://example.com/index.php/welcome/index
	 *	- or -
	 * Since this controller is set as the default controller in
	 * config/routes.php, it's displayed at http://example.com/
	 *
	 * So any other public methods not prefixed with an underscore will
	 * map to /index.php/welcome/<method_name>
	 * @see http://codeigniter.com/user_guide/general/urls.html
	 */
	public function index()
	{
		$data["content"] = "esearch";
		$data["js"] = array("esearch");
		$this->load->view('template', $data);
	}

	public function get()
	{
		$output = array();
		$q = $this->input->post("q", TRUE);
		$_search = trim($q);

		$elastic_index = "users/";
		$elastic_type  = "s/";
		$url = "http://localhost:9200/";

		// if (strpos($q,'friends in ') !== false) {
		// 	$t = $q;
		// 	$_search = "country:".str_replace("friends in ", "", $t);
		// 	$s = array (
		// 	       'query' =>
		// 	       array (
		// 	              'bool' =>
		// 	              array (
		// 	                     'shoud' =>
		// 	                     array (
		// 	                            0 =>
		// 	                            array (
		// 	                                   'match' =>
		// 	                                   array (
		// 	                                          'address' => $t,
		// 	                                          ),
		// 	                                   ),
		// 	                            1 =>
		// 	                            array (
		// 	                                   'match' =>
		// 	                                   array (
		// 	                                          'city' => $t,
		// 	                                          ),
		// 	                                   ),
		// 	                            2 =>
		// 	                            array (
		// 	                                   'match' =>
		// 	                                   array (
		// 	                                          'state' => $t,
		// 	                                          ),
		// 	                                   ),
		// 	                            ),
		// 	                     ),
		// 	              ),
		// 	       );
		// }

		$url .= $elastic_index.$elastic_type."_search?q=".$_search."&pretty";
		// echo json_encode($s);
		// return;
		$result = $this->curl_elastic($url,"","GET");

		$temp = json_decode($result);
		// echo json_encode($temp, JSON_PRETTY_PRINT);
		// return;

		if ($temp && FALSE == isset($temp->error)) {

			$r = $temp->hits->hits;

			foreach ($r as $key => $value) {
				$output[$key] = $value->_source;
			}
			$output['total'] = $temp->hits->total;
			$output['took'] = $temp->took;
			$output['hits'] = count($r);
		}
		echo json_encode($output, JSON_PRETTY_PRINT);
	}

	private function curl_elastic($url="", $params="", $method="GET")
	{
		// set curl options
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,$url);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		if ("GET" != strtoupper($method)) {
			curl_setopt($ch, CURLOPT_POSTFIELDS,$params);
		}

		// catch the output
		$output = curl_exec ($ch);

		// close the connection
		curl_close ($ch);

		return $output;
	}
}
