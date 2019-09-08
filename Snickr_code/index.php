<?php

include 'connect.php';
include 'header1.php';

//retrieve contents from workspaces
$sql = "SELECT * FROM Workspaces"; 
$res = mysqli_query($con, $sql);

if(!$res){
    echo 'Something goes wrong, please try again later.';
}
else{
    if(mysqli_num_rows($res) == 0){
        echo 'Nothing posted.';
    }
    else{
        echo '<table border="1">
              <tr>
                <th>Workspaces</th>
                <th>Description</th>
                <th>Creator</th>
                <th>Time created</th>
              </tr>'; 
        while($row = mysqli_fetch_array($res)){
            //get the creator's nickname
            $Wcreatorid = $row['wcreatorid'];
            $sql1 = "SELECT * FROM Users WHERE uid = '$Wcreatorid'";
            $res1 = mysqli_query($con, $sql1);
            $Creator = mysqli_fetch_array($res1)['unickname'];
            echo '<tr>';
                echo '<td>';
                    echo '<h3><a href="workspace.php?id='.$row['wid'].'">'.$row['wname'].'</a></h3>';
                echo '</td>';
                echo '<td>';
                    echo $row['wdescription'];
                echo '</td>';
                echo '<td>';
                    echo $Creator;
                echo '</td>';
                echo '<td>';
                    echo $row['wtime'];
                echo '</td>';
            echo '</tr>';
        }
        echo'</table>';
    }
}
include 'footer.php';
?>