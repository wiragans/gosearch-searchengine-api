<?php
error_reporting(0);
defined('BASEPATH') OR exit('No direct script access allowed');
include 'vendor/autoload.php';
require APPPATH . '/libraries/REST_Controller.php';
require APPPATH . '/libraries/Pdf2text.php';
use Restserver\Libraries\REST_Controller;
date_default_timezone_set('Asia/Jakarta');
$input_title = "";
$get_most_specific_keyword = "";
$get_user_upload = "";

class File_Upload extends REST_Controller
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
		/*header('HTTP/1.1 405 Method Not Allowed');
		echo json_encode(array(
					'statusCode'=>405,
					'status'=>false,
					'error'=>'Unknown method',
					'error_code'=>'GoSearch-01'
					));
		exit();*/
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

		$input_title = $this->post('search_title');
		$get_user_upload = $this->post('user');
		//$get_most_specific_keyword = $this->post('most_specific_keyword');
		$this->load->library('form_validation');

		$rulesForm = array([
					'field'=>'search_title',
					'label'=>'Search_title',
					'rules'=>'required'
					],
					[
					'field'=>'user',
					'label'=>'User',
					'rules'=>'required'
					]
				);

		$this->form_validation->set_rules($rulesForm);

		if($this->form_validation->run())
		{
			//
		}

		else
		{
			header('HTTP/1.1 200 OK');
			echo json_encode(array(
						'statusCode'=>200,
						'status'=>false,
						'error'=>'All parameters are required to fill up',
						'error_code'=>'GoSearch-70'
						));
			exit();
		}

		$config['upload_path']          = './documents/';
		$config['allowed_types']        = 'pdf';
		$config['max_size']             = 10000;
		$config['max_width']            = 1024;
		$config['max_height']           = 768;

		$this->load->library('upload', $config);

		if (!$this->upload->do_upload('document'))
		{
			$error = array('statusCode'=>200, 'status'=>false, 'error' => strip_tags($this->upload->display_errors()));
			header('HTTP/1.1 200 OK');
			echo json_encode($error);
		}

		else
		{
			$successArray = $this->upload->data();

			$arrayFileInfo = array('file_name'=>$successArray['file_name'], 'file_type'=>$successArray['file_type'], 'raw_name'=>$successArray['raw_name'], 'orig_name'=>$successArray['orig_name'], 'client_name'=>$successArray['client_name'], 'file_ext'=>$successArray['file_ext'], 'file_size'=>floatval($successArray['file_size']));

			$getResultPdfText = $this->parseTextDariPdf('./documents/' . $successArray['file_name'], $arrayFileInfo, $input_title, $get_user_upload, $get_most_specific_keyword);

			//CASE FOLDING STEP

			//$this->caseFolding();

			//

			//$insertFileQuery = "INSERT INTO dokumen(nama_file, file_url, file_format, uploaded_by, token, tokenstem, uploaded_at, ) VALUES()";

			//header('HTTP/1.1 200 OK');
			//echo json_encode($data);
			//echo $getResultPdfText;

			$data = array('statusCode'=>200, 'status'=>true, 'success_data' => 
				[
					'success_message'=>'Berhasil upload file dokumen',
					'file_name' => $successArray['file_name'],
					'file_type'=>$successArray['file_type'],
					'raw_name'=>$successArray['raw_name'],
					'orig_name'=>$successArray['orig_name'],
					'client_name'=>$successArray['client_name'],
					//'client_name'=>$successArray['client_name'],
					'file_ext'=>$successArray['file_ext'],
					'file_size'=>floatval($successArray['file_size']),
					'document_data'=>$getResultPdfText			
				]);

			header('HTTP/1.1 200 OK');
			echo json_encode($data);
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

	public function parseTextDariPdf($docLocation, $getArrayFileInfo, $input_title, $get_user_upload)
	{
		$getDocumentLocation = $docLocation;

		//$reader = new \Asika\Pdf2text;
		//$output = $reader->decode($getDocumentLocation);
		//echo $output;

		$parser = new \Smalot\PdfParser\Parser();
		$pdf    = $parser->parseFile($getDocumentLocation);

		//$text = $pdf->getText();
		//echo $text;

		$details  = $pdf->getDetails();

		//var_dump($details);
		$title = $details['Title'];
		$author = $details['Author'];
		$creator = $details['Creator'];
		$CreationDate = $details['CreationDate'];
		$ModDate = $details['ModDate'];
		$Producer = $details['Producer'];
		$Pages = (int)$details['Pages'];

		$getPdfText = $pdf->getText();

		//CREATE DOKUMEN ID RANDOM BY CRC32
		$bytes_data = openssl_random_pseudo_bytes(32);
        $newHash = bin2hex($bytes_data);
        $timestampNow = time();
        $hashCRC32DokumenId = hash('crc32b', $newHash . $timestampNow . $getArrayFileInfo['file_name']);
        $user = $get_user_upload;
        //$keyword = $get_most_specific_keyword;

        //

		$baseTokenizingPerWord = str_replace(array("\t", "\r", "\n", "\r\n", "'", "-", ")", "(", "\"", "/", "=", ".", ",", ":", ";", "!", "?", ">", "<"), ' ', $getPdfText); // replace dengan spasi
		$lowerCaseFoldingText = $this->caseFolding($baseTokenizingPerWord);
		$tokenizingPerWordText = $this->tokenizing($lowerCaseFoldingText);
		$stemmingPerWordText = $this->stemming($tokenizingPerWordText, $hashCRC32DokumenId); // DECPDE ARRAY DAN LAKUKAN STEMMING PENCARIAN KATA DASARNYA, DAN MASUKKAN DI DATABASE TABLE TOKEN

		$arrayResultPdfText = array('title'=>$title, 'author'=>$author, 'creator'=>$creator, 'CreationDate'=>$CreationDate, 'ModDate'=>$ModDate, 'Producer'=>$Producer, 'Pages'=>$Pages, 'lowerCaseFoldingText'=>$lowerCaseFoldingText, 'tokenizing'=>$tokenizingPerWordText, 'stemming'=>$stemmingPerWordText, 'dokumen_id_hash_name'=>$hashCRC32DokumenId);

		foreach($arrayResultPdfText as $key => $value)
		{
		    if(is_null($value))
		    {
		         $arrayResultPdfText[$key] = "";
    		}
		}

		// INSERT TO DB DOKUMEN FILENYA

		$insertFileToDB = "INSERT INTO dokumen(dokumen_id, nama_file, file_url, file_format, uploaded_by, uploaded_at_timestamp) VALUES(?, ?, ?, ?, ?, ?)";
		$doInsertFileToDB = $this->db->query($insertFileToDB, array($hashCRC32DokumenId, $getArrayFileInfo['file_name'], $getArrayFileInfo['file_name'], $getArrayFileInfo['file_ext'], $user, $timestampNow));

		// INSERT DOKUMEN POST INFO
		$insertPostInfo = "INSERT INTO posts(dokumen_id, post_title, post_desc, author, creator, CreationDate, ModDate, Producer, Pages) VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?)";
		$doInsertPostInfo = $this->db->query($insertPostInfo, array($hashCRC32DokumenId, $input_title, $lowerCaseFoldingText, $arrayResultPdfText['author'], $arrayResultPdfText['creator'], $arrayResultPdfText['CreationDate'], $arrayResultPdfText['ModDate'], $arrayResultPdfText['Producer'], $arrayResultPdfText['Pages']));

		return $arrayResultPdfText;

		//echo $title . "<br>";
		//echo $author . "<br>";
		//echo $creator . "<br>";
		//echo $CreationDate . "<br>";
		//echo $ModDate . "<br>";
		//echo $Producer . "<br>";
		//echo $Pages . "<br>";

		//exit();



		/*foreach ($details as $property => $value)
		{
		    if(is_array($value))
		    {
		        $value = implode(', ', $value);
		    }

	    	echo $property . ' => ' . $value . "\n";
		}*/
	}

	public function caseFolding($caseFoldingData)
	{
		$loweringStringStandar = strtolower($caseFoldingData);
		return $loweringStringStandar;
	}

	public function tokenizing($tokenizingData)
	{
		$getTokenizingData = explode(" ", $tokenizingData); //proses awal tokenisasi, pisah dengan spasi

		// LAKUKAN STOPWORD REMOVAL JIKA ADA LIST DI BAWAH INI GUNA MEMPERCEPAT SEARCH QUERY NANTINYA
		$astoplist = array("a", "about", "above", "acara", "across", "ada", "adalah", "adanya", "after", "afterwards", "again", "against", "agar", "akan", "akhir", "akhirnya", "akibat", "aku", "all", "almost", "alone", "along", "already", "also", "although", "always", "am", "among", "amongst", "amoungst", "amount", "an", "and", "anda", "another", "antara", "any", "anyhow", "anyone", "anything", "anyway", "anywhere", "apa", "apakah", "apalagi", "are", "around", "as", "asal", "at", "atas", "atau", "awal", "b", "back", "badan", "bagaimana", "bagi", "bagian", "bahkan", "bahwa", "baik", "banyak", "barang", "barat", "baru", "bawah", "be", "beberapa", "became", "because", "become", "becomes", "becoming", "been", "before", "beforehand", "begitu", "behind", "being", "belakang", "below", "belum", "benar", "bentuk", "berada", "berarti", "berat", "berbagai", "berdasarkan", "berjalan", "berlangsung", "bersama", "bertemu", "besar", "beside", "besides", "between", "beyond", "biasa", "biasanya", "bila", "bill", "bisa", "both", "bottom", "bukan", "bulan", "but", "by", "call", "can", "cannot", "cant", "cara", "co", "con", "could", "couldnt", "cry", "cukup", "dalam", "dan", "dapat", "dari", "datang", "de", "dekat", "demikian", "dengan", "depan", "describe", "detail", "di", "dia", "diduga", "digunakan", "dilakukan", "diri", "dirinya", "ditemukan", "do", "done", "down", "dua", "due", "dulu", "during", "each", "eg", "eight", "either", "eleven", "else", "elsewhere", "empat", "empty", "enough", "etc", "even", "ever", "every", "everyone", "everything", "everywhere", "except", "few", "fifteen", "fify", "fill", "find", "fire", "first", "five", "for", "former", "formerly", "forty", "found", "four", "from", "front", "full", "further", "gedung", "get", "give", "go", "had", "hal", "hampir", "hanya", "hari", "harus", "has", "hasil", "hasnt", "have", "he", "hence", "her", "here", "hereafter", "hereby", "herein", "hereupon", "hers", "herself", "hidup", "him", "himself", "hingga", "his", "how", "however", "hubungan", "hundred", "ia", "ie", "if", "ikut", "in", "inc", "indeed", "ingin", "ini", "interest", "into", "is", "it", "its", "itself", "itu", "jadi", "jalan", "jangan", "jauh", "jelas", "jenis", "jika", "juga", "jumat", "jumlah", "juni", "justru", "juta", "kalau", "kali", "kami", "kamis", "karena", "kata", "katanya", "ke", "kebutuhan", "kecil", "kedua", "keep", "kegiatan", "kehidupan", "kejadian", "keluar", "kembali", "kemudian", "kemungkinan", "kepada", "keputusan", "kerja", "kesempatan", "keterangan", "ketiga", "ketika", "khusus", "kini", "kita", "kondisi", "kurang", "lagi", "lain", "lainnya", "lalu", "lama", "langsung", "lanjut", "last", "latter", "latterly", "least", "lebih", "less", "lewat", "lima", "ltd", "luar", "made", "maka", "mampu", "mana", "mantan", "many", "masa", "masalah", "masih", "masing-masing", "masuk", "mau", "maupun", "may", "me", "meanwhile", "melakukan", "melalui", "melihat", "memang", "membantu", "membawa", "memberi", "memberikan", "membuat", "memiliki", "meminta", "mempunyai", "mencapai", "mencari", "mendapat", "mendapatkan", "menerima", "mengaku", "mengalami", "mengambil", "mengatakan", "mengenai", "mengetahui", "menggunakan", "menghadapi", "meningkatkan", "menjadi", "menjalani", "menjelaskan", "menunjukkan", "menurut", "menyatakan", "menyebabkan", "menyebutkan", "merasa", "mereka", "merupakan", "meski", "might", "milik", "mill", "mine", "minggu", "misalnya", "more", "moreover", "most", "mostly", "move", "much", "mulai", "muncul", "mungkin", "must", "my", "myself", "nama", "name", "namely", "namun", "nanti", "neither", "never", "nevertheless", "next", "nine", "no", "nobody", "none", "noone", "nor", "not", "nothing", "now", "nowhere", "of", "off", "often", "oleh", "on", "once", "one", "only", "onto", "or", "orang", "other", "others", "otherwise", "our", "ours", "ourselves", "out", "over", "own", "pada", "padahal", "pagi", "paling", "panjang", "para", "part", "pasti", "pekan", "penggunaan", "penting", "per", "perhaps", "perlu", "pernah", "persen", "pertama", "pihak", "please", "posisi", "program", "proses", "pula", "pun", "punya", "put", "rabu", "rasa", "rather", "re", "ribu", "ruang", "saat", "sabtu", "saja", "salah", "sama", "same", "sampai", "sangat", "satu", "saya", "sebab", "sebagai", "sebagian", "sebanyak", "sebelum", "sebelumnya", "sebenarnya", "sebesar", "sebuah", "secara", "sedang", "sedangkan", "sedikit", "see", "seem", "seemed", "seeming", "seems", "segera", "sehingga", "sejak", "sejumlah", "sekali", "sekarang", "sekitar", "selain", "selalu", "selama", "selasa", "selatan", "seluruh", "semakin", "sementara", "sempat", "semua", "sendiri", "senin", "seorang", "seperti", "sering", "serious", "serta", "sesuai", "setelah", "setiap", "several", "she", "should", "show", "side", "since", "sincere", "six", "sixty", "so", "some", "somehow", "someone", "something", "sometime", "sometimes", "somewhere", "still", "suatu", "such", "sudah", "sumber", "system", "tahu", "tahun", "tak", "take", "tampil", "tanggal", "tanpa", "tapi", "telah", "teman", "tempat", "ten", "tengah", "tentang", "tentu", "terakhir", "terhadap", "terjadi", "terkait", "terlalu", "terlihat", "termasuk", "ternyata", "tersebut", "terus", "terutama", "tetapi", "than", "that", "the", "their", "them", "themselves", "then", "thence", "there", "thereafter", "thereby", "therefore", "therein", "thereupon", "these", "they", "thickv", "thin", "third", "this", "those", "though", "three", "through", "throughout", "thru", "thus", "tidak", "tiga", "tinggal", "tinggi", "tingkat", "to", "together", "too", "top", "toward", "towards", "twelve", "twenty", "two", "ujar", "umum", "un", "under", "until", "untuk", "up", "upaya", "upon", "us", "usai", "utama", "utara", "very", "via", "waktu", "was", "we", "well", "were", "what", "whatever", "when", "whence", "whenever", "where", "whereafter", "whereas", "whereby", "wherein", "whereupon", "wherever", "whether", "which", "while", "whither", "who", "whoever", "whole", "whom", "whose", "why", "wib", "will", "with", "within", "without", "would", "ya", "yaitu", "yakni", "yang", "yet", "you", "your", "yours", "yourself", "yourselves");

		$len = count($getTokenizingData);

		//REMOVE STOPWORD DAN BUANG HASIL TOKENIZING FIELD YANG KOSONG

		$arrayTokeninizingDataFix = array();
		for($looping = 0; $looping < $len; $looping++)
		{
			if(!in_array($getTokenizingData[$looping], $astoplist) && preg_match('/\S/', $getTokenizingData[$looping]))
			{
				array_push($arrayTokeninizingDataFix, $getTokenizingData[$looping]);
			}
		}

		//

		$arrayTokeninizing = array('tokenizingCount'=>(int)$len, 'tokenizingResult'=>$arrayTokeninizingDataFix);

		return $arrayTokeninizing;
	}

	public function stemming($stemmingData, $getDokumenIdHashName)
	{
		$stemmerFactory = new \Sastrawi\Stemmer\StemmerFactory();
		$stemmer  = $stemmerFactory->createStemmer();

		$getStemmingData = $stemmingData; // INI BERUPA ARRAY DARI DATA YANG SUDAH DILAKUKAN PROSES CASE FOLDING DAN TOKENISASI SEBELUMNYA, INI AKAN DICOCOKKAN DENGAN RESULT KATA DASAR DARI DATABASE, FUNGSINYA UNTUK DILAKUKAN INPUT TOKEN DAN TOKENSTEM KE DATABASE...

		$getCurrentDokumenId = $getDokumenIdHashName;

		//PERTAMA, AMBIL DULU KATA DASAR DARI DATABASE BIAR GAK BERAT QUERY DATABASENYA CUY...
		$this->db->select('*')->from('tb_katadasar');
		$resultSQLKataDasar = $this->db->get()->result();

		$arrayStemmingFix = array();

		foreach($resultSQLKataDasar as $rowKataDasar)
		{
			if(in_array(strtolower($rowKataDasar->katadasar), $getStemmingData['tokenizingResult']))
			{
				$arrayStemmingFix2 = [
									'token'=>$rowKataDasar->katadasar,
									'tokenstem'=>$rowKataDasar->katadasar
									];

				array_push($arrayStemmingFix, $arrayStemmingFix2);

				$updateToken = "INSERT INTO token_data(dokumen_id, token, tokenstem) VALUES(?, ?, ?)";
				$doUpdateToken = $this->db->query($updateToken, array($getCurrentDokumenId, $rowKataDasar->katadasar, $rowKataDasar->katadasar));
			}
		}

		for($cobaUlangi = 0; $cobaUlangi < sizeof($getStemmingData['tokenizingResult']); $cobaUlangi++)
		{
			$cleanWord = preg_replace("/[^A-Za-z0-9-]/", ' ', $getStemmingData['tokenizingResult'][$cobaUlangi]);

			$outputStem = $stemmer->stem($cleanWord);

			$arrayStemmingFix2 = [
									'token'=>$cleanWord,
									'tokenstem'=>$outputStem
									];

			array_push($arrayStemmingFix, $arrayStemmingFix2);

			$updateToken = "INSERT INTO token_data(dokumen_id, token, tokenstem) VALUES(?, ?, ?)";
			$doUpdateToken = $this->db->query($updateToken, array($getCurrentDokumenId, $cleanWord, $outputStem));
		}

		return $arrayStemmingFix;
	}
}
?>