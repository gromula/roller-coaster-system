# 🎢 Roller Coaster System 🚀

System zarządzania kolejkami górskimi z wykorzystaniem **PHP 8.2**, **CodeIgniter 4**, **Redis**, **Docker** i **PHPUnit**.

---

## 👋 Wymagania
- **Docker** 🐋
- **Docker Compose** 📚
- **PHP 8.2+**
- **Composer**
- **Redis**

---

## 🚀 Szybki Start


### 2️⃣ Uruchom Dockera
```bash
docker-compose up --build -d
```

### 3️⃣ Sprawdź logi
```bash
docker logs php-fpm -f
```

### 4️⃣ Wejdź do kontenera PHP
```bash
docker exec -it php-fpm sh
```

---

## 🛠 Najważniejsze komendy


---

### 📌 Testy
#### Uruchomienie wszystkich testów
```bash
docker exec -it php-fpm vendor/bin/phpunit
```
#### Uruchomienie testów dla konkretnej klasy
```bash
docker exec -it php-fpm vendor/bin/phpunit --filter CoasterServiceTest
```
#### Generowanie raportu pokrycia kodu testami
```bash
docker exec -it php-fpm vendor/bin/phpunit --coverage-html /var/www/html/tests/coverage
```
➡ **Otwórz raport w przeglądarce**:
```bash
open app/tests/coverage/index.html
```

---

### 📌 Redis
#### Wejście do Redis CLI
```bash
docker exec -it redis redis-cli
```
#### Podgląd bazy Redis
```bash
SELECT 2 # Wybór testowej bazy
KEYS *   # Wyświetlenie wszystkich kluczy
HGETALL coasters:test_coaster_xxx # Sprawdzenie danych kolejki
```
#### Wyczyszczenie całej bazy Redis
```bash
FLUSHALL
```

---

## 💁️ Struktura katalogów
```
├── app/
│   ├── Controllers/         # Kontrolery API
│   ├── Services/            # Logika biznesowa
│   ├── DTO/                 # Data Transfer Objects
│   ├── Utils/               # Pomocnicze klasy
│   ├── Tests/               # Testy jednostkowe i e2e
│   ├── Config/              # Konfiguracja aplikacji
│   ├── Views/               # Widoki (opcjonalnie)
│   └── Routes.php           # Definicja endpointów API
├── docker-compose.yml       # Konfiguracja Docker
├── Dockerfile               # Definicja obrazu PHP
├── phpunit.xml.dist         # Konfiguracja PHPUnit
├── .env                     # Plik środowiskowy
└── README.md                # Ten plik 🤯
```

---

## 🌟 Przydatne porady
- Logi aplikacji znajdują się w `storage/logs/`
- Wersja deweloperska rejestruje wszystkie logi, wersja produkcyjna tylko `warning` i `error`
- Upewnij się, że Redis działa przed testami: `docker exec -it redis redis-cli ping`

---

## 🎯 Dalsze kroki
✅ Pokryj kod **100% testami**  
✅ Ulepsz **monitoring i logowanie**  
✅ Wdrożenie **wersji produkcyjnej**  

💡 **Masz pytania?** Pisz na Discorda lub GitHub Issues! 🚀

---

🔥 **To teraz możemy iść, robisz sztos robotę!** 🔥

