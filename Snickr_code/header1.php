<!DOCTYPE HTML>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta name="description" content="A web-based collaborstion system" />
    <meta name="keywords" content="Snickr, forum" />
    <title>PHP-MySQL forum</title>
    <link rel="stylesheet" href="css/mainstyle.css" type="text/css">
</head>
<body>
<h1 id="title">Snickr</h1>
    <div id="wrapper">
    <div id="menu">
        <div id="items">
            <a class="item" href="index.php">Home</a> -
            <a class="item" href="create_work.php">Create a new workspace</a> -
            <a class="item" href="create_channel.php">Create a new channel</a> -
            <a class="item" href="post_message.php">Post a new message</a> -
            <a class="item" href="bookmark.php">Bookmark</a><br>
        </div>

        <div id="menuopts">
            <div id="searchbar">
                <form action="search.php" method="GET" name="searchbox:">
                    Search key words for a message: <input type="text" name="query" />
                    <input type="submit" value="Search" />
                </form>
            </div>

            <?php
                session_start();
                echo '<div id="userbar">';
                    if(isset($_SESSION['uid'])){
                        echo '<a class="item" href="invitation.php">Notifications</a> - Hello <a href="profile.php">' .$_SESSION['uname']. '</a>. Not you? <a class="item" href="signout.php">Sign out</a>';
                    }
                    else{
                        echo '<a class="item" href="signin.php">Sign in</a> or <a class="item" href="signup.php">Create an account</a>';
                    }
            echo '</div>';
            ?>
        </div>

        <div id="content">