<?php

include 'connect.php';
include 'header1.php';

echo '<h3>Kick members</h3>';
if(!isset($_SESSION['uid'])){
    //the user is not signed in
    echo '<p style="color:Red;font-size:120%">Sorry, you have to be <a href="signin.php">signed in</a> to kick somebody.</p>';
}
else{
    //check if id been set
    if(!isset($_GET['id'])){
        echo '<p style="color:Red;font-size:120%">Lost in space? No worries. <a href="index.php">Click me</a> to go back.</p>';
    }
    else{
        //first, anthenticate if this user is a creator himself/herself
        $Cid = htmlspecialchars($_GET['id']); 
        $Uid_cr = $_SESSION['uid'];
        $sql_cr = $con -> prepare("SELECT * FROM Channels WHERE ccreatorid = ? AND cid = ?");
        $sql_cr -> bind_param("ii",$Uid_cr,$Cid);
        $sql_cr -> execute();
        $res_cr = $sql_cr -> get_result();
        $row_cr = mysqli_fetch_array($res_cr);
        $Wid = $row_cr['wid'];
        if(mysqli_num_rows($res_cr) == 0){
            //this user is not a creator of this channel
            echo '<p style="color:Red;font-size:120%">You are not authorized for this operation.</p>';
        }
        else{
            //retrieve all users in this channel except the creator
            $sql_m = $con -> prepare("SELECT * 
                                    FROM Users u JOIN Cmembers c ON u.uid = c.uid 
                                    WHERE c.cid = ? AND c.uid != ?");
            $sql_m -> bind_param("ii",$Cid,$Uid_cr);
            $sql_m -> execute();
            $res_m = $sql_m -> get_result();
            $nrow_m = mysqli_num_rows($res_m);
            if($nrow_m == 0){
                echo '<p style="color:Red;font-size:120%">There are no users in this channel.</p>';
            }
            else{
                echo '<form method="post" action="">';
                /* create a dynamic, multiple selection field, 
                so creator can kick multiple users at a time */
                echo 'Choose members you want to kick:<br>';
                echo '<select name="k_member[]" multiple size = '.$nrow_m.')>';
                    while($row_m = mysqli_fetch_array($res_m)){
                        echo '<option value="'.$row_m['uid'].'">'.$row_m['unickname'].'</option>';
                    }
                echo '</select><br>'; 
                echo '<input type="submit" value="Kick" /> </form>';

                if($_SERVER['REQUEST_METHOD'] == 'POST'){
                    //check if the creator select at least one person to kick
                    if(empty($_POST['k_member'])){
                        echo '<p style="color:Red;font-size:120%">Please at least select one user.</p>';
                    }
                    else{
                        //start the transaction
                        $query  = "BEGIN WORK;";
                        $res = mysqli_query($con, $query);
                         
                        if(!$res){
                            echo '<p style="color:Red;font-size:120%">Server error. Please try again later.</p>';
                        }
                        else{
                            foreach($_POST['k_member'] as $K_member){
                                $Uid = htmlspecialchars($K_member);
                                $sql = $con -> prepare("DELETE FROM Cmembers
                                                        WHERE uid =? AND cid = ?");
                                $sql -> bind_param("ii",$Uid,$Cid);
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
                                    //retrieve nickname
                                    $sql2 = $con -> prepare("SELECT unickname
                                                            FROM Users
                                                            WHERE uid= ?");
                                    $sql2 -> bind_param("i",$Uid);
                                    $sql2 -> execute();
                                    $res2 = $sql2 -> get_result();
                                    $row2 = mysqli_fetch_array($res2);
                                    echo '<p style="font-size:120%">User ' .$row2['unickname']. 'has been successfully kicked.</p>';
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