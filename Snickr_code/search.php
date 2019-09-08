<?php

include 'connect.php';
include 'header1.php';

$query = htmlspecialchars($_GET['query']); 
//set minimum length of the query inputs
$min_length = 1;

if(strlen($query) >= $min_length){ 
	//display bookmark button to users
	echo '<a class="item" href="add_bookmark.php?url=search&query='.$query.'">Add to bookmarks.</a><br>';
	$query = "%{$query}%";
	//query length is more or equal minimum length
	// use HTML Encoding to guard against Cross-site scripting attack
	//$query = htmlspecialchars($query); 
	// use prepared statement to guard against SQL injection attack
	$sql = $con -> prepare("SELECT * FROM Messages
							WHERE text LIKE ?
							ORDER BY cid ASC");
	$sql -> bind_param("s",$query);
	$sql -> execute();
	$raw_results = $sql -> get_result();
	
	if(!$sql){
		echo '<p style="color:Red;font-size:120%">Server error. Please try again later.</p>';
	}
	else{
		if(mysqli_num_rows($raw_results) > 0){ 
			/*some initial values for later use:
			i: stores number of results
			c: stores channel id
			r: stores channel name
			t: stores channel type
			w: stores workspace name*/
			$index = 0;
			$i[$index] = 0;
			$c[$index] = 0;
			$r[$index] = 0;
			$t[$index] = 0;
			$w[$index] = 0;
			$Cid = 0;

			while($row = mysqli_fetch_array($raw_results)){
				//store old cid in session
				$_SESSION['cid'] = $Cid;
				//get new cid
				$Cid = $row['cid'];
				//retrieve the channels and workspace information where can find this part of message
				$sql_c = "SELECT * FROM Channels c JOIN Workspaces w ON c.wid = w.wid
						WHERE cid = '$Cid'
						ORDER BY cid ASC";
				$res_c = mysqli_query($con, $sql_c);
				$row_c = mysqli_fetch_array($res_c);

				/*Do not output the message directly, but output the channel page where user can read this message,
				since this user may not be authorized to read this mesaage.*/
				if($Cid == $_SESSION['cid']){
					/*find multiple results in one channel,
					increase the number of results by 1*/
					$i[$index] = $i[$index] + 1;
				}
				else{
					/*find only one result in one channel,
					set number of result to 1,
					store new cid, cname, ctype, and wnameinto array*/
					$index = $index + 1;
					$i[$index] = 1;
					$c[$index] = $Cid;
					$r[$index] = $row_c['cname'];
					$t[$index] = $row_c['ctype'];
					$w[$index] = $row_c['wname'];
				}
			}
			//print the results
			foreach($i as $key => $value){
				if($i[$key] == 1){
					echo '<p style="font-size:120%">Find '.$i[$key].' result in '.$t[$key].' channel: <a href="channel.php?id='.$c[$key].'">' .$r[$key]. '</a> under workspace '.$w[$key].'.</p>';
				}
				elseif($i[$key] > 1){
					echo '<p style="font-size:120%">Find '.$i[$key].' results in '.$t[$key].' channel: <a href="channel.php?id='.$c[$key].'">' .$r[$key]. '</a> under workspace '.$w[$key].'.</p>';
				}
			}
		}
		else{ 
			// there are no matching results
			echo '<p style="font-size:120%">No results.</p>';
		}
	}
}
else{
	// if query length is less than minimum
	echo 'Minimum input length is '.$min_length.'.';
}
include 'footer.php';
?>
