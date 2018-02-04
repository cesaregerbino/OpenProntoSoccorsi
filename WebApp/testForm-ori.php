<html>
 <head>
  <title>
    Test
  </title>
 </head>
 <body>
  <form name="test" action="<?php echo htmlentities($_SERVER['PHP_SELF']); ?>" method="post">
   <input type="text" name="name"><br>
   <input type="submit" name="submit" value="Submit Form"><br>
  </form>

  <?php
   if(isset($_POST['submit']))
    {
     $name = $_POST['name'];
     echo "User Has submitted the form and entered this name : <b> $name </b>";
     echo "<br>You can use the following form again to enter a new name.";
     echo ("<script>  window.location.href='http://YourPatch.com';</script>");
    }
  ?>
 </body>
</html>
