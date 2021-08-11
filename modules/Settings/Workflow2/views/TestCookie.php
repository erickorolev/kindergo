<?php

if($_GET['value'] == '1') {
    setcookie('stefanDebug', 1, 0, '/');
    setcookie('stefanError', 1, 0, '/');
} else {
    setcookie('stefanDebug', 1, time()-(60*60*24), '/');
    setcookie('stefanError', 1, time()-(60*60*24), '/');
}

