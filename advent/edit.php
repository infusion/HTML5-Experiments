<?php
$db = mysqli_connect('database', 'christkind', 'pw', 'advent');

if (!isset($_SERVER['PHP_AUTH_USER'])) {
    header('WWW-Authenticate: Basic realm="Advent Calendar"');
    header('HTTP/1.0 401 Unauthorized');
    exit;
}

$res = mysqli_query($db, "SELECT * FROM user WHERE UName='" . addslashes($_SERVER['PHP_AUTH_USER']) . "'");
$user = mysqli_fetch_assoc($res);

if (!isset($user['UID']) || $user['UPass'] !== md5($_SERVER['PHP_AUTH_PW'])) {
    header('WWW-Authenticate: Basic realm="Advent Calendar"');
    header('HTTP/1.0 401 Unauthorized');
    exit;
}
?><!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        <style>

            body {
                color: #292b2c;
                background:#efefef;
                line-height: 1.5;
                font-weight:400;
                font-family: -apple-system,system-ui,BlinkMacSystemFont,"Segoe UI",Roboto,"Helvetica Neue",Arial,sans-serif
            }

            a {
                padding:2px;
                text-decoration: none;
            }

            a:hover {
                text-decoration:underline
            }

        </style>

        <?php
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (isset($_POST['msg'])) {
                if (!empty($_POST['msg']))
                    mysqli_query($db, "INSERT INTO gift (GDay, UID_From, UID_to, GData) VALUES (" . (int) $_POST['day'] . ", " . $user['UID'] . ",  " . (int) $_POST['name'] . ", '" . addslashes($_POST['msg']) . "')");
            } else {
                $error = false;
                if ($_POST['type'] === 'image') {
                    $filename = '/' . rand(1000000, 9999999);

                    switch ($_FILES['cont']['type']) {
                        case 'image/svg+xml':
                            $filename .= '.svg';
                            break;
                        case 'image/jpeg':
                            $filename .= '.jpg';
                            break;
                        case 'image/png':
                            $filename .= '.png';
                            break;
                        case 'image/gif':
                            $filename .= '.gif';
                            break;
                        default:
                            echo "unknown image format";
                            $error = true;
                    }

                    move_uploaded_file($_FILES['cont']['tmp_name'], '/var/www/image' . $filename);

                    $_POST['cont'] = $filename;
                }
                if (!$error && !empty($_POST['cont']))
                    mysqli_query($db, "INSERT INTO door (DDay, UID_From, UID_To, DData) VALUES (" . (int) $_POST['day'] . ", " . $user['UID'] . ",  " . (int) $_POST['name'] . ", '" . addslashes($_POST['cont']) . "')");
            }
        }
        ?>

        <style>
            th {
                text-align:right
            }
            td a, td span {
                color: black;
            }
            td {
                width: 20px;
            }
        </style>
    </head>
    <body>

        <h2>Calendar Overview</h2>
        <?php
        $data = array();
        $userID = array();
        $res = mysqli_query($db, "SELECT A.UID, A.UName, DID, DDay, DData, UID_From, UID_To, B.UName UNameFrom FROM user A LEFT JOIN door ON UID_To=A.UID LEFT JOIN user B ON UID_From=B.UID ORDER BY UID ASC");
        while ($row = mysqli_fetch_assoc($res)) {
            $name = $row['UName'];
            if (!isset($data[$name])) {
                $data[$name] = array();
            }
            if (!empty($row['DDay']))
                $data[$name][$row['DDay']] = $row;
            $userID[$name] = $row['UID'];
        }
        $res->free();

        $msgs = array();
        $res = mysqli_query($db, "select UID_TO, GDay, COUNT(*) GCount from gift group by UID_TO, GDay");
        while ($row = mysqli_fetch_assoc($res)) {
            $msgs[$row['UID_TO'] . ';' . $row['GDay']] = $row['GCount'];
        }
        $res->free();



        echo '<table>';

        $tomonth = (int) date('m');
        $today = (int) date('d');

        foreach ($data as $name => $v) {
            $n = $user['UName'];

            if ($user['UName'] == $name) {
                continue;
            }

            echo '<tr><th>', $name, ':</th>';

            for ($i = 1; $i <= 24; $i++) {
                $new = 1;
                $data = "";
                $from = "";
                if (isset($v[$i])) {
                    $style = 'background:#0c0;color:white';
                    $new = 0;
                    $from = $v[$i]['UNameFrom'];
                    $data = $v[$i]['DData'];
                } else if ($tomonth === 12 && $today >= $i - 1) {
                    $style = 'background:#c00;color:white';
                } else {
                    $style = '';
                }
                $app = "";
                if (isset($msgs[$userID[$name] . ';' . $i])) {
                    $app .= '<sup style="color:orange">' . $msgs[$userID[$name] . ';' . $i] . '</sup>';
                }
                echo '<td><a href="#" style="', $style, '" onclick="set(', $i, ', \'', $name, '\',', $userID[$name], ',', $new, ',\'' . addslashes($data) . '\', \'', $from, '\')">', $i, $app, '</a></td>';
            }

            echo '</tr>';
        }
        echo '</table><div id="form"></div>';

        mysqli_close($db);
        ?>

        <script>

            function set(day, name, user, n, gift, from) {
              var form = document.getElementById('form');

              var str = '';
              if (n) {
                str += '<h2>Place a gift for ' + name + '</h2><form autocomplete="off" action="" method="post" enctype="multipart/form-data"><input type="hidden" name="name" value="' + user + '"><input type="hidden" name="day" value="' + day + '">';
                str += '<table>';
                str += '<tr><th>To: </th><td>' + name + ' @ ' + day + '.12.</td></tr>';
                str += '<tr><th>Content:</th><td><input type="text" id="cont" name="cont" value="" placeholder="http://"></td></tr>';
                str += '<tr><th>Type:</th><td><input onclick="setContType(\'file\')" type="radio" name="type" value="image"> Image <input type="radio" checked name="type" value="link" onclick="setContType(\'text\')">Link</td></tr>';
                str += '<tr><td></td><td><input type="submit" value="Save"></td></tr>';
                str += '</table></form>';
              } else {
                str += "<h2>" + name + " is well served by " + from + "</h2>";

                var back = "";
                var door = "";
                var click = "";

                if (gift[0] === '/') {
                  back = '/image' + gift;
                  click = "layer";
                } else {

                  var m = gift.match(/v=([a-z0-9_-]+)/i);
                  if (m !== null && m[1] !== undefined) {
                    back = 'https://i.ytimg.com/vi/' + m[1] + '/hqdefault.jpg';
                    click = gift;
                  } else {
                    back = 'https://thumbs.dreamstime.com/b/hand-cursor-clicking-click-here-button-d-render-52415076.jpg';
                    click = gift;
                  }

                  var m = gift.match(/\.(gif|png|jpg)/);
                  if (m !== null) {
                    back = gift;
                    click = "layer";
                  }
                }

                if (click === 'layer') {
                  str += name + " will get: <br>" + '<a href="' + back + '"><img src="' + back + '" width="95"></a>';
                } else {
                  str += name + " will get: <br>" + '<a href="' + click + '" target="_blank"><img src="' + back + '" width="95"></a>';
                }
              }
              str += '<h2>Send a Greeting</h2><form autocomplete="off" action="" method="post"><input type="hidden" name="name" value="' + user + '"><input type="hidden" name="day" value="' + day + '">';
              str += '<table>';
              str += '<tr><th>To: </th><td>' + name + ' @ ' + day + '.12.</td></tr>';
              str += '<tr><th>Message:</th><td><input type="text" name="msg" value="" style="width:400px"></td></tr>';
              str += '<tr><td></td><td><input type="submit" value="Send"></td></tr>';
              str += '</table></form>';

              form.innerHTML = str;
            }

            function setContType(type) {
              document.getElementById('cont').setAttribute('type', type);
            }

        </script>
    </body>
</html>
