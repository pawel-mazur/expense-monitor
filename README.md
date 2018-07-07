Monitor Wydatków
========================

[![Build Status](https://travis-ci.org/pawel-mazur/spending-monitor.png)](https://travis-ci.org/pawel-mazur/spending-monitor)

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
        volumes:
            - imports:/var/www/html/web/var/imports
            - logs:/var/www/html/web/var/logs

    database:
        image: postgres
        restart: always
        environment:
            POSTGRES_USER: spendingmonitor
            POSTGRES_PASSWORD: spendingmonitor
        volumes:
            - database:/var/lib/postgresql/data

volumes:
    database:
    imports:
    logs:

```

## Budowanie/Aktualizowanie środowiska

Poniższe polecenia należy uruchomić wewnątrz kontenera web.

Aby zbudować nowo skonfigurowane środowisko należy wykonać polecenie `make init`.

Do aktualizacji środowiska został skonfigurowany skrypt `make update`.
