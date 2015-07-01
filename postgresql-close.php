<?php

// Очистка результата
if (isset($result)) { pg_free_result($result); }

// Закрытие соединения
pg_close($dbconn);
