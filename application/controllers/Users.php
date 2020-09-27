<?php
defined('BASEPATH') OR exit('No direct script access allowed');
require APPPATH . '/libraries/REST_Controller.php';
use Restserver\Libraries\REST_Controller;

//https://api.kmsp-store.com/realms/smtp_5bc46ae230cb27d5/v1/otpapikey

class Users extends REST_Controller
{
	public function __construct($config = 'rest')
	{
		parent::__construct($config);
		$this->load->database();
		$this->load->helper('url');
	}

	public function index_get($idnya = NULL)
	{
		header('Content-Type: application/json; charset=UTF-8');
		$json = array(
						'statusCode'=>200,
						'status'=>true,
						'Code'=>'00',
						'message'=>'Joss',
						'data'=>[
								'id'=>$idnya,
								'csrf'=>$this->security->get_csrf_hash()
								]
						);
		$this->response($json, 200);
	}
}
?>