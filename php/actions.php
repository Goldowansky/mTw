<?php
	include_once('functions.php');

	$action = $_GET["act"];
	switch($action){
		case 'sign_in':
			$obj = json_decode($_POST["obj"], true);
			echo sign_in($obj["login"],$obj["hash"]);
			break;
		case 'sign_out':
			echo sign_out();
			break;
		case 'sign_up':
			$obj = json_decode($_POST["obj"], true);
			echo sign_up($obj["login"],$obj["hash"]);
			break;
		case 'tweet_post':
			if (auth()){
				echo post_tweet($_SESSION["user_id"],$_POST["tweet"]);
			}
			else{
				echo '';
			}
			break;
		case 'tweet_edit':
			if (auth()){
				$tweet = json_decode($_POST["tweet"], true);
				echo edit_tweet($tweet["id"],$_SESSION["user_id"],$tweet["content"]);
			}
			else{
				echo '';
			}
			break;
	}
?>