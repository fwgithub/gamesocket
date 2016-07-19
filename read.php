<?php 
	include("str.php");

	//header('Content-Type:application/x-www-form-urlencoded; charset=GBK');
	//echo 'testttt';
	//print_r($_POST);
	if(isset($_POST)) {
		echo $_POST['act'];
	}

	PS::read();
	
	
	class RD extends PS{
		function __construct() {
			
			parent::__construct();

		}
		
		// public function init(){
			// echo "inited";
		// }	
	}
	
	// $read = new RD();
	// $read->init();
?>