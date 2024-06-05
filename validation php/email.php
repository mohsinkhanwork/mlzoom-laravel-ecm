<?php
session_start();
    $errors=[];
$email =filter_input(INPUT_POST,"email",FILTER_SANITIZE_EMAIL);
echo $email;
$_SESSION['email']=$email;
if(emptyEmail($email)){
      $errors['email']="email must not be empty";
}
elseif(validateEmail($email)){
    $errors['email']="enter valid email";
}

$_SESSION['errors["email"]']=$errors['email'];
function emptyEmail($input){
    if(empty($input)){
        return true;
    }
    else return false;
}
function validateEmail($input){
    if(filter_var($input,FILTER_VALIDATE_EMAIL)){
        return false;
    }
    else return true;
}

?>
