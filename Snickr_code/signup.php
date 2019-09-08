<?php

include 'connect.php';
include 'header1.php';
 
echo '<h3>Sign up</h3>';
echo '<p>Please fill in this form to create an account.</p>';
echo '<form method="post" action="">
    Username ( Combine of letters and numbers. Maximum 50 characters.):<br> 
        <input type="text" name="uname" /> <br>
    Nickname ( Combine of letters and numbers. Maximum 50 characters.): <br> 
        <input type="text" name="unickname" /> <br>
    Password: <br> 
        <input type="password" name="upwd"> <br>
    Retype password again: <br> 
        <input type="password" name="upwd_check"> <br>
    E-mail: <br> 
        <input type="email" name="uemail"> <br>
    <input type="submit" value="SUBMIT" /> <br>
    </form>';

if($_SERVER['REQUEST_METHOD'] == 'POST'){

    $errors = array(); //store the input errors in an array
    $Username = htmlspecialchars($_POST['uname']);
    $Nickname = htmlspecialchars($_POST['unickname']);
    $Upwd = htmlspecialchars($_POST['upwd']);
    $Upwd_check = htmlspecialchars($_POST['upwd_check']);
    $Email = htmlspecialchars($_POST['uemail']);
    if(!empty($Username)){
        if(!ctype_alnum($Username)){
            //check if all characters are alphanumeric
            $errors[] = 'The username can only contain letters and digits.';
        }
        if(strlen($Username) > 50){
            //check the length of user's input
            $errors[] = 'The username cannot be longer than 50 characters.';
        }
    }
    else{
        $errors[] = 'The username field must not be empty.';
    }
    
    //check if this username is already taken
    $sql_check = $con -> prepare("SELECT uname FROM Users WHERE uname=?");
    $sql_check -> bind_param("s",$Username);
    $sql_check -> execute();
    $usernamecheck = $sql_check -> get_result();

    if(mysqli_num_rows($usernamecheck) >= 1){
       $errors[] = 'Username ' . $Username . ' is already taken.';
    }

    //username is avaliable, then check for other fields
    if(!empty($Nickname)){
        if(!ctype_alnum($Nickname)){
            //check if all characters are alphanumeric
            $errors[] = 'The nickname can only contain letters and digits.';
        }
        if(strlen($Nickname) > 50){
            //check the length of user's input
            $errors[] = 'The nickname cannot be longer than 50 characters.';
        }
    }
    else{
        $errors[] = 'The nickname field must not be empty.';
    }    
    //check if this nickname is already taken
    $sql_check2 = $con -> prepare("SELECT unickname FROM Users WHERE unickname=?");
    $sql_check2 -> bind_param("s",$Nickname);
    $sql_check2 -> execute();
    $nikenamecheck = $sql_check2 -> get_result();   
    if(mysqli_num_rows($nikenamecheck) >= 1){
       echo $errors[] = 'Nickname ' . $Nickname . ' is already taken.';

    }
    if(!empty($Upwd)){
        if(strlen($Upwd) < 6){
            //check the length of user's input
            $errors[] = 'Password must be longer than 6 characters.';
        }
        if($Upwd != $Upwd_check){
            $errors[] = 'The two passwords did not match.';
        }
    }
    else{
        $errors[] = 'The password field must not be empty.';
    }
    
    if(empty($Email)){
        $errors[] = 'Email field must not be empty.';
    }

    if(!empty($errors)) {   
        //output errors in a list if error array is not empty
        echo '<p style="color:Red;font-size:120%">*Hate to say it, but you did somethnig wrong:</p>';
        echo '<ul style="color:Red;">';
        foreach($errors as $key => $value){
            echo '<li>' . $value . '</li>';
        }
        echo '</ul>';
    }
    else{
        //Users fill this form without errors.
        //Using the password_hash() function to hash passwords.
        $Password_encrypted = password_hash($Upwd, PASSWORD_DEFAULT) ;
        $sql = $con -> prepare("INSERT INTO Users(uname, unickname, upwd ,uemail)
                VALUES(?,?,?,?)");
        $sql -> bind_param("ssss",$Username,$Nickname,$Password_encrypted,$Email);
        $sql -> execute();
        if($sql){
            echo 'Successfully registered. Now redirecting you to sign in page.';
            header("refresh:1.5; url = signin.php" ); //open signin page after 1.5 sec
            exit();
        }
        else{
            //Could not able to execute $sql.
            echo '<p style="color:Red;">Oops...Something went wrong. Please try again later.</p><br>';
            }
    } 
}
 
include 'footer.php';
?>