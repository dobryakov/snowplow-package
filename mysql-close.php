<?php

if (isset($result)) { mysql_free_result($result); }

mysql_close($link);

