<?php
if(isset($_FILES['Select_file'])){
    echo"<pre>";
    print_r($_FILES);
    echo "</pre>";
    $file_name=$_FILES['Select_file']['name'];
    $file_size=$_FILES['Select_file']['size'];
    $file_type=$_FILES['Select_file']['type'];
    $file_tmp=$_FILES['Select_file']['tmp_name'];
    if(move_uploaded_file($file_tmp,"uploaded/". $file_name)){
        echo "uploaded successfully";
    }
    else{
        echo "errors";
    }
   
}
?>
<html>
    <body>
        <form action="" method="POST" enctype="multipart/form-data">
            <input type="file" name="Select_file" /><br>
            <input type="submit"/>
</form>
</body>
</html>