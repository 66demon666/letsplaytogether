<?php
if ($_GET['code']) {
    echo "Код получен: " . $_GET["code"];
}
else {
    echo "Код не получен";
}