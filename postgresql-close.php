<?php

// Очистка результата
if (isset($pgresult)) { pg_free_result($pgresult); }

// Закрытие соединения
pg_close($pglink);
