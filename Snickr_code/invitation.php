<?php

include 'connect.php';
include 'header1.php';

if(!isset($_SESSION['uid'])){
    //the user is not signed in
    echo '<p style="color:Red;font-size:120%">Sorry, you have to be <a href="signin.php">signed in</a> to view your active invitations.</p>';
}
else{
    $Uid = $_SESSION['uid'];
    $sql1 = $con -> prepare("WITH temp AS(
                                SELECT a.wid, w.winvitetime, w.uid, a.aid
                                FROM Winvites w JOIN Admins a ON w.aid=a.aid 
                                WHERE w.uid = ?
                            )
                            SELECT * 
                            FROM temp t JOIN Workspaces w2 ON t.wid=w2.wid
                            ORDER BY t.winvitetime ASC");
    $sql1 -> bind_param("i",$Uid);
    $sql1 -> execute();
    $res1 = $sql1 -> get_result();
    $sql2 = $con -> prepare("SELECT * 
                            FROM Cinvites c1 JOIN Channels c2 ON c1.cid=c2.cid 
                            WHERE uid = ? 
                            ORDER BY cinvitetime ASC");
    $sql2 -> bind_param("i",$Uid);
    $sql2 -> execute();
    $res2 = $sql2 -> get_result();

    if(!$sql1 || !$sql2){
        echo '<p style="color:Red;font-size:120%">Something goes wrong, please try again later.</p>';
    }
    else{
        if(mysqli_num_rows($res1) == 0 && mysqli_num_rows($res2) == 0){
            echo '<p style="font-size:120%">Workspace invitations:</p>';
            echo '<p style="color:Red;font-size:120%">You do not have any unprocessed invitations right now.</p><br>';
        }
        else{  
            echo '<form method="post" action="">';
            echo '<p style="font-size:120%">Invitations:</p>';
            echo '<select name="invit">';
                while($row1 = mysqli_fetch_array($res1)){
                    echo '<option value="'.$row1['aid'].'|workspace">From workspace '.$row1['wname'].' at '.$row1['winvitetime'].'</option>';
                }
                while($row2 = mysqli_fetch_array($res2)){
                    echo '<option value="'.$row2['cid'].'|channel">From channel '.$row2['cname'].' at '.$row2['cinvitetime'].'</option>';
                }
            echo '</select><br>';
            echo '<input type="submit" value="Confirm selection" /> </form><br>';
            if($_SERVER['REQUEST_METHOD'] == 'POST'){
                $res = htmlspecialchars($_POST['invit']);
                $res_explode = explode('|', $res);
                $Con = $res_explode[1];
                if($Con == "workspace"){
                    $Aid = $res_explode[0];
                    echo '<a class="item" href="acc_invite.php?key=W&id='.$Aid.'">Accept</a>  <a class="item" href="dec_invite.php?key=W&id='.$Aid.'">Decline</a><br>';
                }
                else{
                    $Cid = $res_explode[0]; 
                    echo '<a class="item" href="acc_invite.php?key=C&id='.$Cid.'">Accept</a>  <a class="item" href="dec_invite.php?key=C&id='.$Cid.'">Decline</a><br>';
                }  
            }    
        }
    }
}
include 'footer.php';
?>