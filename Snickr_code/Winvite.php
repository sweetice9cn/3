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
        //retrieve this admin's id
        $Wid = htmlspecialchars($_GET['id']); 
        $Uid_ad = $_SESSION['uid'];
        $sql_ad = $con -> prepare("SELECT aid FROM Admins WHERE uid =? AND wid = ?");
        $sql_ad -> bind_param("ii",$Uid_ad,$Wid);
        $sql_ad -> execute();
        $res_ad = $sql_ad -> get_result();
        $row_ad = mysqli_fetch_array($res_ad);
        if(mysqli_num_rows($res_ad) == 0){
            //this user is not an admin of this workspace
            echo '<p style="color:Red;font-size:120%">You are not authorized for this operation.</p>';
        }
        else{
            $Aid = $row_ad['aid'];

            //retrieve all users not in this workspace
            $sql_m = $con -> prepare("SELECT * 
                                    FROM Users 
                                    WHERE uid NOT IN(
                                    SELECT uid FROM Wmembers WHERE wid =?)");
            $sql_m -> bind_param("i",$Wid);
            $sql_m -> execute();
            $res_m = $sql_m -> get_result();
            $nrow_m = mysqli_num_rows($res_m);
            if($nrow_m == 0){
                echo '<p style="color:Red;font-size:120%">All users are already in this workspace.</p>';
            }
            else{
                echo '<form method="post" action="">';
                /* create a dynamic, multiple selection field, 
                so admins can invite multiple users to this workspace at a time */
                echo 'Choose nickname of the members you want to invite:<br>';
                echo '<select name="in_member[]" multiple size = '.$nrow_m.')>';
                    while($row_m = mysqli_fetch_array($res_m)){
                        echo '<option value="'.$row_m['uid'].'">'.$row_m['unickname'].'</option>';
                    }
                echo '</select><br>'; 
                echo '<input type="submit" value="Invite" /> </form>';

                if($_SERVER['REQUEST_METHOD'] == 'POST'){
                    //check if the admin select at least one person to invite
                    if(empty($_POST['in_member'])){
                        echo '<p style="color:Red;font-size:120%">Please at least select one user.</p>';
                    }
                    else{
                        foreach($_POST['in_member'] as $In_member){
                            //get nickname
                            $Uid = htmlspecialchars($In_member);
                            $sql2 = $con -> prepare("SELECT unickname
                                                    FROM Users
                                                    WHERE uid= ?");
                            $sql2 -> bind_param("i",$Uid);
                            $sql2 -> execute();
                            $res2 = $sql2 -> get_result();
                            $row2 = mysqli_fetch_array($res2);
                            // check if this admin has sent invitaions to this user before
                            $sql = $con -> prepare("SELECT * 
                                                    FROM Winvites 
                                                    WHERE uid = ? AND aid =?");
                            $sql -> bind_param("ii",$Uid,$Aid);
                            $sql -> execute();
                            $res = $sql -> get_result();
                            $row = mysqli_fetch_array($res);
                            if(mysqli_num_rows($res) != 0){
                                echo '<p style="color:Red;font-size:120%">You have sent invitaions to '.$row2['unickname'].' before. You cannot send again until this user decline your last invitaion.</p>';
                            }
                            else{
                                //new invitation, insert into Winvite table
                                $sql = $con -> prepare("INSERT INTO Winvites(aid,uid,winvitetime)
                                                        VALUES(?, ?, now())");
                                $sql -> bind_param("ii",$Aid,$Uid);
                                $sql -> execute();
                                if(!$sql){
                                    //something went wrong, display the error
                                    echo '<p style="color:Red;font-size:120%">Server error. Please try again later.</p>';
                                }
                                else{

                                    echo '<br><p style="font-size:120%">Invitaion to user ' .$row2['unickname']. ' has successfully sent.</p>'; 
                                }
                            }
                        }
                    }
                }
            }
        }
    echo '<a href="workspace.php?id='.$Wid.'">Click me</a> to go back to previous page.<br>';
    }  
}
include 'footer.php';
?>