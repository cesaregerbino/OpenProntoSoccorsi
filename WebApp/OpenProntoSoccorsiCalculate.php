<?php
 # ***************************************************************************************************
 # *** Open Pronto Soccorsi - Web Mapping
 # *** Description:
 # ***
 # ***        Note:
 # ***
 # ***
 # ***      Author: Cesare Gerbino
 # ***        Code: https://github.com/cesaregerbino/OpenProntoSoccorsi
 # ***     License: MIT (https://opensource.org/licenses/MIT)
 # ***************************************************************************************************

 session_start();
?>

<html>
<head>
  <meta charset='utf-8' />
<head>
<body>
    <?php
        if (isset($_POST['comune']) && isset($_SESSION['page1']))
        {
            #Do calculation here. Store in $_SESSION.
            $_SESSION['page2']="2";

            $_SESSION['comune']=$_POST['comune'];
            $_SESSION['dist']=$_POST['dist'];

            header('Location: OpenProntoSoccorsiResults.php');
	}
        else
        {
            header('Location: OpenProntoSoccorsiForm.php');

        }
    ?>
</body>
</html>
