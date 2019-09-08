<?php

include 'connect.php';
include 'header1.php';

echo '<h2>Post a Message</h2>';
if(!isset($_SESSION['uid'])){
    //the user is not signed in
    echo 'Sorry, you have to be <a href="signin.php">signed in</a> to post a message.';
}
else{
    //retrive channels this user joined
    $Uid = $_SESSION['uid'];  
    $sql = $con -> prepare("SELECT * 
                            FROM Cmembers c1 JOIN Channels c2 ON c1.cid = c2.cid 
                            WHERE c1.uid =?");
    $sql -> bind_param("i",$Uid);
    $sql -> execute();
    $res = $sql -> get_result();
    $nrow = mysqli_num_rows($res);
    if(!$res){
        echo '<p style="color:Red;font-size:120%">Something goes wrong. Please try again later.</p>';
    }
    else{
        echo '<form method="post" action="">
            Content:<br> 
                <textarea rows="12" cols="50" name="text"></textarea><br>
            Channel:<br>'; 
        /* create a dynamic, multiple selection field, 
        so users can post multiple messages in different channels at a time */
        echo '<select name="in_channel[]" multiple size = '.$nrow.'>';
            while($row = mysqli_fetch_array($res)){
                echo '<option value="'.$row['cid'].'">'.$row['cname'].'</option>';
            }
        echo '</select><br>'; 

        echo '<input type="submit" value="Post" /> </form>';
    }

    if($_SERVER['REQUEST_METHOD'] == 'POST'){
        if(empty($_POST['text'])){
            echo '<p style="color:Red;font-size:120%">You cannot post a blank message.</p>';
        }
        else{
            //start the transaction
            $query  = "BEGIN WORK;";
            $res = mysqli_query($con, $query);
             
            if(!$res){
                echo '<p style="color:Red;font-size:120%">Server error. Please try again later.</p>';
                }
            else{
                foreach($_POST['in_channel'] as $In_channel){
                    $Content = htmlspecialchars($_POST['text']);
                    $Cid = htmlspecialchars($In_channel);
                    $sql = $con -> prepare("INSERT INTO Messages(senderid, cid, mtime, text)
                                            VALUES(?, ?, now(), ?)");
                    $sql -> bind_param("iis",$Uid,$Cid,$Content);
                    $sql -> execute();
                    if(!$sql){
                        //something went wrong, display the error
                        echo '<p style="color:Red;font-size:120%">Server error. Please try again later.</p>';
                        $sql = "ROLLBACK;";
                        $res = mysqli_query($con, $sql);
                    }
                    else{
                        $sql = "COMMIT;";
                        $res = mysqli_query($con, $sql);
                        //retrieve cname and ctype      
                        $sql2 = $con -> prepare("SELECT *
                                                FROM Channels
                                                WHERE cid= ?");
                        $sql2 -> bind_param("i",$Cid);
                        $sql2 -> execute();
                        $res2 = $sql2 -> get_result();
                        $row2 = mysqli_fetch_array($res2);
                        // retrieve wname
                        $Wid2 = $row2['wid'];
                        $sql3 = $con -> prepare("SELECT wname
                                                FROM Workspaces
                                                WHERE wid= ?");
                        $sql3 -> bind_param("i",$Wid2);
                        $sql3 -> execute();
                        $res3 = $sql3 -> get_result();
                        $row3 = mysqli_fetch_array($res3);
                        echo '<p style="font-size:120%">You have successfully post a new message under '.$row2['ctype'].' channel: '.$row2['cname'].' in workspace '.$row3['wname'].' . <a href="channel.php?id='.$Cid.'">Click me </a> to go to this channel.</p>';
                    }
                }
            }
        }
    }
}
 
include 'footer.php';
?>