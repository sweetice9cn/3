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
        $Wid = htmlspecialchars($_GET['id']);
        //anthenticate if this user is an admin
        $Uid_ad = $_SESSION['uid'];
        $sql_ad = $con -> prepare("SELECT aid FROM Admins WHERE uid = ? AND wid = ?");
        $sql_ad -> bind_param("ii",$Uid_ad,$Wid);
        $sql_ad -> execute();
        $res_ad = $sql_ad -> get_result();
        if(mysqli_num_rows($res_ad) == 0){
            //this user is not an admin of this workspace
            echo '<p style="color:Red;font-size:120%">You are not authorized for this operation.</p>';
        }
        else{
            //retrieve all users in this workspace except the operator himself/herself
            $sql_m = $con -> prepare("SELECT * 
                                    FROM Users u JOIN Wmembers w ON u.uid = w.uid 
                                    WHERE w.wid = ? AND w.uid != ?");
            $sql_m -> bind_param("ii",$Wid,$Uid_ad);
            $sql_m -> execute();
            $res_m = $sql_m -> get_result();
            $nrow_m = mysqli_num_rows($res_m);
            if($nrow_m == 0){
                echo '<p style="color:Red;font-size:120%">There are no users in this workspace.</p>';
            }
            else{
                echo '<form method="post" action="">';
                /* create a dynamic, multiple selection field, 
                so admins can kick multiple users at a time */
                echo 'Choose members you want to kick:<br>';
                echo '<select name="k_member[]" multiple size = '.$nrow_m.')>';
                    while($row_m = mysqli_fetch_array($res_m)){
                        echo '<option value="'.$row_m['uid'].'">'.$row_m['unickname'].'</option>';
                    }
                echo '</select><br>'; 
                echo '<input type="submit" value="Kick" /> </form>';

                if($_SERVER['REQUEST_METHOD'] == 'POST'){
                    //check if the admin select at least one person to kick
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
                            //delete member from workspace
                            foreach($_POST['k_member'] as $K_member){
                                $Uid = htmlspecialchars($K_member);
                                $sql = $con -> prepare("DELETE FROM Wmembers
                                                    WHERE uid = ? AND wid = ?");
                                $sql -> bind_param("ii",$Uid,$Wid);
                                $sql -> execute();
                                //retrieve nickname
                                $sql2 = $con -> prepare("SELECT unickname
                                                        FROM Users
                                                        WHERE uid= ?");
                                $sql2 -> bind_param("i",$Uid);
                                $sql2 -> execute();
                                $res2 = $sql2 -> get_result();
                                $row2 = mysqli_fetch_array($res2);
                                echo '<p style="font-size:120%">User ' .$row2['unickname']. ' has been successfully kicked from this workspace.</p>';
                                if(!$sql){
                                    //something went wrong, display the error
                                    echo '<p style="color:Red;font-size:120%">Server error. Please try again later.</p>';
                                    $sql = "ROLLBACK;";
                                    $res = mysqli_query($con, $sql);
                                }
                                else{
                                    //delete from admin table if this user is an admin
                                    $sql_a = $con -> prepare("SELECT *
                                                            FROM Admins
                                                            WHERE uid = ? AND wid = ?");
                                    $sql_a -> bind_param("ii",$Uid,$Wid);
                                    $sql_a -> execute();
                                    $res_a = $sql_a -> get_result();
                                    if(!$sql_a){
                                        echo '<p style="color:Red;font-size:120%">Server error. Please try again later.</p>';
                                        $sql = "ROLLBACK;";
                                        $res = mysqli_query($con, $sql);
                                    }
                                    else{
                                        $nrow_a = mysqli_num_rows($res_a);
                                        if($nrow_a != 0){
                                            $sql = $con -> prepare("DELETE FROM Admins
                                                                    WHERE uid = ? AND wid = ?");
                                            $sql -> bind_param("ii",$Uid,$Wid);
                                            $sql -> execute();
                                            if(!$sql){
                                                echo '<p style="color:Red;font-size:120%">Server error. Please try again later.</p>';
                                                $sql = "ROLLBACK;";
                                                $res = mysqli_query($con, $sql);
                                            }
                                            else{  
                                                echo '<p style="font-size:120%">User ' .$row2['unickname']. ' has been successfully remove from admins.</p>';
                                                //delete member from channels in this workspace
                                                $sql_c = $con -> prepare("SELECT *
                                                                        FROM Channels
                                                                        WHERE wid = ?");
                                                $sql_c -> bind_param("i",$Wid);
                                                $sql_c -> execute();
                                                $res_c = $sql_c -> get_result();
                                                if(!$sql_c){
                                                    echo '<p style="color:Red;font-size:120%">Server error. Please try again later.</p>';
                                                    $sql = "ROLLBACK;";
                                                    $res = mysqli_query($con, $sql);
                                                }
                                                else{
                                                    $nrow_c = mysqli_num_rows($res_c);
                                                    if($nrow_c != 0){
                                                        while($row_c = mysqli_fetch_array($res_c)){
                                                            $Cid = $row_c['cid'];
                                                            $sql = $con -> prepare("DELETE FROM Cmembers
                                                                                WHERE uid = ? AND cid = ?");
                                                            $sql -> bind_param("ii",$Uid,$Cid);
                                                            $sql -> execute();
                                                            if(!$sql){
                                                                //something went wrong, display the error
                                                                echo '<p style="color:Red;font-size:120%">Server error. Please try again later.</p>';
                                                                $sql = "ROLLBACK;";
                                                                $res = mysqli_query($con, $sql);
                                                            }
                                                            else{
                                                                // users kicked from workspaces, admin, and channels table
                                                                $sql = "COMMIT;";
                                                                $res = mysqli_query($con, $sql);
                                                                echo '<p style="font-size:120%">User ' .$row2['unickname']. ' has been successfully kicked from underlying channels.</p>';
                                                            }
                                                        }
                                                    }
                                                    else{
                                                        //this user is not member of any underlying channels
                                                        // users kicked from workspaces, admin table
                                                        $sql = "COMMIT;";
                                                        $res = mysqli_query($con, $sql);
                                                    }
                                                }
                                            }
                                        }
                                        else{
                                            // this user is not an admin
                                            //delete member from channels in this workspace
                                            $sql_c = $con -> prepare("SELECT *
                                                                    FROM Channels
                                                                    WHERE wid = ?");
                                            $sql_c -> bind_param("i",$Wid);
                                            $sql_c -> execute();
                                            $res_c = $sql_c -> get_result();
                                            if(!$sql_c){
                                                echo '<p style="color:Red;font-size:120%">Server error. Please try again later.</p>';
                                                $sql = "ROLLBACK;";
                                                $res = mysqli_query($con, $sql);
                                            }
                                            else{
                                                $nrow_c = mysqli_num_rows($res_c);
                                                if($nrow_c != 0){
                                                    while($row_c = mysqli_fetch_array($res_c)){
                                                        $Cid = $row_c['cid'];
                                                        $sql = $con -> prepare("DELETE FROM Cmembers
                                                                            WHERE uid = ? AND cid = ?");
                                                        $sql -> bind_param("ii",$Uid,$Cid);
                                                        $sql -> execute();
                                                        if(!$sql){
                                                            //something went wrong, display the error
                                                            echo '<p style="color:Red;font-size:120%">Server error. Please try again later.</p>';
                                                            $sql = "ROLLBACK;";
                                                            $res = mysqli_query($con, $sql);
                                                        }
                                                        else{
                                                            //users kicked from workspaces, and channels table
                                                            $sql = "COMMIT;";
                                                            $res = mysqli_query($con, $sql);
                                                            echo '<p style="font-size:120%">User ' .$row2['unickname']. ' has been successfully kicked from underlying channels.</p>';
                                                        }
                                                    }
                                                }
                                                else{
                                                    //this user is not member of any underlying channels
                                                    //users kicked from workspace table
                                                    $sql = "COMMIT;";
                                                    $res = mysqli_query($con, $sql);
                                                }
                                            }
                                        }
                                    }
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