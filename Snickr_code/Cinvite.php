<?php

include 'connect.php';
include 'header1.php';

echo '<h3>Invite new member</h3>';
if(!isset($_SESSION['uid'])){
    //the user is not signed in
    echo '<p style="color:Red;font-size:120%">Sorry, you have to be <a href="signin.php">signed in</a> to invite somebody.</p>';
}
else{
    //check if id been set
    if(!isset($_GET['id'])){
        echo '<p style="color:Red;font-size:120%">Lost in space? No worries. <a href="index.php">Click me</a> to go back.</p>';
    }
    else{
        //retrieve wid
        $Cid = htmlspecialchars($_GET['id']);
        $sql_w = $con -> prepare("SELECT * 
                                FROM Channels WHERE cid =?");
        $sql_w -> bind_param("i",$Cid);
        $sql_w -> execute();
        $res_w = $sql_w -> get_result();
        $row_w = mysqli_fetch_array($res_w);
        $Wid = $row_w['wid'];
        //anthenticate if this user is a creator and an workspace member
        $Uid_cr = $_SESSION['uid'];
        $sql_cr = $con -> prepare("SELECT * 
                                FROM Channels c JOIN Wmembers w ON  c.ccreatorid = w.uid 
                                WHERE c.ccreatorid = ? AND c.cid = ? AND w.wid = ?");
        $sql_cr -> bind_param("iii",$Uid_cr,$Cid,$Wid);
        $sql_cr -> execute();
        $res_cr = $sql_cr -> get_result();
        $row_cr = mysqli_fetch_array($res_cr);
        $C_type = $row_cr['ctype'];
        $C_name = $row_cr['cname'];
        if(mysqli_num_rows($res_cr) == 0){
            //this user is not the creator of this channel or not a member of this workspace
            echo '<p style="color:Red;font-size:120%">You are not authorized for this operation.</p>';
        }
        else{
            if($C_type == 'public'){
                echo '<p style="color:Red;font-size:120%">You cannot invite more users into a public channel.</p>';
            }
            else{
                //retrieve all users in this workspace but not in this channel
                $sql_m = $con -> prepare("SELECT * 
                                        FROM Users u JOIN Wmembers w ON u.uid = w.uid
                                        WHERE w.wid = ?
                                        AND w.uid NOT IN( 
                                        SELECT uid FROM Cmembers WHERE cid = ?)");
                $sql_m -> bind_param("ii",$Wid,$Cid);
                $sql_m -> execute();
                $res_m = $sql_m -> get_result();
                $nrow_m = mysqli_num_rows($res_m);
                if($C_type == 'direct'){
                    //count the number of members of a direct channel
                    $sql_d = $con -> prepare("SELECT * FROM Cmembers WHERE cid = ?");
                    $sql_d -> bind_param("i",$Cid);
                    $sql_d -> execute();
                    $res_d = $sql_d -> get_result();
                    $row_d = mysqli_fetch_array($res_d);
                    if(mysqli_num_rows($res_d) > 1){
                        echo '<p style="color:Red;font-size:120%">Maximum member limitation reach.</p>';
                    }
                    else{
                        if($nrow_m == 0){
                            echo '<p style="color:Red;font-size:120%">There are no other members in this workspace you can invite.</p>';
                        }
                        else{
                            /* create a dynamic, single selection field for a direct channel,
                            so creator can only invite one user*/
                            echo '<form method="post" action="">';
                            echo 'Choose nickname of the member you want to invite:<br>';
                            echo '<select name="in_member")>';
                                while($row_m = mysqli_fetch_array($res_m)){
                                    echo '<option value="'.$row_m['uid'].'">'.$row_m['unickname'].'</option>';
                                }
                            echo '</select><br>'; 
                            echo '<input type="submit" value="Invite" /> </form>';

                            if($_SERVER['REQUEST_METHOD'] == 'POST'){
                                //check if the creator select at least one person to invite
                                if(empty($_POST['in_member'])){
                                    echo '<p style="color:Red;font-size:120%">Please select at least one user.</p>';
                                }
                                else{       
                                    //retrieve nickname
                                    $Uid = htmlspecialchars($_POST['in_member']);
                                    $sql2 = $con -> prepare("SELECT unickname
                                                    FROM Users
                                                    WHERE uid= ?");
                                    $sql2 -> bind_param("i",$Uid);
                                    $sql2 -> execute();
                                    $res2 = $sql2 -> get_result();
                                    $row2 = mysqli_fetch_array($res2);                       
                                    //first check if this admin has sent invitaions to others before                                 
                                    $sql = $con -> prepare("SELECT * 
                                                            FROM Cinvites 
                                                            WHERE cid =?");
                                    $sql -> bind_param("i",$Cid);
                                    $sql -> execute();
                                    $res = $sql -> get_result();
                                    $row = mysqli_fetch_array($res);
                                    if(mysqli_num_rows($res) != 0){
                                        echo '<p style="color:Red;font-size:120%">You can only send invitaion to 1 user for a direct channel. You cannot send another one until that user decline your last invitaion.</p>';
                                    }
                                    else{
                                        //new invitation, insert into Cinvite table
                                        $sql = $con -> prepare("INSERT INTO Cinvites(cid,uid,cinvitetime)
                                                            VALUES(?, ?, now())");
                                        $sql -> bind_param("ii",$Cid,$Uid);
                                        $sql -> execute();
                                        if(!$sql){
                                            //something went wrong, display the error
                                            echo '<p style="color:Red;font-size:120%">Server error. Please try again later.</p>';
                                        }
                                        else{
                                            echo '<br><p style="font-size:120%">You successfully send '.$row2['unickname'].' the invitation to channel '.$C_name.'.</p>';
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
                else{
                    //private channel
                    if($nrow_m == 0){
                        echo '<p style="color:Red;font-size:120%">There are no other members in this workspace you can invite.</p>';
                    }
                    else{
                        /* create a dynamic, multiple selection field for a private channel, 
                        so creator can invite multiple users to this channel at a time */
                        echo '<form method="post" action="">';
                        echo 'Choose nickname of the member you want to invite:<br>';
                        echo '<select name="in_member[]" multiple size = '.$nrow_m.')>';
                            while($row_m = mysqli_fetch_array($res_m)){
                                echo '<option value="'.$row_m['uid'].'">'.$row_m['unickname'].'</option>';
                            }
                        echo '</select><br>'; 
                        echo '<input type="submit" value="Invite" /> </form>';

                        if($_SERVER['REQUEST_METHOD'] == 'POST'){
                            //check if the creator select at least one person to invite
                            if(empty($_POST['in_member'])){
                                echo '<p style="color:Red;font-size:120%">Please at least select one user.</p>';
                            }
                            else{
                                foreach($_POST['in_member'] as $In_member){
                                    //retrieve nickname
                                    $Uid = htmlspecialchars($In_member);
                                    $sql2 = $con -> prepare("SELECT unickname
                                                    FROM Users
                                                    WHERE uid= ?");
                                    $sql2 -> bind_param("i",$Uid);
                                    $sql2 -> execute();
                                    $res2 = $sql2 -> get_result();
                                    $row2 = mysqli_fetch_array($res2);                       
                                    //first check if this admin has sent invitaions to this user before                                 
                                    $sql = $con -> prepare("SELECT * 
                                                            FROM Cinvites 
                                                            WHERE uid = ? AND cid =?");
                                    $sql -> bind_param("ii",$Uid,$Cid);
                                    $sql -> execute();
                                    $res = $sql -> get_result();
                                    $row = mysqli_fetch_array($res);
                                    if(mysqli_num_rows($res) != 0){
                                        echo '<p style="color:Red;font-size:120%">You have sent invitaions to '.$row2['unickname'].' before. You cannot send again until this user decline your last invitaion.</p>';
                                    }
                                    else{
                                        //new invitation, insert into Cinvite table
                                        $sql = $con -> prepare("INSERT INTO Cinvites(cid,uid,cinvitetime)
                                                            VALUES(?, ?, now())");
                                        $sql -> bind_param("ii",$Cid,$Uid);
                                        $sql -> execute();
                                        if(!$sql){
                                            //something went wrong, display the error
                                            echo '<p style="color:Red;font-size:120%">Server error. Please try again later.</p>';
                                        }
                                        else{
                                            echo '<br><p style="font-size:120%">You successfully send user '.$row2['unickname'].' the invitation to channel '.$C_name.'.</p>';
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
    echo '<a href="channel.php?id='.$Cid.'">Click me</a> to go back to previous page.<br>';
    }  
}
include 'footer.php';
?>