<?php
session_start();
$_SESSION['page1']=1;
?>

<html>
<body>
    <form name="test" action="NoRefreshCalculate.php" method="post">
        City name: <input type="text" name="city"><br>
        <input type="submit" name="submit" value="Search" />
    </form>
</body>
</html>
