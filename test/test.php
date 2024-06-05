<?php //6)) ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>table</title>
</head>
<body>
    <table>
        <thead>
            <tr>
            <?php for($i=1;$i<=10;$i++){?>
                <th>
                <?php echo $i ?>
                </th>
                <?php }?>
            </tr>
        </thead>
        <tbody>
        <?php for($i=1;$i<=10;$i++){?>
            <tr>
                <?php for($j=1;$j<=10;$j++){  ?>
                <td><?php echo $i * $j ?></td>
                <?php }?>

            </tr>

            <?php }?>
        </tbody>
    </table>



</body>
</html>
</html>




<?php
//1)problem 1 erasoft php
// $sum=0;
// $x=rand(0,5);
// $y=rand(6,10);
// for($i=$x;$i<=$y;$i++){
//     if($i%2!=0){
//         $sum+=$i;
//     }
//     else{
//         continue;
//     }
// }
// echo $x ."\n";
// echo $y ."\n";
// echo $sum;


//2)
// declare(strict_types=1);
// function revstring(string $string){
//     $reversed="";
// for($i=strlen($string)-1;$i>=0;$i--){
//   $reversed.=$string[$i];
// }
// return $reversed;

// }
// $string1="alahly";
// echo revstring($string1);


//3)
// for($i=0;$i<10;$i++){
//     for($j=0;$j<=$i;$j++){
//         echo "*  ";
//     }
//     echo "\n";
//     }

//4)
// $arr=[-101,1,4,51,61,7,-120,4,2,11,-2,-9,50,243,12,2,-100];
// $minNum=$arr[0];
// for($i=0;$i<count($arr);$i++){
//     if($arr[$i]<$minNum){
//         $minNum=$arr[$i];
//     }
// }
// echo $minNum;

//5))
// for($i=0;$i<20;$i++){
//     if($i<10){
//         for($j=0;$j<=$i;$j++){
//             echo "*  ";
//         }
//         echo "\n";
//         }
//     if($i>=10){
//         for($j=19;$j>=$i;$j--){
//             echo "*  ";
//         }
//         echo "\n";

//     }

//     }
    
