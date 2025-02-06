<?php

require 'vendor/autoload.php';

use Predis\Client;

$redis = new Client([
    'host' => 'redis',
    'port' => 6379,
    'database' => 2,
]);

$coasterId = 'test_coaster_' . uniqid();
$wagonId = 'wagon_' . uniqid();

// ✅ Tworzenie testowej kolejki
$redis->hmset("coasters:$coasterId", [
    'state' => 'idle',
    'godziny_od' => '00:00',
    'godziny_do' => '23:59',
    'next_run' => time(),
    'liczba_personelu' => 5,
    'liczba_klientow' => 10000,
    'dl_trasy' => 1800
]);

// ✅ Dodanie kolejki do globalnego zbioru kolejek
$redis->sadd("coasters:All", $coasterId);

// ✅ Tworzenie testowego wagonu
$redis->hmset("coasters:{$coasterId}:wagons:{$wagonId}", [
    'ilosc_miejsc' => 32,
    'predkosc_wagonu' => 1.2
]);

// ✅ Dodanie ID wagonu do zbioru wagonów danej kolejki
$redis->sadd("coasters:wagons:{$coasterId}", $wagonId);

// ✅ Dodanie ID kolejki do zbioru kolejek w stanie 'idle'
$redis->sadd("coasters:idle", $coasterId);

// 📌 Pobranie listy wagonów przypisanych do kolejki
$wagonList = $redis->smembers("coasters:wagons:{$coasterId}");

// 📌 Pobranie listy kolejek w stanie idle
$idleCoasters = $redis->smembers("coasters:idle");

// 📌 Pobranie wszystkich kolejek zapisanych w `coasters:All`
$allCoasters = $redis->smembers("coasters:All");

echo "🎢 Kolejka testowa dodana: {$coasterId}\n";
echo "🚃 Wagon testowy dodany: {$wagonId}\n";

// 📌 Wyświetlenie wagonów, które są przypisane do kolejki
if (!empty($wagonList)) {
    echo "🔍 Lista wagonów dla kolejki {$coasterId}: " . implode(', ', $wagonList) . "\n";
} else {
    echo "⚠ Brak wagonów w kolejce {$coasterId}!\n";
}

// 📌 Wyświetlenie listy kolejek w stanie idle
if (!empty($idleCoasters)) {
    echo "🔄 Kolejki w stanie idle: " . implode(', ', $idleCoasters) . "\n";
} else {
    echo "⚠ Brak kolejek w stanie idle!\n";
}

// 📌 Wyświetlenie listy wszystkich kolejek
if (!empty($allCoasters)) {
    echo "📋 Lista wszystkich kolejek: " . implode(', ', $allCoasters) . "\n";
} else {
    echo "⚠ Brak zarejestrowanych kolejek!\n";
}
