<?php

date_default_timezone_set("Europe/Berlin");

$db = mysqli_connect('database', 'christkind', 'pw', 'advent');

if (isset($_GET['delete'])) {

    mysqli_query($db, "delete from gift where gid=" . (int) $_GET['delete']);
    exit;
}

$today = (int) date('d');
$tomonth = (int) date('m');
$name = substr($_SERVER['REQUEST_URI'], 1);
$gifts = array();

if (isset($_SERVER['PHP_AUTH_USER']) && $name !== $_SERVER['PHP_AUTH_USER']) {
    $today = 24;
} else if ($tomonth !== 12) {
    $today = 0;
}

for ($i = 0; $i < 24; $i++) {
    $gifts[$i] = null;
}

$theme = null;
$found = false;
$res = mysqli_query($db, "SELECT DDay, DData, C.UName, C.UTheme, F.UName UNameFrom
FROM door
JOIN user C  ON C.UID=UID_TO
JOIN user F ON F.UID=UID_FROM
WHERE C.UName='" . addslashes($name) . "'");
while ($row = mysqli_fetch_assoc($res)) {
    $found = true;
    $theme = $row['UTheme'];
    $gifts[$row['DDay'] - 1] = $row;
}

if (!$found) {
    die("nothing in here");
}

$boxes = array();
if (empty($_SERVER['PHP_AUTH_USER']) || $name === $_SERVER['PHP_AUTH_USER']) {
    $res = mysqli_query($db, "SELECT GID, GDay, T.UName, F.UName UNameFrom, GData
FROM gift
JOIN user F ON UID_FROM=F.UID
JOIN user T ON UID_TO=T.UID
WHERE T.UName='" . addslashes($name) . "' AND GDay <= " . $today . ";");
    while ($row = mysqli_fetch_assoc($res)) {
        $boxes[] = $row;
    }
}

$gifts = array_slice($gifts, 0, $today);

require('template.html');

