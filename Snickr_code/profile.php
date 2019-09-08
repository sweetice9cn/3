<?php

include 'connect.php';
include 'header1.php';

if(!isset($_SESSION['uid'])){
    //the user is not signed in
    echo '<p style="color:Red;font-size:120%">Sorry, you have to be <a href="signin.php">signed in</a> to view your profile.</p>';
}
else{
    $Uid = $_SESSION['uid'];
    $sql = $con -> prepare("SELECT * FROM Users WHERE uid = ?");
    $sql -> bind_param("i",$Uid);
    $sql -> execute();
    $res = $sql -> get_result();

    if(!$sql){
        echo 'Something goes wrong, please try again later.';
    }
    else{
        if(mysqli_num_rows($res) != 1){
            echo 'Error: User not exist.';
        }
        else{
            $row = mysqli_fetch_array($res);    
            echo '<table style="width:10%" border="1">
                <tr>
                    <th>UserID:</th>
                    <td>';
                echo $row['uid'];
                echo '</td>
                </tr>
                <tr>
                    <th>Username:</th>
                    <td>';
                echo $row['uname'];
                echo '</td>
                </tr>
                <tr>

                    <th>Nickname:</th>
                    <td>';
                echo $row['unickname'];
                echo '</td>
                </tr>
                <tr>
                    <th>Email:</th>
                    <td>';
                echo $row['uemail'];
                echo' </td>
                </tr><br>';
            echo '</table>'; 
            echo '<p>Incorrect information? <a href="correctinfo.php">Correct it</a>.</p>';
        }
    }
}
include 'footer.php';
?>