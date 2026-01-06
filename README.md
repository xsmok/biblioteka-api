# Instrukcja uruchomienia aplikacji

## Wymagania wstępne

- PHP 8.4+
- Composer
- Symfony CLI

## Kroki instalacji

### 1. Skopiuj plik konfiguracyjny środowiska

```bash
copy .env.example .env
```

### 2. Wygeneruj i ustaw klucz aplikacji

Wygeneruj klucz za pomocą polecenia:

```bash
php -r "echo bin2hex(random_bytes(16));"
```

Skopiuj wygenerowany klucz i ustaw go w pliku `.env` jako wartość `APP_SECRET`.

### 3. Zainstaluj zależności

```bash
composer install
```

### 4. Utwórz bazę danych

> **Uwaga:** Ten krok jest wymagany tylko jeśli nie używasz SQLite.

```bash
php bin/console doctrine:database:create
```

### 5. Uruchom migracje

```bash
php bin/console doctrine:migrations:migrate
```

### 6. Uruchom serwer deweloperski

```bash
symfony server:start
```

Aplikacja będzie dostępna pod adresem wyświetlonym w konsoli (domyślnie `https://127.0.0.1:8000`).
