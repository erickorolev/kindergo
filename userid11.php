<?php
echo "<div align=center>";
echo "=======================================================================<br><br>";
echo "USER: ";
echo `/usr/bin/whoami`;
echo "<br><br>=======================================================================<br><br>";
echo " UID, GID, groups: ";
system (id);
echo "<br><br>=======================================================================<br><br>";
echo `/usr/sbin/httpd.itk -l`;
echo "<br><br>=======================================================================<br><br>";
echo `/usr/sbin/apachectl -v`;
echo "<br><br>=======================================================================<br><br>";
echo "<dr><dr>";
echo "</div>";
phpinfo();
?>

