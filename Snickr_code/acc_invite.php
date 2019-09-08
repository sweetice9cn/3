<?php

include 'connect.php';
include 'header1.php';

if(!isset($_SESSION['uid'])){
    //the user is not signed in
    echo '<p style="color:Red;font-size:120%">Sorry, you have to be <a href="signin.php">signed in</a> to proceed.</p>';
}
else{
    //check if id been set
    if(!isset($_GET['id']) || !isset($_GET['key'])){
        echo '<p style="color:Red;font-size:120%">Lost in space? No worries. <a href="index.php">Click me</a> to go back.</p>';
    }
    else{
        $KEY = htmlspecialchars($_GET['key']);
        $Uid = $_SESSION['uid'];
        if($KEY == 'W'){
            //W is for workspace
            //retrieve aid
            $Aid = htmlspecialchars($_GET['id']); 
            //first check if the invitation message exists in the database
            $sql = $con -> prepare("SELECT * 
                                FROM Winvites w JOIN Admins a ON w.aid= a.aid
                                WHERE w.uid = ? AND w.aid =?");
            $sql -> bind_param("ii",$Uid,$Aid);
            $sql -> execute();
            $res = $sql -> get_result();
            $row = mysqli_fetch_array($res);
            $Wid = $row['wid'];
            if(mysqli_num_rows($res) == 0){
                echo '<p style="color:Red;font-size:120%">ERROR. You are not allowed for this action.</p>';
            }
            else{
                //start the transaction
                $query  = "BEGIN WORK;";
                $res = mysqli_query($con, $query);
                if(!$res){
                    echo '<p style="color:Red;font-size:120%">Server error. Please try again later.</p>';
                }
                else{
                    $sql = $con -> prepare("INSERT INTO Wmembers(wid, uid)
                                            VALUES (?,?)");
                    $sql -> bind_param("ii",$Wid,$Uid);
                    $sql -> execute();
                    if(!$res){
                        //something went wrong, display the error
                        echo '<p style="color:Red;font-size:120%">Server error. Please try again later.</p>';
                        $sql = "ROLLBACK;";
                        $res = mysqli_query($con, $sql);
                    }
                    else{
                        //delete this invitation after inserting user to Wmembers table
                        $sql = $con -> prepare("DELETE FROM Winvites
                                            WHERE uid = ? AND aid = ?");
                        $sql -> bind_param("ii",$Uid,$Aid);
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
                            echo '<p style="font-size:120%">Invitation accepted.</p>';
                            echo '<p style="font-size:120%"><a href="workspace.php?id='.$Wid.'">Go to</a> this workspace.</p>';
                        }
                    }
                }
            }
        }
        elseif($KEY == 'C'){
            //'C' is for channel
            //retrieve cid
            $Cid = htmlspecialchars($_GET['id']); 
            //first check if the invitation message exists in the database
            $sql = $con -> prepare("SELECT * 
                                FROM Cinvites
                                WHERE uid = ? AND cid =?");
            $sql -> bind_param("ii",$Uid,$Cid);
            $sql -> execute();
            $res = $sql -> get_result();
            $row = mysqli_fetch_array($res);
            if(mysqli_num_rows($res) == 0){
                echo '<p style="color:Red;font-size:120%">ERROR. You are not allowed for this action.</p>';
            }
            else{
                //start the transaction
                $query  = "BEGIN WORK;";
                $res = mysqli_query($con, $query);
                if(!$res){
                    echo '<p style="color:Red;font-size:120%">Server error. Please try again later.</p>';
                }
                else{
                    $sql = $con -> prepare("INSERT INTO Cmembers(cid, uid)
                                            VALUES (?,?)");
                    $sql -> bind_param("ii",$Cid,$Uid);
                    $sql -> execute();
                    if(!$res){
                        //something went wrong, display the error
                        echo '<p style="color:Red;font-size:120%">Server error. Please try again later.</p>';
                        $sql = "ROLLBACK;";
                        $res = mysqli_query($con, $sql);
                    }
                    else{
                        //delete this invitation after inserting user to Cmembers table
                        $sql = $con -> prepare("DELETE FROM Cinvites
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
                            $sql = "COMMIT;";
                            $res = mysqli_query($con, $sql);
                            echo '<p style="font-size:120%">Invitation accepted.</p>';
                            echo '<p style="font-size:120%"><a href="channel.php?id='.$Cid.'">Go to</a> this channel.</p>';
                        }
                    }
                }
            }
        }
        else{
            echo '<p style="color:Red;font-size:120%">UNKNOWN ERROR. Please try again later.</p>';
        }
    }
}

include 'footer.php';
?>