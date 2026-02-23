<?php

echo "Hello, World with echo!<br />";
print "Hello, World with print!<br /><br />";

$days = 288;
$message = "Все возвращаются на работу!";

# 1 способ — конкатенация (через точку)
echo "Количество дней: " . $days . "<br />";

# 2 способ — двойные кавычки (подстановка переменной)
echo "Сообщение: $message<br />";

# 3 способ — вместе
echo "Через $days дней: " . $message . "<br />";