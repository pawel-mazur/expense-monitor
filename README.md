Monitor Wydatków
========================

Aplikacja przeznaczona do monitorowania domowego budżetu, 
której zadaniem jest proste importowanie danych oraz wizualizacja
poniesionych kosztów z uwzględnieniem podziału na poszczególne
kategorie oraz porównanie ich z dostępnymi przychodami.

## Uruchomienie

Do uruchomienia aplikacji należy posłużyć się przykładową konfiguracją dla docker-compose:

```
version: '3'

services:

    web:
        image: pakumaz/spending-monitor
        restart: always
        ports:
            - 80:80
        links:
            - database

    database:
        image: postgres
        restart: always
        environment:
            POSTGRES_USER: spendingmonitor
            POSTGRES_PASSWORD: spendingmonitor
```

## Budowanie/Aktualizowanie środowiska

Poniższe polecenia należy uruchomić wewnątrz kontenera web.

Aby zbudować nowo skonfigurowane środowisko należy wykonać polecenie `make init`.

Do aktualizacji środowiska został skonfigurowany skrypt `make update`.
