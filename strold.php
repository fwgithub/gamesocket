<?php
//include("read.php");
// prevent the server from timing out
//set_time_limit(5);
// $bin = pack("a3", "中");
// echo "output: " . $bin . "\n";
// echo "output: 0x" . bin2hex($bin) . "\n";		16进制
// echo "output: " . chr(0xe4) . chr(0xb8) . chr(0xad) . "\n";
// echo "output: " . $bin{0} . $bin{1} . $bin{2} . "\n";


$ps = new PS();
$ps->connect();
// if ($err = socket_last_error($this->socket))
// {

  // socket_close($this->socket);socket_recv($this->socket,$buffer,2048,0);
  // die(socket_strerror($err) . "\n");
// }


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
	public $sockett;
	public $socket = array();

	public $host = "192.168.1.163";
	public $port = "41000";
	
	
    function __construct(){
		$this->socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP)
		  or die("Unable to create socket\n");
		//$this->socket[] = $this->socket;	
		$result = socket_connect($this->socket, $this->host, $this->port);

		
		if($result === false) {
			echo "socket_connect() failed.\nReason: ($result) " . socket_strerror(socket_last_error($this->socket)) . "\n";
		} else {
			echo "Connect OK \n";
		}
		// do{
			// echo 'test'.time().'<br/>';
			
			// $this->welive();
			// set_time_limit(20);
			//sleep(5);
		// }while(true);
		
		
	}
	function welive() {
		echo "welive";
		//sleep(5);
		
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

	function read($binarydata) {
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
			//$socketid = unpack("l",$mcontent);   3
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
	function connect(){
		//首次连接
		$binarydata = $this->packhead(self::MSG_CONNECT,1509171435);
		$len = socket_write($this->socket , $binarydata, strlen($binarydata));
		$bytes = socket_read($this->socket,4096);
		$Head = $this->unpackhead($bytes);
		$mcontent = substr($bytes,28,4); 
		print_r($Head);

		if($this->status($Head['Message']) == 2) {
			$this->connected($mcontent);
		} else {
			return false;
		}
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
				$bytess = socket_read($this->socket,4096);
				//$bytess = send($binarydata);
				$Headd = $this->unpackhead($bytess);
				print_r($Headd);
				if($this->status($Headd['Message']) == 4) {
					//print_r($mcontentt);
					$this->authed();
				} else {
					return false;
				}
			}
		
	}
	
	function authed(){	
				$contentt = pack("l", 1111);
				$contenttt = pack("c", 111);
				$content = $contentt.$contenttt;
				$binarydata = $this->packhead(233,0,$content);
				socket_write($this->socket , $binarydata, strlen($binarydata));
				print_r(5);
				
				// $bytess = socket_read($this->socket,4096);
				// $Headd = $this->unpackhead($bytess);
				// if($this->status($Headd['Message']) == 5) {
					// print_r(5);
					// $this->info($Headd,$mcontentt);
				// } else {
					// return false;
				// }
			
		
	}
	function info($mcontent){
		$content='';
		$socketid = unpack("l",$mcontent);
			if($socketid){
				$contentt = pack("l", $socketid[1]);
				$contenttt = pack("c", 1);
				$content = $contentt.$content;
				$binarydata = $this->packhead(self::MSG_INFO_RET,0,$content);
				socket_write($this->socket , $binarydata, strlen($binarydata));
				$bytess = socket_read($this->socket,4096);
				$Headd = $this->unpackhead($bytess);
				$mcontentt= substr($bytess,28,4);
				if($this->status($Headd['Message']) == 5) {
					$this->inforet($Headd,$mcontentt);
				} else {
					return false;
				}
			}
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
				$bytess = socket_read($this->socket,4096);
				$Headd = $this->unpackhead($bytess);
				$mcontentt= substr($bytess,28,4);
				if($this->status($Headd['Message']) == 6) {
					$this->user($Headd,$mcontentt);
				} else {
					return false;
				}
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
				$bytess = socket_read($this->socket,4096);
				$Headd = $this->unpackhead($bytess);
				$mcontentt= substr($bytess,28,4);
				if($this->status($Headd['Message']) == 7) {
					return $mcontentt ;
				} else {
					return false;
				}
			}
	}


}
?>


