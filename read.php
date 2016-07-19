<?php 
	include("str.php");

	//header('Content-Type:application/x-www-form-urlencoded; charset=GBK');
	
	if(isset($_POST)) {
		//echo $_POST['act'];
		print_r($GLOBALS["test"]);
	}

	$ps->read();
	
	
	// class RD extends PS{
		// function __construct() {
			//parent::read();
			//parent::__construct();

		// }
		
	// }
	
	// $read = new RD();
	// $read->init();
?>