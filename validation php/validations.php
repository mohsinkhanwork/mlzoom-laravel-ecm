<?php 
session_start();
if($_SERVER['REQUEST_METHOD']=="POST"){
$nameErrors=[];
$emailErrors=[];
$user_name = trim( filter_input(INPUT_POST,"user_name",FILTER_SANITIZE_STRING));
$_SESSION['user_name']=$user_name;
if(emptyInput($user_name)){
    $nameErrors[] = "name is required";
}
elseif(minInput($user_name,3)){
    $nameErrors[]="name must be more than 3 characters";
}
elseif(maxInput($user_name,50)){
    $nameErrors[]="name must be less than 50 characters";
}

$email =filter_input(INPUT_POST,"email",FILTER_SANITIZE_EMAIL);
echo $email;
$_SESSION['email']=$email;
if(emptyEmail($email)){
      $emailErrors[]="email must not be empty";
}
elseif(validateEmail($email)){
    $emailErrors[]="enter valid email";
}

$_SESSION['nameErrors']=$nameErrors;
$_SESSION['emailErrors']=$emailErrors;
if(!empty($_SESSION['nameErrors'])||!empty($_SESSION['emailErrors'])){
    header("location:form.php");
}
else{
    header("location:profile.php");
}
}
else{
    echo"no";
}
function emptyInput($string){
    if(empty($string)){
        return true;
    }
    else {
        return false;
    }

}
function minInput($string,$min){
    if(strlen($string)<=$min){
        return true;
    }
    else{
        return false;
    }
}
function maxInput($string,$max){
    if (strlen($string) > $max) {
        return true;
    }
    else return false;
}
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


