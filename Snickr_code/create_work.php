<?php

include 'connect.php';
include 'header1.php';

echo '<h2>Create a Workspace</h2>';
if(!isset($_SESSION['uid'])){
    //the user is not signed in
    echo 'Sorry, you have to be <a href="signin.php">signed in</a> to create a workspace.';
}
else{
    echo '<form method="post" action=" ">
        Workspace name:<br> 
            <input type="text" name="wname" /><br>
        Workspace description:<br> 
            <textarea rows="8" cols="50" name="wdescription"></textarea><br>
        <input type="submit" value="Create" />
     	</form>';

    if($_SERVER['REQUEST_METHOD'] == 'POST'){
        //first check if the subject name field is empty
        if(empty($_POST['wname'])){
            echo '<p style="color:Red;font-size:120%">The Workspace name field must not be empty.</p>';
        }
        else{
            //start the transaction
            $query  = "BEGIN WORK;";
            $res = mysqli_query($con, $query);
            if(!$res){
                echo '<p style="color:Red;font-size:120%">Server error. Please try again later.</p>';
            }
            else{
            	$Work_name = htmlspecialchars($_POST['wname']);
                $Uid = $_SESSION['uid'];
            	$Work_des = htmlspecialchars($_POST['wdescription']);
                $sql = $con -> prepare("INSERT INTO Workspaces(wname, wtime, wcreatorid, wdescription)
                                        VALUES(?, now(), ?, ?)");
                $sql -> bind_param("sis",$Work_name,$Uid,$Work_des);
                $sql -> execute();
                if(!$sql){
                    //Could not able to execute $sql.
                    echo '<p style="font-size:120%">Server error. Please try later.</p>';
                    $sql = "ROLLBACK;";
                    $res = mysqli_query($con, $sql);
                }
                else{
                    //retrieve the newly created wid from database
                    //insert uid, wid into Admin and Wmember table
                    $Wid = mysqli_insert_id($con);
                    $sql3 = $con -> prepare("INSERT INTO Admins(uid, wid, atime)
                                            VALUES(?,?,now())");
                    $sql3 -> bind_param("ii",$Uid,$Wid);
                    $sql3 -> execute();
                    $sql4 = $con -> prepare("INSERT INTO Wmembers(uid, wid)
                                            VALUES(?,?)");
                    $sql4 -> bind_param("ii",$Uid,$Wid);
                    $sql4 -> execute();
                    if(!$sql3 || !$sql4){
                        //something went wrong, display the error
                        echo '<p style="color:Red;font-size:120%">Server error. Please try again later.</p>';
                        $sql = "ROLLBACK;";
                        $res = mysqli_query($con, $sql);
                    }
                    else{
                        $sql = "COMMIT;";
                        $res = mysqli_query($con, $sql);
                        echo '<p style="font-size:120%">You have successfully created a new workspace. <a href="workspace.php?id='.$Wid.'">Click me </a> to go to your newly created workspace.</p>';
                    }
                }
            }
        }
    }
}
include 'footer.php';
?>