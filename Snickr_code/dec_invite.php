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
                //delete this invitation from table
                $sql = $con -> prepare("DELETE FROM Winvites
                                    WHERE uid = ? AND aid = ?");
                $sql -> bind_param("ii",$Uid,$Aid);
                $sql -> execute();
                if(!$sql){
                    //something went wrong, display the error
                    echo '<p style="color:Red;font-size:120%">Server error. Please try again later.</p>';
                }
                else{                                        
                    echo '<p style="font-size:120%">Invitation declined.</p>';
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
                //delete this invitation from table
                $sql = $con -> prepare("DELETE FROM Cinvites
                                    WHERE uid = ? AND cid = ?");
                $sql -> bind_param("ii",$Uid,$Cid);
                $sql -> execute();
                if(!$sql){
                    //something went wrong, display the error
                    echo '<p style="color:Red;font-size:120%">Server error. Please try again later.</p>';
                }
                else{
                    echo '<p style="font-size:120%">Invitation declined.</p>';
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