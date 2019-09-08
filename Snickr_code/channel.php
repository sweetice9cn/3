<?php

include 'connect.php';
include 'header1.php';

if(!isset($_SESSION['uid'])){
    //the user is not signed in
    echo '<p style="color:Red;font-size:120%">Sorry, you have to be <a href="signin.php">signed in</a> to view a channel.</p>';
}
else{
    //check if id been set
    if(!isset($_GET['id'])){
        echo '<p style="color:Red;font-size:120%">Lost in space? No worries. <a href="index.php">Click me</a> to go back.</p>';
    }
    else{
        //first select the matched channel based on cid
        $Cid = htmlspecialchars($_GET['id']);

        $sql = $con -> prepare("SELECT * 
                                FROM Channels
                                WHERE cid = ?");
        $sql -> bind_param("i",$Cid);
        $sql -> execute();
        $res = $sql -> get_result();
        $row = mysqli_fetch_array($res);
        $Wid = $row['wid'];
        if(!$sql){
            echo '<p style="color:Red;font-size:120%">Server error. Please try again later.</p>';
        }
        else{
            //check if users are authorized members
            $Uid_au = $_SESSION['uid'];  
            $sql_au = $con -> prepare("SELECT * 
                                    FROM Cmembers
                                    WHERE cid = ? AND uid = ?");
            $sql_au -> bind_param("ii",$Cid,$Uid_au);
            $sql_au -> execute();
            $res_au = $sql_au -> get_result();
            if(mysqli_num_rows($res_au) == 0){
                echo '<p style="color:Red;font-size:120%">Sorry, you are not authorized to view this channel.</p>';
            }
            else{
                //user is a member
                //display bookmark button to users
                $Cname = $row['cname'];
                echo '<a class="item" href="add_bookmark.php?url=channel&name='.$Cname.'&id='.$Cid.'">Add to bookmarks</a>';
                //display invite and kick button to private and direct channel creators
                $sql_cr = $con -> prepare("SELECT * 
                                    FROM Channels
                                    WHERE cid = ? AND ccreatorid = ? AND ctype != 'public'");
                $sql_cr -> bind_param("ii",$Cid,$Uid_au);
                $sql_cr -> execute();
                $res_cr = $sql_cr -> get_result(); 
                if(mysqli_num_rows($res_cr) != 0){
                    //user is an creator
                    echo ' - <a class="item" href="Cinvite.php?id='.$Cid.'">Invite new members</a> -
                        <a class="item" href="Ckick.php?id='.$Cid.'">Kick members</a>';
                }

                //display messages
                $row = mysqli_fetch_array($res);
                echo '<h2>Messages in '.$Cname.' channel</h2>';

                $sql = $con -> prepare("SELECT * 
                                    FROM Messages m JOIN Users u ON m.senderid = u.uid
                                    WHERE m.cid = ?
                                    ORDER BY m.mtime DESC");
                $sql -> bind_param("i",$Cid);
                $sql -> execute();
                $res = $sql -> get_result();
             
                if(!$sql){
                    echo '<p style="color:Red;font-size:120%">Server error. Please try again later.</p>';
                }
                else{
                    if(mysqli_num_rows($res) == 0){
                        echo 'There are no messages in this channel yet. <a href="post_message.php">Post one?</a><br>';
                    }
                    else{
                        echo '<table border="1">
                              <tr>
                                <th>Messages</th>
                                <th>Post by</th>
                                <th>Post time</th>
                              </tr>'; 
    
                        while($row = mysqli_fetch_array($res)){               
                            echo '<tr>';
                                echo '<td>';
                                    echo $row['text'];
                                echo '</td>';
                                echo '<td>';
                                    echo $row['unickname'];
                                echo '</td>';
                                echo '<td>';
                                    echo $row['mtime'];
                                echo '</td>';
                            echo '</tr>';
                        }
                        echo '</table>';
                    }
                }
            }
        }
    echo '<a href="workspace.php?id='.$Wid.'">Click me</a> to go back to previous page.<br>';
    }
} 
include 'footer.php';
?>