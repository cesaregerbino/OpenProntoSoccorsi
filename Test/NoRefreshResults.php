<?php
session_start();
?>

<html>
<body>
  <form action="NoRefreshCalculate.php" method="post">
    City name: <input type="text" name="city"><br>
    <input type="submit" name="submit" value="Search" />
  </form>

    <?php
        if (isset($_SESSION['page2']))
        {
            # echo results
            if (isset($_SESSION['city'])) {
              echo "City name = ".$_SESSION['city'];
            }

            session_destroy();
            session_start();
            $_SESSION['page1']=1;
        }
        else
        {
            header('Location: NoRefreshForm.php');
        }
    ?>
</body>
</html>
