<?php
error_reporting(0);
defined('BASEPATH') OR exit('No direct script access allowed');
include 'vendor/autoload.php';
require APPPATH . '/libraries/REST_Controller.php';
require APPPATH . '/libraries/Pdf2text.php';
use Restserver\Libraries\REST_Controller;
date_default_timezone_set('Asia/Jakarta');

class Latest_Upload extends REST_Controller
{
	public function __construct($config = 'rest')
	{
		parent::__construct($config);
		$this->load->database();
		$this->load->helper('form', 'url');
		header('Content-Type: application/json; charset=UTF-8');
	}

	public function index_get()
	{
		$this->validateAuth();

		$baseUrl = "https://www.kmsp-store.com/gosearch/documents/";

		$starttime = microtime(true);

		$latestQuery = "SELECT * FROM dokumen ORDER BY uploaded_at_timestamp DESC LIMIT 5";
		$doLatestQuery = $this->db->query($latestQuery);

		if($doLatestQuery)
		{
			$resultLatestQuery = $doLatestQuery->result_array();

			$arrayOutput = array();

				foreach($resultLatestQuery as $rowResultSearch)
				{
					$dokumen_id_nya = $rowResultSearch['dokumen_id'];
					$getBaseUrl = $baseUrl . $rowResultSearch['file_url'];

					$postInfoQuery = "SELECT * FROM posts WHERE BINARY dokumen_id=? ORDER BY id DESC LIMIT 1";
					$doPostInfoQuery = $this->db->query($postInfoQuery, array($dokumen_id_nya));
					$resultDoPostInfoQuery = $doPostInfoQuery->result_array();

					foreach($resultDoPostInfoQuery as $rowResultDoPostInfoQuery)
					{
						$limitPostDesc = $rowResultDoPostInfoQuery['post_desc'];
						$limitPostDesc = substr($limitPostDesc, 0, 200) . " ...";
					}

					$arrayOutput2 = [
									'dokumen_id'=>$dokumen_id_nya,
									'nama_file'=>$rowResultSearch['nama_file'],
									'file_format'=>$rowResultSearch['file_format'],
									'uploaded_by'=>$rowResultSearch['uploaded_by'],
									'uploaded_at_timestamp'=>(int)$rowResultSearch['uploaded_at_timestamp'],
									'post_title'=>htmlentities($rowResultDoPostInfoQuery['post_title'], ENT_QUOTES, 'UTF-8'), // ANTI XSS FILTERING
									'post_desc'=>htmlentities($limitPostDesc, ENT_QUOTES, 'UTF-8'), // ANTI XSS FILTERING,
									'file_download_url'=>$getBaseUrl
									];

					array_push($arrayOutput, $arrayOutput2);
				}

				$endtime = microtime(true);
				$duration = $endtime - $starttime;
				$duration = number_format((float)$duration, 2, '.', '');

				header('HTTP/1.1 200 OK');
				echo json_encode(array(
							'statusCode'=>200,
							'status'=>true,
							'success_data'=>[
										'success_message'=>'SUCCESS',
										'amount_result'=>sizeof($arrayOutput),
										//'query_load_time'=>$duration . " detik",
										'documents_data'=>$arrayOutput
										]
							));
		}

		else
		{
			header('HTTP/1.1 500 Internal Server Error');
			echo json_encode(array(
						'statusCode'=>500,
						'status'=>false,
						'error'=>'Internal Server Error',
						'error_code'=>'GoSearch-04'
						));
		}

		exit();
	}

	public function index_post()
	{
		header('HTTP/1.1 405 Method Not Allowed');
		echo json_encode(array(
					'statusCode'=>405,
					'status'=>false,
					'error'=>'Method Not Allowed',
					'error_code'=>'GoSearch-01'
					));
		exit();
	}

	public function index_put()
	{
		header('HTTP/1.1 405 Method Not Allowed');
		echo json_encode(array(
					'statusCode'=>405,
					'status'=>false,
					'error'=>'Method Not Allowed',
					'error_code'=>'GoSearch-01'
					));
		exit();
	}

	public function index_delete()
	{
		header('HTTP/1.1 405 Method Not Allowed');
		echo json_encode(array(
					'statusCode'=>405,
					'status'=>false,
					'error'=>'Method Not Allowed',
					'error_code'=>'GoSearch-01'
					));
		exit();
	}

	public function index_patch()
	{
		header('HTTP/1.1 405 Method Not Allowed');
		echo json_encode(array(
					'statusCode'=>405,
					'status'=>false,
					'error'=>'Method Not Allowed',
					'error_code'=>'GoSearch-01'
					));
		exit();
	}

	public function validateAuth()
	{
		$detectGoSearchAPIKeyHeader = $this->input->get_request_header('GOSEARCH-API-KEY');

		if(preg_match('/[A-Z]/', $detectGoSearchAPIKeyHeader))
		{
			header('HTTP/1.1 403 Forbidden');
			echo json_encode(array(
						'statusCode'=>403,
						'status'=>false,
						'error'=>'Invalid API key',
						'error_code'=>'GoSearch-02'
						));
			exit();
		}

		$detectAuth = $this->output->get_output();
		$decodeDetectAuth = json_decode($detectAuth, true);
		
		$authCode = $decodeDetectAuth['error'];

		if($authCode == "Invalid API key ")
		{
			header('HTTP/1.1 403 Forbidden');
			echo json_encode(array(
						'statusCode'=>403,
						'status'=>false,
						'error'=>'Invalid API key',
						'error_code'=>'GoSearch-02'
						));
			exit();
		}

		if($authCode == "Unauthorized")
		{
			header('HTTP/1.1 401 Unauthorized');
			echo json_encode(array(
						'statusCode'=>401,
						'status'=>false,
						'error'=>'Unauthorized',
						'error_code'=>'GoSearch-03'
						));
			exit();
		}
	}
}
?>