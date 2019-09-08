<?php

include 'connect.php';
include 'header1.php';

if(!isset($_SESSION['uid'])){
    //the user is not signed in
    echo '<p style="color:Red;font-size:120%">Sorry, you have to be <a href="signin.php">signed in</a> to view your bookmark pages.</p>';
}
else{
    $Uid = $_SESSION['uid'];
    $sql = $con -> prepare("SELECT * FROM Bookmarks WHERE uid = ? ORDER BY btime ASC");
    $sql -> bind_param("i",$Uid);
    $sql -> execute();
    $res = $sql -> get_result();

    if(!$sql){
        echo 'Something goes wrong, please try again later.';
    }
    else{
        if(mysqli_num_rows($res) == 0){
            echo '<p style="color:Red;font-size:120%">You do not have any bookmarks yet.</p>';
        }
        else{  
            echo 'Your bookmarks:<br>';
            echo '<ul style="color:Green;">';
            while($row = mysqli_fetch_array($res)){
                echo '<li><a href="'.$row['url'].'">' . $row['bdescription']  . '</a></li>';
            }
            echo '</ul>';
        }
    }
}
include 'footer.php';
?>