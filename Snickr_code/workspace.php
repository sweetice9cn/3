<?php

include 'connect.php';
include 'header1.php';

if(!isset($_SESSION['uid'])){
    //the user is not signed in
    echo '<p style="color:Red;font-size:120%">Sorry, you have to be <a href="signin.php">signed in</a> to view a workspace.</p>';
}
else{
    //check if id been set
    if(!isset($_GET['id'])){
        echo '<p style="color:Red;font-size:120%">Lost in space? No worries. <a href="index.php">Click me</a> to go back.</p>';
    }
    else{
        //first select the mateched workspace based on wid
        $Wid = htmlspecialchars($_GET['id']);
        $sql = $con -> prepare("SELECT * 
                                FROM Workspaces
                                WHERE wid = ?");
        $sql -> bind_param("i",$Wid);
        $sql -> execute();
        $res = $sql -> get_result();
        $row = mysqli_fetch_array($res);
        if(!$sql){
            echo '<p style="color:Red;font-size:120%">Server error. Please try again later.</p>';
        }
        else{
            //check if users are authorized members
            $Uid_au = $_SESSION['uid'];
            $sql_au = $con -> prepare("SELECT * 
                                    FROM Wmembers
                                    WHERE wid = '$Wid' AND uid = ?");
            $sql_au -> bind_param("i",$Uid_au);
            $sql_au -> execute();
            $res_au = $sql_au -> get_result();   
            if(mysqli_num_rows($res_au) == 0){
                echo '<p style="color:Red;font-size:120%">Sorry, you are not authorized to view this workspace.</p>';
            }
            else{
                //user is a member
                //display bookmark button to users
                $Wname = $row['wname'];
                echo '<a class="item" href="add_bookmark.php?url=workspace&name='.$Wname.'&id='.$Wid.'">Add to bookmarks.</a> - ';
                //display invite and kick button to Admins
                $sql_ad = $con -> prepare("SELECT * 
                                            FROM Admins
                                            WHERE wid = ? AND uid = ?");
                $sql_ad -> bind_param("ii",$Wid,$Uid_au);
                $sql_ad -> execute();
                $res_ad = $sql_ad -> get_result();   
                if(mysqli_num_rows($res_ad) != 0){
                    //user is an admin of this workspace
                    echo '<a class="item" href="Winvite.php?id='.$Wid.'">Invite new members</a> -
                        <a class="item" href="Wkick.php?id='.$Wid.'">Kick members</a> -
                        <a class="item" href="addadmin.php?id='.$Wid.'">Add Admins</a>';
                }

                //display channels
                $row = mysqli_fetch_array($res);
                echo '<h2>Channels in '.$Wname.' workspace</h2>';

                //sql query finds channels that are public and private, and direct channel that this user is a member
                $sql = $con -> prepare("SELECT * 
                                        FROM Channels c JOIN Users u ON c.ccreatorid = u.uid
                                        WHERE c.wid = ? 
                                            AND c.cid IN(
                                                SELECT c2.cid 
                                                FROM Channels c1 JOIN Cmembers c2 ON c1.cid = c2.cid
                                                WHERE c1.ctype = 'direct' AND c2.uid = ? AND c1.wid =?)
                                        UNION
                                        SELECT * 
                                        FROM Channels c JOIN Users u ON c.ccreatorid = u.uid
                                        WHERE c.wid = ? AND c.ctype!= 'direct'");
                $sql -> bind_param("iiii",$Wid,$Uid_au,$Wid,$Wid);
                $sql -> execute();
                $res = $sql -> get_result();
             
                if(!$sql){
                    echo '<p style="color:Red;font-size:120%">Server error. Please try again later.</p>';
                }
                else{
                    if(mysqli_num_rows($res) == 0){
                        echo '<br>There are no channels in this workspace yet. <a href="create_channel.php">Create one?</a><br>';
                    }
                    else{
                        echo '<table border="1">
                              <tr>
                                <th>Channels</th>
                                <th>Post by</th>
                                <th>Post time</th>
                                <th>Type</th>
                              </tr>';     
                        while($row = mysqli_fetch_array($res)){               
                            echo '<tr>';
                                echo '<td>';
                                    echo '<h3><a href="channel.php?id='.$row['cid'].'">' .$row['cname'].'</a><h3>';
                                echo '</td>';
                                echo '<td>';
                                    echo $row['unickname'];
                                echo '</td>';
                                echo '<td>';
                                    echo $row['ctime'];
                                echo '</td>';
                                echo '<td>';
                                    echo $row['ctype'];
                                echo '</td>';
                            echo '</tr>';
                        }
                        echo '</table>';
                    }
                }
            }
        }
    }
} 
include 'footer.php';
?>