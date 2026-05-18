<?php
session_start();
session_destroy();
header('Location: index.php?nointro=1');
exit;
