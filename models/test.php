<?php

$password = '12';
$containsLetter  = preg_match('/[a-zA-Z]/',$password);
echo $containsLetter;
?>