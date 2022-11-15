<?php
	function db_connect(){
		$link = mysqli_connect("localhost", "mtw", "mtwpass");
		if(!mysqli_select_db($link, "mTw")) exit("DB connection error");
		mysqli_set_charset($link, "UTF8");
		return $link;
	}
	function auth(){
		session_start();
		return isset($_SESSION["user_id"]);
	}
	function check_auth(){
		if (!auth()){
			header('Location: http://194.44.45.233/mTw/auth.php');
			die();
		}
	}
	function sign_in($login_row, $hash){
		$link = db_connect();
		$login = mysqli_real_escape_string($link, $login_row);
		$query = "SELECT id, login FROM users WHERE login='$login' AND pass_hash=unhex('$hash');";
		$result = mysqli_query($link, $query);
		if (mysqli_num_rows($result) == 1){
			session_start();
			$row = mysqli_fetch_row($result);
			$_SESSION["user_id"] = $row[0];
			$_SESSION["user_name"] = $row[1];
			mysqli_close($link);
			return true;
		}
		else{
			mysqli_close($link);
			return false;
		}
	}
	function sign_up($login_row, $hash){
		$link = db_connect();
		$login = mysqli_real_escape_string($link, $login_row);
		$query = "INSERT INTO users (login, pass_hash) VALUES ('$login', unhex('$hash'));";
		$result = mysqli_query($link, $query);
		if ($result){
			session_start();
			$_SESSION["user_id"] = mysqli_insert_id($link);
			$_SESSION["user_name"] = $login_row;
		}
		mysqli_close($link);
		return $result;
	}
	function sign_out(){
		session_start();
		session_destroy();
		return true;
	}
	function post_tweet($creator_id, $content_unsafe){
		$link = db_connect();
		$content = mysqli_real_escape_string($link, $content_unsafe);
		$query = "INSERT INTO tweets (creator_id, content) VALUES ($creator_id, '$content');";
		mysqli_query($link, $query);
		$tweet_id = mysqli_insert_id($link);
		mysqli_close($link);
		return $tweet_id;
	}
	function edit_tweet($id, $editor_id, $content_unsafe){
		$link = db_connect();
		$content = mysqli_real_escape_string($link, $content_unsafe);
		$query = "UPDATE tweets SET content='$content' WHERE id=$id AND creator_id=$editor_id;";
		mysqli_query($link, $query);
		$res = mysqli_affected_rows($link);
		mysqli_close($link);
		return $res;
	}
	function html_my_feed($creator_id, $user_name){
		$link = db_connect();
		$html = '';
		$query = "SELECT `id`, `content`, `time` FROM tweets WHERE creator_id=$creator_id ORDER BY `id` DESC;";
		$result = mysqli_query($link, $query);
		while ($row = mysqli_fetch_row($result)){ ?>
			<div data-id="<?php echo $row[0]; ?>">
				<div class="post_content">
					<div class="small_ava"></div><!--
				--><div class="post_data">
						<div class="post_author"><?php echo $user_name; ?></div>
						<div class="post_date"><?php echo $row[2]; ?></div>
						<div class="post_text"><?php echo $row[1]; ?></div>
					</div>
				</div>
				<div class="post_buttons">
					<button class="edit_btn" onclick="editTweet();">Edit</button>
					<button class="sc_btns" onclick="saveEditing();">Save</button>
					<button class="sc_btns" onclick="cancelEditing();">Cancel</button>
				</div>
			</div>
			 
		<?php
		}
	}
	function html_all_feed($user_id){
		$link = db_connect();
		$query = "SELECT tweets.`id`, `content`, `time`, users.`login` FROM tweets, users WHERE creator_id=users.`id` ORDER BY tweets.`id` DESC;";
		$result = mysqli_query($link, $query);
		while ($row = mysqli_fetch_row($result)){ ?>
			<div class="a_tweet" data-id="<?php echo $row[0]; ?>">
				<div class="post_content">
					<div class="small_ava"></div><!--
				--><div class="post_data">
						<div class="post_author"><?php echo $row[3]; ?></div>
						<div class="post_date"><?php echo $row[2]; ?></div>
						<div class="post_text"><?php echo $row[1]; ?></div>
					</div>
				</div>
				<div class="post_buttons">
					<button>Like</button>
				</div>
			</div>
			 
		<?php
		}
	}
?>