<?php
session_start();
?>

<html>
<body>
    <?php
        if (isset($_POST['city']) && isset($_SESSION['page1']))
        {
            #Do calculation here. Store in $_SESSION.
            $_SESSION['page2']="2";

            $_SESSION['city']=$_POST['city'];
            header('Location: NoRefreshResults.php');
	}
        else
        {
            header('Location: NoRefreshForm.php');

        }
    ?>
</body>
</html>
