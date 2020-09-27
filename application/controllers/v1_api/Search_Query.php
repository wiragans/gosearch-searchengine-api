<?php
error_reporting(0);
defined('BASEPATH') OR exit('No direct script access allowed');
include 'vendor/autoload.php';
require APPPATH . '/libraries/REST_Controller.php';
require APPPATH . '/libraries/Pdf2text.php';
use Restserver\Libraries\REST_Controller;
date_default_timezone_set('Asia/Jakarta');

class Search_Query extends REST_Controller
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
		header('HTTP/1.1 405 Method Not Allowed');
		echo json_encode(array(
					'statusCode'=>405,
					'status'=>false,
					'error'=>'Method Not Allowed',
					'error_code'=>'GoSearch-01'
					));
		exit();
	}

	public function index_post()
	{
		$this->validateAuth();

		// DO QUERY SEARCH WITH BOOLEAN MODE
		$search_query = $this->post('search_query');
		$this->load->library('form_validation');
		$rulesForm = array([
					'field'=>'search_query',
					'label'=>'search_query',
					'rules'=>'required'
					]);

		$this->form_validation->set_rules($rulesForm);

		if($this->form_validation->run())
		{
			$baseUrl = "https://www.kmsp-store.com/gosearch/documents/";

			$starttime = microtime(true);

			$searchQuery = "SELECT DISTINCT token_data.dokumen_id, token_data.token, token_data.tokenstem, dokumen.nama_file, dokumen.file_format, dokumen.uploaded_by, dokumen.file_url, dokumen.uploaded_at_timestamp, posts.post_title, posts.post_desc, posts.author, posts.creator, posts.CreationDate, posts.ModDate, posts.Producer, posts.Pages FROM token_data INNER JOIN dokumen ON BINARY dokumen.dokumen_id = token_data.dokumen_id INNER JOIN posts ON BINARY posts.dokumen_id = token_data.dokumen_id WHERE MATCH (token_data.token, token_data.tokenstem) AGAINST (? IN BOOLEAN MODE) ORDER BY dokumen.uploaded_at_timestamp DESC";
			//$searchQuery = "SELECT DISTINCT token_data.dokumen_id, token_data.token, token_data.tokenstem FROM token_data WHERE MATCH (token_data.token, token_data.tokenstem) AGAINST (? IN BOOLEAN MODE)";
			$doSearchQuery = $this->db->query($searchQuery, array($search_query));

			//$searchQuery = "SELECT DISTINCT token_data.dokumen_id, token_data.token, token_data.tokenstem FROM token_data WHERE MATCH (token_data.token, token_data.tokenstem) AGAINST (? IN BOOLEAN MODE)";
			//$doSearchQuery = $this->db->query($searchQuery, array($search_query));

			if($doSearchQuery)
			{
				$resultSearchQuery = $doSearchQuery->result_array();

				//var_dump($resultSearchQuery);

				//exit();

				$arrayOutput = array();

				foreach($resultSearchQuery as $rowResultSearch)
				{
					$postDescFind = stripos($rowResultSearch['post_desc'], rawurldecode($search_query));

					$limitPostDesc = $rowResultSearch['post_desc'];
					$limitPostDesc = substr($limitPostDesc, $postDescFind, 200) . " ...";
					$dokumen_id_nya = $rowResultSearch['dokumen_id'];
					$getBaseUrl = $baseUrl . $rowResultSearch['file_url'];

					$arrayOutput2 = [
									'dokumen_id'=>$dokumen_id_nya,
									'nama_file'=>$rowResultSearch['nama_file'],
									'file_format'=>$rowResultSearch['file_format'],
									'uploaded_by'=>$rowResultSearch['uploaded_by'],
									'uploaded_at_timestamp'=>(int)$rowResultSearch['uploaded_at_timestamp'],
									'post_title'=>htmlentities($rowResultSearch['post_title'], ENT_QUOTES, 'UTF-8'), // ANTI XSS FILTERING
									'post_desc'=>htmlentities($limitPostDesc, ENT_QUOTES, 'UTF-8'), // ANTI XSS FILTERING,
									'file_download_url'=>$getBaseUrl
									];

					array_push($arrayOutput, $arrayOutput2);
				}

				$tampungFixArray = array();

				for($arrayUlang = 0; $arrayUlang < sizeof($arrayOutput); $arrayUlang++)
				{
					if(!in_array($arrayOutput[$arrayUlang], $tampungFixArray))
					{
						$tampungFixArray2 = [
											'dokumen_id'=>$arrayOutput[$arrayUlang]['dokumen_id'],
											'nama_file'=>$arrayOutput[$arrayUlang]['nama_file'],
											'file_format'=>$arrayOutput[$arrayUlang]['file_format'],
											'uploaded_by'=>$arrayOutput[$arrayUlang]['uploaded_by'],
											'uploaded_at_timestamp'=>(int)$arrayOutput[$arrayUlang]['uploaded_at_timestamp'],
											'post_title'=>htmlentities($arrayOutput[$arrayUlang]['post_title'], ENT_QUOTES, 'UTF-8'), // ANTI XSS FILTERING
											'post_desc'=>htmlentities($arrayOutput[$arrayUlang]['post_desc'], ENT_QUOTES, 'UTF-8'), // ANTI XSS FILTERING
											'file_download_url'=>$arrayOutput[$arrayUlang]['file_download_url'], // ANTI XSS FILTERING
											];

						array_push($tampungFixArray, $tampungFixArray2);
					}
				}

				//$result = array_unique($arrayOutput, SORT_REGULAR);

				//$result = array_map("unserialize", array_unique(array_map("serialize", $arrayOutput)));

				//$result = $this->array_unique_multidimensional($tampungFixArray);

				$endtime = microtime(true);
				$duration = $endtime - $starttime;
				$duration = number_format((float)$duration, 2, '.', '');

				header('HTTP/1.1 200 OK');
				echo json_encode(array(
							'statusCode'=>200,
							'status'=>true,
							'success_data'=>[
										'success_message'=>'SUCCESS',
										'amount_result'=>sizeof($tampungFixArray),
										'query_load_time'=>$duration . " detik",
										'documents_data'=>$tampungFixArray,
										'highlighted_outputs'=>htmlentities(rawurldecode($search_query), ENT_QUOTES, 'UTF-8')
										]
							));

				exit();
			}

			else
			{
				header('HTTP/1.1 200 OK');
				echo json_encode(array(
							'statusCode'=>200,
							'status'=>false,
							'error'=>'The service is temporarily down. Please try again later',
							'error_code'=>'GoSearch-80'
							));
			}

			exit();
		}

		else
		{
			header('HTTP/1.1 200 OK');
			echo json_encode(array(
						'statusCode'=>200,
						'status'=>false,
						'error'=>'Search query parameter is required',
						'error_code'=>'GoSearch-90'
						));
			exit();
		}

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

	public function array_unique_multidimensional($input)
	{
	    $serialized = array_map('serialize', $input);
	    $unique = array_unique($serialized);
	    return array_intersect_key($input, $unique);
	}
}
?>