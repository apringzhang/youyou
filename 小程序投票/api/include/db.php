<?php
$dbh = new PDO('mysql:host=192.168.9.112;port=3306;dbname=vote_mp', 'root', '123456');
$dbh->query("SET NAMES UTF8MB4");