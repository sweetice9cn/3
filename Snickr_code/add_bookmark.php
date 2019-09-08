<?php

include 'connect.php';
include 'header1.php';

if(!isset($_SESSION['uid'])){
    //the user is not signed in
    echo 'Sorry, you have to be <a href="signin.php">signed in</a> to add a bookmark.';
}
else{
    $Uid = $_SESSION['uid'];
    $which = htmlspecialchars($_GET['url']);
    if($which == 'workspace'){
        $Wid = htmlspecialchars($_GET['id']);
        $Wname = htmlspecialchars($_GET['name']);
        $url = 'workspace.php?id='.$Wid;
        $des = 'workspace: '.$Wname;
        //first detect if this workspace already in this user's bookmark
        $sql = $con -> prepare("SELECT * 
                                FROM Bookmarks
                                WHERE uid=? AND url=?");
        $sql -> bind_param("is",$Uid,$url);
        $sql -> execute();
        $res = $sql -> get_result();
        if(!$sql){
            echo '<p style="color:Red;font-size:120%">Server error. Please try again later.</p>';
        }
        else{
            if(mysqli_num_rows($res) == 0){
                //this is the first time this user tries to bookmark this page
                $sql = $con -> prepare("INSERT INTO Bookmarks(uid, url, bdescription, btime)
                                        VALUES(?,?,?,now())");
                $sql -> bind_param("iss",$Uid,$url,$des);
                $sql -> execute();
                if(!$sql){
                    echo '<p style="color:Red;font-size:120%">Server error. Please try again later.</p>';
                }
                else{
                    echo '<p style="font-size:120%">Successfully add this workspace to your personal bookmarks.</p><br>';
                    echo '<a href="workspace.php?id='.$Wid.'">Click me</a> to go back to previous page.<br>';
                }
            }
            else{
                echo '<p style="color:Red;font-size:120%">This page is already in your bookmark.<br>';
                echo '<a href="workspace.php?id='.$Wid.'">Go back to the last page.</a></p>';
            }
        }
    }
    elseif($which == 'channel'){
        $Cid = htmlspecialchars($_GET['id']);
        $Cname = htmlspecialchars($_GET['name']);
        $url = 'channel.php?id='.$Cid;
        $des = 'channel: '.$Cname;
        //first detect if this channel already in this user's bookmark
        $sql = $con -> prepare("SELECT * 
                                FROM Bookmarks
                                WHERE uid=? AND url=?");
        $sql -> bind_param("is",$Uid,$url);
        $sql -> execute();
        $res = $sql -> get_result();
        if(!$sql){
            echo '<p style="color:Red;font-size:120%">Server error. Please try again later.</p>';
        }
        else{
            if(mysqli_num_rows($res) == 0){
                //this is the first time this user tries to bookmark this page
                $sql = $con -> prepare("INSERT INTO Bookmarks(uid, url, bdescription, btime)
                                        VALUES(?,?,?,now())");
                $sql -> bind_param("iss",$Uid,$url,$des);
                $sql -> execute();
                if(!$sql){
                    echo '<p style="color:Red;font-size:120%">Server error. Please try again later.</p>';
                }
                else{
                    echo '<p style="font-size:120%">Successfully add this channel to your personal bookmarks.</p><br>';
                    echo '<a href="channel.php?id='.$Cid.'">Click me</a> to go back to previous page.<br>';
                }
            }
            else{
                echo '<p style="color:Red;font-size:120%">This page is already in your bookmark.<br>';
                echo '<a href="channel.php?id='.$Cid.'">Go back to the last page.</a></p>';
            }
        }
    }
    elseif($which == 'search'){
        $query = htmlspecialchars($_GET['query']);
        $url = 'search.php?query='.$query;
        $des = 'search results for: '.$query;
        //first detect if this result page already in this user's bookmark
        $sql = $con -> prepare("SELECT * 
                                FROM Bookmarks
                                WHERE uid=? AND url=?");
        $sql -> bind_param("is",$Uid,$url);
        $sql -> execute();
        $res = $sql -> get_result();
        if(!$sql){
            echo '<p style="color:Red;font-size:120%">Server error. Please try again later.</p>';
        }
        else{
            if(mysqli_num_rows($res) == 0){
                //this is the first time this user tries to bookmark this page
                $sql = $con -> prepare("INSERT INTO Bookmarks(uid, url, bdescription, btime)
                                        VALUES(?,?,?,now())");
                $sql -> bind_param("iss",$Uid,$url,$des);
                $sql -> execute();
                if(!$sql){
                    echo '<p style="color:Red;font-size:120%">Server error. Please try again later.</p>';
                }
                else{
                    echo '<p style="font-size:120%">Successfully add this search result to your personal bookmarks.</p><br>';
                    echo '<a href="search.php?query='.$query.'">Click me</a> to go back to previous page.<br>';
                }
            }
            else{
                echo '<p style="color:Red;font-size:120%">This page is already in your bookmark.<br>';
                echo '<a href="search.php?query='.$query.'">Go back to the last page.</a></p>';
            }
        }
    }
    else{
        echo '<p style="color:Red;font-size:120%">UNKNOWN ERROR. Please try again later.</p>';
    }
}

?>