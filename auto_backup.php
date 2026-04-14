<?php
include('db.php');

$db = "kiharu_student_portal";
$file = "backups/auto_" . date("Y-m-d") . ".sql";

if (!is_dir("backups")) {
    mkdir("backups", 0777, true);
}

system("mysqldump -u root $db > $file");
?>
