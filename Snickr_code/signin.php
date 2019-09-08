<?php

include 'connect.php';
include 'header1.php';
echo '<h3>Sign in</h3>';
 
//check if the user is already signed in
if(isset($_SESSION['uid'])){
    echo 'You are already signed in, you can <a href="signout.php">sign out</a> if you want.<br>';
}
else{
    echo '<form method="post" action="">
        Username: <input type="text" name="uname" />
        Password: <input type="password" name="upwd">
        <input type="submit" value="Sign in" />
        </form>';
    if($_SERVER['REQUEST_METHOD'] == 'POST'){
        $Username = htmlspecialchars($_POST['uname']);
        $Password = htmlspecialchars($_POST['upwd']);
        $errors = array(); 
         
        if(!empty($Username)){
            if(!ctype_alnum($Username)){
                //check if all characters are alphanumeric
                $errors[] = 'Invalid username.';
            }
        }
        else{
                $errors[] = 'The username field must not be empty.';
        }

        if(empty($Password)){
            $errors[] = 'The password field must not be empty.';
        }
        
        if(!empty($errors)) {   
            //output errors in a list if error array is not empty
            echo '<ul style="color:Red;">';
            foreach($errors as $key => $value){
                echo '<li>' . $value . '</li>';
            }
            echo '</ul>';
        }

        else{
            //Users fill this form without errors.
            $sql = $con -> prepare("SELECT * FROM Users WHERE uname = ?");
            $sql -> bind_param("s",$Username);
            $sql -> execute();
            $res = $sql -> get_result();

            if($sql){  
                if(mysqli_num_rows($res) == 1){
                    //username exists
                    $row = mysqli_fetch_array($res);
                    $Password_match = password_verify($Password, $row['upwd']);

                    if($Password_match){
                    //password is correct
                    $_SESSION['uid']    = $row['uid'];
                    $_SESSION['uname']  = $row['uname'];
                    $_SESSION['unickname'] = $row['unickname'];
                    $_SESSION['uemail'] = $row['uemail'];
                     
                    echo '<p style="font-size:120%">Welcome back, ' . $_SESSION['uname'] . '. Now proceed you to the homepage.</p><br>';
                    header("refresh:1.5; url = index.php" ); //open home page after 1.5 sec
                    exit();
                    }
                    else{
                        echo '<p style="color:Red;">Your password is incorrect.</p><br>';
                    }
                }
                else{
                    //username does not exist in the database
                    echo '<p style="color:Red;">Username does not exist.</p><br>';
                }   
            }
            else{
                //Could not able to execute query   .
                echo '<p style="color:Red;">Oops...Something went wrong. Please try again later.</p><br>';
            }
            
        }
    }
}
 
include 'footer.php';
?>