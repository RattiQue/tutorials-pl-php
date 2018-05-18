<?php
$date = new \DateTime();

echo 'Aktualne wydanie to ' . ($date->format('n') + 1) . '/' . $date->format('Y');
