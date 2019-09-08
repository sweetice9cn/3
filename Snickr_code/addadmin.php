<?php

include 'connect.php';
include 'header1.php';

echo '<h3>Add new admins</h3>';
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
        $Wid = htmlspecialchars($_GET['id']); 

        //first, anthenticate if this user is an admin
        $Uid_ad = $_SESSION['uid'];
        $sql_ad = $con -> prepare("SELECT aid FROM Admins WHERE uid = ? AND wid = ?");
        $sql_ad -> bind_param("ii",$Uid_ad,$Wid);
        $sql_ad -> execute();
        $res_ad = $sql_ad -> get_result();
        $row_ad = mysqli_fetch_array($res_ad);
        if(mysqli_num_rows($res_ad) == 0){
            //this user is not an admin of this workspace
            echo '<p style="color:Red;font-size:120%">You are not authorized for this operation.</p>';
        }
        else{
            //retrieve all non-admin users in this workspace
            $sql_m = $con -> prepare("SELECT * 
                                    FROM Users u JOIN Wmembers w ON u.uid = w.uid 
                                    WHERE w.wid = ? 
                                    AND w.uid NOT IN( 
                                    SELECT uid FROM Admins WHERE wid =?)");
            $sql_m -> bind_param("ii",$Wid,$Wid);
            $sql_m -> execute();
            $res_m = $sql_m -> get_result();
            $nrow_m = mysqli_num_rows($res_m);
            if($nrow_m == 0){
                echo '<p style="color:Red;font-size:120%">There are no other users you can add them to Admins.</p>';
            }
            else{
                echo '<form method="post" action="">';
                /* create a dynamic, multiple selection field, 
                so admins can assign multiple users as admin at a time */
                echo 'Choose nickname of the members you want to assign as admin:<br>';
                echo '<select name="member[]" multiple size = '.$nrow_m.')>';
                    while($row_m = mysqli_fetch_array($res_m)){
                        echo '<option value="'.$row_m['uid'].'">'.$row_m['unickname'].'</option>';
                    }
                echo '</select><br>'; 
                echo '<input type="submit" value="Assign" /> </form>';

                if($_SERVER['REQUEST_METHOD'] == 'POST'){
                    //check if the admin select at least one user
                    if(empty($_POST['member'])){
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
                            foreach($_POST['member'] as $member){
                                $Uid = htmlspecialchars($member);
                                $sql = $con -> prepare("INSERT INTO Admins(uid,wid,atime)
                                                        VALUES(?, ?, now())");
                                $sql -> bind_param("ii",$Uid,$Wid);
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
                                    echo '<br><p style="font-size:120%">User ' .$row2['unickname']. 'has been successfully assigned as admin of this workspace.</p>';

                                }                 
                            }
                            //refresh this page
                            header("Refresh:1.5");
                            exit();
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