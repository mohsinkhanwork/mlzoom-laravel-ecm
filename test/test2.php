<?php 
// for($i=0;$i<=100;$i++){
//     if($i%5==0&&$i%3==0){
//         echo $i."five and three <br>";
//     }
//     elseif($i%5==0){
//         echo $i . "five <br>";
//     }
//     elseif($i%3==0){
//         echo $i ."three <br>";
//     }
// }

// $array1=[1,2,3,4,5,6];
// function arrAdd($array,$index,$value){
//     if($index>=count($array)){
//         $array[$index]=$value;
//         $array_after_adding=$array;
//     }
//     else{

//     for($i=0;$i<count($array);$i++){
//         if($index!=$i){
//             if($index==-1){
//                $array_after_adding[$i+1]=$array[$i];
//             }
//             else{
//             $array_after_adding[$i]=$array[$i];}
//         }
//         elseif($index==$i){
//             $array_after_adding[$i]=$value;
//             $index=-1;
//             $i--;
//         }
//     }
// }
//     return $array_after_adding;
// }
// print_r( arrAdd($array1,5,7));


?>
<!doctype html>
<html lang="en">
  <head>
    <title>Title</title>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
  </head>
  <body>
    <?php $users=[["name"=>"mohamed" ,
    "user_id"=>1,
     "id"=>11,
     "title"=>"ka",
     "body"=>"kkkkkkkkkkkkk"
    ],
    ["name"=>"mahmoud" ,
    "user_id"=>2,
     "id"=>12,
     "title"=>"ma",
     "body"=>"mmmmmmmm"
    ],
    ["name"=>"zakaria" ,
    "user_id"=>3, 
    "id"=>13,
    "title"=>"za",
    "body"=>"zzzzzzzzzzz"
    ]
    ]
      ?>





  <table class="table">
  <thead class="table-dark"> 
    <tr>
      <th scope="col">id</th>
      <th scope="col">user_id</th>
      <th scope="col">name</th>
      <th scope="col">title</th>
      <th scope="col">body</th>
      <th scope="col">edit</th>
      <th scope="col">delete</th>

    </tr>
  </thead>
  <tbody>
    <?php foreach($users as $user) {?>

    <tr>
      <td><?php echo $user['id'] ?></td>
      <td><?php echo $user['user_id'] ?></td>
      <td><?php echo $user['name'] ?></td>
      <td><?php echo $user['title'] ?></td>
      <td><?php echo $user['body'] ?></td>
      <td><button type="submit" class="btn btn-primary">edit</button></td>
      <td><button type="submit" class="btn btn-primary">delete</button></td>


    </tr>
<?php } ?>
  </tbody>
</table>

      
    <!-- Optional JavaScript -->
    <!-- jQuery first, then Popper.js, then Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
  </body>
</html>
