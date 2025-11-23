<?php
session_start();
session_destroy();
header("Location: ACT2_HTML.SERDAN.php");
exit();
?>