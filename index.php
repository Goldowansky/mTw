<?php
	include_once('php/functions.php');
	
	check_auth();
	
//	$link = db_connect();
	
//	$query = "SELECT * FROM users;";
//	$result = mysqli_query($link, $query);
//	while ($row = mysqli_fetch_row($result)){
//		echo $row[0] . " - " . $row[1] . "<br>";
//	}
	
//	mysqli_close($link);
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>mTw. Feed</title>
</head>
<style>
	html{
		height: 100%;
	}
	html *{
		margin: 0;
		padding: 0;
		border: none;
		font-family: sans-serif;
	}
	body{
		border: none;
		height: 100%;
		min-width: 50rem;
		background: #EFEFEF;
	}
	header{
		width: 100%;
		height: 4rem;
		display: flex;
		flex-direction: row;
		justify-content: space-between;
		align-items: center;
		background: #FFF;
	}
	.logo{
		margin-left: 4rem;
		font-size: 1.5rem;
		font-style: italic;
	}
	#signout{
		margin-right: 2rem;
		cursor: pointer;
	}
	#bgpic{
		height: 15rem;
		background: hotpink;
		text-align: right;
		font-size: 3rem;
		padding-top: 10rem;
		padding-right: 3rem;
		box-sizing: border-box;
		color: #FFF;
		font-style: italic;
		font-weight: bold;
	}
	main{
		position: relative;
	}
	nav{
		display: flex;
		flex-direction: row;
		justify-content: center;
		align-items: baseline;
		background: #FFF;
	}
	nav>div{
		padding: 1rem;
		cursor: pointer;
	}
	main[data-show="my"] #nav_my{
		border-bottom: 0.2rem solid deeppink;
	}
	main[data-show="all"] #nav_all{
		border-bottom: 0.2rem solid deeppink;
	}
	
	.ava{
		position: absolute;
		width: 7rem;
		height: 7rem;
		display: inline-block;
		border-radius: 50%;
		border: 0.3rem solid #FFF;
		background: beige;
		left: 3rem;
		top: -3rem;
	}
	.feed{
		display: none;
	}
	main[data-show="my"] #myfeed{
		display: block;
	}
	main[data-show="all"] #allfeed{
		display: block;
	}
	.feed>div{
		width: 30rem;
		padding: 0.5rem;
		margin: 1rem auto;
		background: #FFF;
	}
	.feed>div>div>*{
		margin: 0.5rem;
	}
	.post_content>*{
		display: inline-block;
		vertical-align: top;
	}
	.post_buttons{
		display: flex;
		flex-direction: row-reverse;
		justify-content: space-between;
		align-items: center;
	}
	.post_buttons button{
		background: none;
		font-style: italic;
		font-weight: bold;
		color:deeppink;
		font-size: 1rem;
		cursor: pointer;
	}
	.post_buttons .sc_btns{
		display: none;
	}
	div[data-editing="true"] .sc_btns{
		display: inline-block;
	}
	div[data-editing="true"] .edit_btn{
		display: none;
	}

	.small_ava{
		width: 3rem;
		height: 3rem;
		background: beige;
		border-radius: 50%;
	}
	#myfeed textarea{
		resize: none;
		width: 25rem;
		padding: 1rem;
		box-sizing: border-box;
		background: #F2F2F2;
		font-family: sans-serif;
		font-size: 1rem;
		color: #444;
		outline: none;
	}
	.post_author{
		font-weight: bold;
	}
	.post_date{
		font-size: 0.75rem;
		color: #777;
		margin-bottom: 1rem;
	}
	div[data-editing="true"] .post_text{
		display: none;
	}
</style>
<body>
	<header>
		<div class="logo">mTw</div>
		<div id="signout" onclick="signOut();">Sign out</div>
	</header>
	<div id="bgpic"><?php echo $_SESSION["user_name"]; ?></div>
	<main data-show="my">
		<nav>
			<div id="nav_my" onclick="toogleToMy();">My Tweets</div>
			<div id="nav_all" onclick="toogleToAll();">Feed</div>
		</nav>
		<div class="ava"></div>
		<div id="myfeed" class="feed">
			<div id="post_div">
				<div class="post_content">
					<div class="small_ava"></div><textarea id="new_tweet" rows="5" placeholder="Text your tweet"></textarea>
				</div>
				<div class="post_buttons">
					<button onclick="postTweet();">Post It</button>
				</div>
			</div>
			<?php html_my_feed($_SESSION["user_id"], $_SESSION["user_name"]); ?>
		</div>
		<div id="allfeed" class="feed">
			<?php html_all_feed($_SESSION["user_id"]); ?>
		</div>
	</main>
</body>
<script>
	function postTweet(){
		let tweetArea = document.getElementById("new_tweet"), tweet = tweetArea.value;
		let j = new XMLHttpRequest();
		j.open("POST", 'php/actions.php?act=tweet_post');
		j.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
		j.send("tweet="+tweet);
		j.onreadystatechange = function(){
			if (j.readyState == 4 && j.status == 200){
				if (j.responseText){
					tweetArea.value = '';
					drawTweet(tweet, j.responseText, document.getElementById("bgpic").innerText);
					drawTweetToAll(tweet, j.responseText, document.getElementById("bgpic").innerText);
				}
				else{
					alert("ERROR");
				}
			}
		};
	}
	function editTweet(){
		let btn = event.target, tweetDiv = btn.parentNode.parentNode;
		let tweetText = tweetDiv.getElementsByClassName("post_text")[0], message = tweetText.innerText;
		let txt = document.createElement("TEXTAREA");
		tweetDiv.dataset.editing = true;
		txt.value = message;
		tweetText.parentNode.insertBefore(txt,tweetText);
		txt.focus();
		// alert(message);
	}
	function cancelEditing(){
		let btn = event.target, tweetDiv = btn.parentNode.parentNode, txtArea = tweetDiv.getElementsByTagName("TEXTAREA")[0];
		txtArea.remove();
		tweetDiv.removeAttribute('data-editing');
	}
	function saveEditing(){
		let btn = event.target, tweetDiv = btn.parentNode.parentNode, txtArea = tweetDiv.getElementsByTagName("TEXTAREA")[0], tweetText = tweetDiv.getElementsByClassName("post_text")[0];
		let allTweets = document.getElementsByClassName("a_tweet"), aTweetText;
		for (let i = 0; i < allTweets.length; i++){
			if (allTweets[i].dataset.id == tweetDiv.dataset.id){
				aTweetText = allTweets[i].getElementsByClassName("post_text")[0];
				break;
			}
		}
		let tweet = {
			id: tweetDiv.dataset.id,
			content: txtArea.value
		}
		let j = new XMLHttpRequest();
		j.open("POST", "php/actions.php?act=tweet_edit");
		j.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
		j.send("tweet="+JSON.stringify(tweet));
		j.onreadystatechange = function(){
			if (j.readyState == 4 && j.status == 200){
				if (j.responseText == '1'){
					txtArea.remove();
					tweetText.innerText = tweet.content;
					aTweetText.innerText = tweet.content;
					tweetDiv.removeAttribute('data-editing');
				}
				else{
					alert("ERROR. You can't edit this tweet");
				}
			}
		};
	}
	function signOut(){
		let j = new XMLHttpRequest();
		j.open("GET", "php/actions.php?act=sign_out");
		j.send();
		j.onreadystatechange = function(){
			if (j.readyState == 4 && j.status == 200){
				if (j.responseText){
					window.location.replace("auth.php");
				}
				else{
					alert("ERROR");
				}
			}
		};
	}
	function drawTweet(content, tweetID, author){
		let div = document.createElement("DIV"), post_content = document.createElement("DIV"), post_buttons = document.createElement("DIV");
		post_content.classList.add("post_content");
		post_buttons.classList.add("post_buttons");
		div.dataset["id"] = tweetID;
		div.appendChild(post_content);
		div.appendChild(post_buttons);

		let small_ava = document.createElement("DIV"), post_data = document.createElement("DIV");
		small_ava.classList.add("small_ava");
		post_data.classList.add("post_data");
		post_content.appendChild(small_ava);
		post_content.appendChild(post_data);
		
		let post_author = document.createElement("DIV"), post_date = document.createElement("DIV"), post_text = document.createElement("DIV");
		post_author.classList.add("post_author");
		post_date.classList.add("post_date");
		post_text.classList.add("post_text");
		post_data.appendChild(post_author);
		post_data.appendChild(post_date);
		post_data.appendChild(post_text);
		
		let btnE = document.createElement("BUTTON"), btnS = document.createElement("BUTTON"), btnC = document.createElement("BUTTON");
		btnE.innerText = "Edit";
		btnS.innerText = "Save";
		btnC.innerText = "Cancel";
		btnE.classList.add("edit_btn");
		btnC.classList.add("sc_btns");
		btnS.classList.add("sc_btns");
		post_buttons.appendChild(btnE);
		post_buttons.appendChild(btnS);
		post_buttons.appendChild(btnC);
		btnE.onclick = editTweet;
		btnC.onclick = cancelEditing;
		btnS.onclick = saveEditing;

		post_author.innerText = author;
		post_date.innerText = "now";
		post_text.innerText = content;

		let post_div = document.getElementById("post_div");
		post_div.parentNode.insertBefore(div, post_div.nextSibling);
	}
	function drawTweetToAll(content, tweetID, author){
		let div = document.createElement("DIV"), post_content = document.createElement("DIV"), post_buttons = document.createElement("DIV");
		post_content.classList.add("post_content");
		post_buttons.classList.add("post_buttons");
		div.dataset["id"] = tweetID;
		div.appendChild(post_content);
		div.appendChild(post_buttons);

		let small_ava = document.createElement("DIV"), post_data = document.createElement("DIV");
		small_ava.classList.add("small_ava");
		post_data.classList.add("post_data");
		post_content.appendChild(small_ava);
		post_content.appendChild(post_data);
		
		let post_author = document.createElement("DIV"), post_date = document.createElement("DIV"), post_text = document.createElement("DIV");
		post_author.classList.add("post_author");
		post_date.classList.add("post_date");
		post_text.classList.add("post_text");
		post_data.appendChild(post_author);
		post_data.appendChild(post_date);
		post_data.appendChild(post_text);
		
		let btnL = document.createElement("BUTTON");
		btnL.innerText = "Like";
		btnL.onclick = () => {alert("Nobody cares");}
		post_buttons.appendChild(btnL);

		post_author.innerText = author;
		post_date.innerText = "now";
		post_text.innerText = content;

		let allFeed = document.getElementById("allfeed");
		allFeed.insertBefore(div, allFeed.firstChild);
	}
	function toogleToAll(){
		document.getElementsByTagName("MAIN")[0].dataset.show = "all";
	}
	function toogleToMy(){
		document.getElementsByTagName("MAIN")[0].dataset.show = "my";
	}
</script>
</html>