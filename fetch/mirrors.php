<?php

# token required, since this should only get accessed from rsync.php.net
if (!isset($token) || md5($token) != "19a3ec370affe2d899755f005e5cd90e")
  die("token not correct.");

// Connect and generate the list from the DB
if (@mysql_pconnect("localhost","nobody","")) {
  if (@mysql_select_db("php3")) {
    $res = @mysql_query("SELECT mirrors.*,country.name AS cname,(DATE_SUB(NOW(),INTERVAL 3 DAY) < mirrors.lastchecked) AS up,(DATE_SUB(NOW(),INTERVAL 7 DAY) < mirrors.lastupdated) AS current FROM mirrors LEFT JOIN country ON mirrors.cc = country.id ORDER BY country.name,hostname");
    if ($res) {
      echo "<?php\n\$MIRRORS = array(\n";
      while ($row = @mysql_fetch_array($res)) {
        if (!strstr("http:",$row[hostname])) { $row[hostname]="http://$row[hostname]/"; }
        // Set inactive mirrors to type 2 so they won't show up in the drop-down.
        if (!$row["active"] || !$row["current"] || !$row["up"]) { $row["mirrortype"] = 2; }
        echo "  \"$row[hostname]\" => array(\"$row[cc]\",\"$row[providername]\",$row[has_stats],\"$row[providerurl]\",$row[mirrortype],$row[has_search],\"$row[lang]\"),\n";
      }
      echo '  0 => array("xx", "Unknown", 0, "/", 2, 0, "en")', "\n";
      echo ");\n";
      echo "?>\n";
    }
  }
}
