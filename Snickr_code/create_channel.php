<?php

include 'connect.php';
include 'header1.php';
 
echo '<h2>Create a Channel</h2>';
if(!isset($_SESSION['uid'])){
    //the user is not signed in
    echo 'Sorry, you have to be <a href="signin.php">signed in</a> to create a channel.';
}
else{
    //retrive workspaces this user joined
    $Uid = $_SESSION['uid'];  
    $sql = $con -> prepare("SELECT * 
                            FROM Wmembers w1 JOIN Workspaces w2 ON w1.wid = w2.wid 
                            WHERE w1.uid = ?");
    $sql -> bind_param("i",$Uid);
    $sql -> execute();
    $res = $sql -> get_result();
    $nrow = mysqli_num_rows($res);
    if(!$sql){
        echo '<p style="color:Red;font-size:120%">Something goes wrong. Please try again later.</p>';
    }
    else{
        echo '<form method="post" action="">
            Subject:<br> 
                <input type="text" name="cname" /><br>
            Type:'; 
         
        echo '<select name="chan_type">';
            echo '<option value="public">Public</option>';
            echo '<option value="private">Private</option>';
            echo '<option value="direct">Direct</option>';
        echo '</select><br>';

        /* create a dynamic, multiple selection field, 
        so users can created multiple channels under different workspaces at a time */
        echo 'Workspace:<br>';
        echo '<select name="in_workspace[]" multiple size = '.$nrow.'>';
            while($row = mysqli_fetch_array($res)){
                echo '<option value="'.$row['wid'].'">'.$row['wname'].'</option>';
            }
        echo '</select><br>'; 

        echo '<input type="submit" value="Create channel" /> </form>';
    }

    if($_SERVER['REQUEST_METHOD'] == 'POST'){
        //first check if the subject is empty
        if(empty($_POST['cname'])){
            echo '<p style="color:Red;font-size:120%">The Subject field must not be empty.</p>';
        }
        else{
            //check if at least one workspace is selected
            if(empty($_POST['in_workspace'])){
                echo '<p style="color:Red;font-size:120%">Please at least select one workspace.</p>';
            }
            else{
                //start the transaction
                $query  = "BEGIN WORK;";
                $res = mysqli_query($con, $query);
                 
                if(!$res){
                    echo '<p style="color:Red;font-size:120%">Server error. Please try again later.</p>';
                }
                else{
                    foreach($_POST['in_workspace'] as $In_workspace){
                        $Chan_name = htmlspecialchars($_POST['cname']);
                        $Wid = htmlspecialchars($In_workspace);
                        $Chan_type = htmlspecialchars($_POST['chan_type']);
                        $sql = $con -> prepare("INSERT INTO Channels(cname, ctime, ccreatorid, wid, ctype)
                                                VALUES(?, now(), ?, ?, ?)");
                        $sql -> bind_param("siis",$Chan_name,$Uid,$Wid,$Chan_type);
                        $sql -> execute();
                        if(!$sql){
                            //something went wrong, display the error
                            echo '<p style="color:Red;font-size:120%">Server error. Please try again later.</p>';
                            $sql = "ROLLBACK;";
                            $res = mysqli_query($con, $sql);
                        }
                        else{
                            //retrieve the newly created wid from database
                            $Cid = mysqli_insert_id($con);
                             
                            if($Chan_type == 'public'){
                                //insert all users in this workspace into Cmembers table when type is public
                                $sql_wm = $con -> prepare("SELECT * FROM Wmembers WHERE wid= ?");
                                $sql_wm -> bind_param("i",$Wid);
                                $sql_wm -> execute();
                                $res_wm = $sql_wm -> get_result();
                                while($row_wm = mysqli_fetch_array($res_wm)){
                                    $Uid_wm = $row_wm['uid'];
                                    $sql_p = $con -> prepare("INSERT INTO Cmembers(cid, uid)
                                                            VALUES (?,?)");
                                    $sql_p -> bind_param("ii",$Cid,$Uid_wm);
                                    $sql_p -> execute();
                                    if(!$sql_p){
                                        //something went wrong, display the error
                                        echo '<p style="color:Red;font-size:120%">Server error. Please try again later.</p>';
                                        $sql = "ROLLBACK;";
                                        $res = mysqli_query($con, $sql);
                                    }
                                    else{
                                        $sql = "COMMIT;";
                                        $res = mysqli_query($con, $sql);
                                    }
                                }
                                //retrieve wname
                                $sql2 = $con -> prepare("SELECT wname
                                                        FROM Workspaces
                                                        WHERE wid=?");
                                $sql2 -> bind_param("i",$Wid);
                                $sql2 -> execute();
                                $res2 = $sql2 -> get_result();
                                $row2 = mysqli_fetch_array($res2);
                                echo '<p style="font-size:120%">You have successfully created a new channel under workspace '.$row2['wname'].'. <a href="channel.php?id='.$Cid.'">Click me </a> to go to your newly created channel: '.$Chan_name.'.</p>';
                            }
                            else{
                                //channel is private or direct
                                //only insert the creator to the Cmembers table
                                $sql = $con -> prepare("INSERT INTO Cmembers(uid, cid)
                                                        VALUES (?,?)");
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
                                    //retrieve wname       
                                    $sql2 = $con -> prepare("SELECT wname
                                                            FROM Workspaces
                                                            WHERE wid=?");
                                    $sql2 -> bind_param("i",$Wid);
                                    $sql2 -> execute();
                                    $res2 = $sql2 -> get_result();
                                    $row2 = mysqli_fetch_array($res2);
                                    echo '<p style="font-size:120%">You have successfully created a new channel under workspace '.$row2['wname'].'. <a href="channel.php?id='.$Cid.'">Click me </a> to go to your newly created channel: '.$Chan_name.'.</p>';
                                }
                            }
                        }
                    }
                }
            }
        }
    }
}
 
include 'footer.php';
?>