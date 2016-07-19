<?php
//include("read.php");
// prevent the server from timing out
//set_time_limit(5);
// $bin = pack("a3", "中");
// echo "output: " . $bin . "\n";
// echo "output: 0x" . bin2hex($bin) . "\n";		16进制
// echo "output: " . chr(0xe4) . chr(0xb8) . chr(0xad) . "\n";
// echo "output: " . $bin{0} . $bin{1} . $bin{2} . "\n";
// header('Content-Type:application/x-www-form-urlencoded; charset=GBK');
// if(isset($_POST)) {
	//echo @$_POST['act'];
	// $ps->read();
// }

//global $ps ;


$ps = new PS();

$ps->connect();
if(isset($_POST)) {
	$ps->read();
}
	
	
class PS {
	const MSG_INVALID = 0;
	const MSG_CONNECT = 1;
	const MSG_CONNECTED = 2;
	const MSG_AUTH = 3;
	const MSG_AUTHED = 4;
	const MSG_INFO = 5;
	const MSG_INFO_RET = 6;
	const MSG_USER = 7;
	const HEAD_SIZE = 28;
	//public $resid;
	//public $sockett;
	public $socket = array();
	static $resid = array();
	public $host = "192.168.1.205";
	public $port = "41000";

	function __construct(){
		
		// do{
			//echo 'test'.time().'<br/>';
			// $res = $this->read();
			// set_time_limit(20);
			//sleep(5);

		// }while(true);

		//echo "str";
	}

	//组包头
	function packhead($message,$id,$content = "") {
		//$content = "";
		$Stx = pack("c", 0x27);
		$LineServerId = pack("c", 1);
		$DestServerType = pack("c", 2);
		$Ckx = pack("c", 0x72);
		$Message = pack("V", $message);
		$SrcZoneId = pack("V", 3);
		$DestZoneId = pack("V", 0);
		$Id = pack("l", $id);

		$packetsize = strlen($content);
		$RSV = pack("l",$packetsize);
		$PacketSizee = pack("V", $packetsize);
		$binarydata = $Stx.$LineServerId.$DestServerType.$Ckx.$Message.$SrcZoneId.$DestZoneId.$Id.$RSV.$PacketSizee.$content;
		return $binarydata;
	}
	//解包头
	function unpackhead($bytes) {
		$Head = unpack("c1Stx/c1LineServerId/c1DestServerType/c1Ckx/V1Message/V1SrcZoneId/V1DestZoneId/l1Id/l1RSV/V1PacketSizee", $bytes);
		return $Head;
	}
	//解包体
	function unpackcontent($bytes) {
		$mcontent= substr($bytes,28,4);
		$socketid = unpack("l",$mcontent);
		return $socketid;
	}

	function send($binarydata) {
		$len = socket_write ($this->socket , $binarydata, strlen($binarydata));
		$bytes = socket_read($this->socket,4096);
		return $bytes;
	}
	//获取服务器返回状态
	function status($mesg){
		switch($mesg){
			case self::MSG_CONNECTED:
				return 2;
				break;
			case self::MSG_AUTH:
				return 3;
				break;
			case self::MSG_AUTHED:
				return 4;
				break;
			case self::MSG_INFO:
				return 5;
				break;
			case self::MSG_INFO_RET:
				return 6;
				break;
			case self::MSG_USER:
				return 7;
				break;
			default:
				return false;
		}

	}
	public function read()
	{
		echo 'read.';
		print_r($GLOBALS["test"]);
		 $bytes = socket_read($this->socket,4096);
		 $Head = self::unpackhead($bytes);
		 $mcontent = substr($bytes,28,4); 
		 //$this->readnext($bytes);
		 
		print_r($Head);
	}
	//判断是否下一步
	function readnext($bytes) {
		$Head = $this->unpackhead($bytes);
		$mcontent = substr($bytes,28,4);
		print_r(8888);
		
		switch($Head['Message']){
			case 2:		//self::MSG_CONNECTED
				$this->connected($mcontent);
				break;
			case 4:			//self::MSG_AUTH
				$this->authed();
				break;
			case 5:			//self::MSG_INFO
				$this->info();
				break;
			case 7:			//self::MSG_USER
				$this->user();
				break;
			default:
				return false;
		}
	}
	function connect(){
		//首次连接
		$this->socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP)
		or die("Unable to create socket\n");
		// $res = socket_set_nonblock($this->socket);
		$GLOBALS["test"] = $this->socket;
		//print_r($GLOBALS["test"]);
		$result = socket_connect($this->socket, $this->host, $this->port);


		if($result === false) {
			echo "socket_connect() failed.\nReason: ($result) " . socket_strerror(socket_last_error($this->socket)) . "\n";
		} else {
			echo "Connect OK \n".$this->socket;
		}
		$binarydata = $this->packhead(self::MSG_CONNECT,1509171435);
		$len = socket_write($this->socket , $binarydata, strlen($binarydata));
		echo 'write.';
		print_r($this);
		
		//$this->read();
		 
	}

	
	function connected($mcontent){
		$content='';
		$socketid = unpack("l",$mcontent);
		if($socketid){
			$contentt = pack("l", $socketid[1]);
			$contenttt = pack("c", 1);
			$content = $contentt.$content;
			$binarydata = $this->packhead(self::MSG_AUTH,0,$content);
			socket_write($this->socket , $binarydata, strlen($binarydata));
		}

	}

	function authed(){
		$contentt = pack("l", 1111);
		$contenttt = pack("c", 111);
		$content = $contentt.$contenttt;
		$binarydata = $this->packhead(233,0,$content);
		socket_write($this->socket , $binarydata, strlen($binarydata));
		print_r(5);


	}
	function info($mcontent){
		$randnum = $this->randpw(8,'NUMBER');
		$key = 'DIGUO';
		$time = time();
		$contentmd = md5($randnum.$key.$time);
		$contentt = pack("l", $randnum);
		$contenttt = pack("c", $time);
		$content = $contentt.$contenttt.$contentmd;

		$binarydata = $this->packhead(233,0,$content);
		socket_write($this->socket , $binarydata, strlen($binarydata));

	}
	function inforet($mcontent){
		$content='';
		$socketid = unpack("l",$mcontent);
		if($socketid){
			$contentt = pack("l", $socketid[1]);
			$contenttt = pack("c", 1);
			$content = $contentt.$content;
			$binarydata = $this->packhead(self::MSG_INFO_RET,0,$content);
			socket_write($this->socket , $binarydata, strlen($binarydata));

		}
	}
	function user($mcontent){
		$content='';
		$socketid = unpack("l",$mcontent);
		if($socketid){
			$contentt = pack("l", $socketid[1]);
			$contenttt = pack("c", 1);
			$content = $contentt.$content;
			$binarydata = $this->packhead(self::MSG_USER,0,$content);
			socket_write($this->socket , $binarydata, strlen($binarydata));

		}
	}
	function randpw($len=8,$format='ALL'){
		$is_abc = $is_numer = 0;
		$password = $tmp ='';
		switch($format){
			case 'ALL':
				$chars='ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
				break;
			case 'CHAR':
				$chars='ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
				break;
			case 'NUMBER':
				$chars='0123456789';
				break;
			default :
				$chars='ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
				break;
		}

		mt_srand((double)microtime()*1000000*getmypid());
		while(strlen($password)<$len){
			$tmp =substr($chars,(mt_rand()%strlen($chars)),1);
			if(($is_numer <> 1 && is_numeric($tmp) && $tmp > 0 )|| $format == 'CHAR'){
				$is_numer = 1;
			}
			if(($is_abc <> 1 && preg_match('/[a-zA-Z]/',$tmp)) || $format == 'NUMBER'){
				$is_abc = 1;
			}
			$password.= $tmp;
		}

		if($is_numer <> 1 || $is_abc <> 1 || empty($password) ){
			$password = randpw($len,$format);
		}

		return $password;
	}

}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="Content-Type" content="application/x-www-form-urlencoded; charset=GBK">
	<title>phpsocket</title>
	<script type="text/javascript" src="/jquery.min.js"></script>
	<script type="text/javascript" src="/Ps.js"></script>
	<link rel="shortcut icon" href="favicon.ico" />
</head>
<body>this is str html
<script type="text/javascript">waiting();</script>
</body>
</html>
