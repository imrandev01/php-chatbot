<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>my PhP page</title>
</head>
<style>
    *{
        margin :0 ;
        padding : 0;
        box-sizing: border-box;

    }
    .container{
        max-width: 80%;
        background-color : rgb(228,195,195);
        margin: auto;
        padding : 23px;
    }
</style>
<body>
    <div class= "container">
        <h1>let's learn about PHP </h1>
        <p> your party status is here/</p>
        <?php
        $age = 34;
        if ($age>18){
            echo "you can go to the party";
        
        }
        else if ($age == 7){
            echo "you are 7 year old";
        }
        else{
            echo "you can not go to the party";
        }
        ?>
    </div>
</body>
</html>