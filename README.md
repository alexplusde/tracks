# T-Racks für REDAXO 5 - Keep Modules and Templates on track with T-Racks

Hilfsklasse zur Installation und Synchronisation von Templates und Modulen, die mit Add-ons mitgeliefert werden. Add-ons, die dieses System verwenden: Events, School, plus_bs5.

Auch für eigene Addons einsetzbar!

## Verwendung im eigenen Addon

### Tracks-Addon als Abhängigkeit in der `package.yml` des eigenen Addons definieren

```yml
requires:
    php:
        version: '>=8.1'
    redaxo: ^5.18
    tracks: ^4.0
```

### 2. In der install.php des eigenen Addons Tracks verwenden

```php
use Tracks\Tracks;

if(rex_addon::exists('tracks')) {
    Tracks::forceBackup('meinaddon'); // Sichert standardmäßig Module und Templates
    Tracks::updateModule('meinaddon'); // Synchronisiert Module
    Tracks::updateTemplate('meinaddon'); // Synchronisiert Templates
}
```

### 3. In der boot.php des eigenen Addons während der Entwicklung Tracks verwenden

Dazu müssen die zu synchroniserenden Module und Templates einen Prefix im Schlüssel haben, z.B. `meinprefix.%`. Da diese mit dem SQL-LIKE-Operator abgefragt werden, können beliebige Zeichen vor und nach dem `%` stehen.

```php
if (rex::isBackend() && rex::isDebugMode() && rex_config::get('meinaddon', 'dev')) {
    Helper::writeModule('meinaddon', 'meinprefix.%'); // Schreibt Module
    Helper::writeTemplate('meinaddon', 'meinprefix.%'); // Schreibt Templates
}
```

> Tipp: Es empfiehlt sich, neben dem Debug-Modus auch einen eigenen Konfigurationsparameter für die Entwicklung zu verwenden, um die Synchronisation bei Bedarf zu aktivieren und zu deaktivieren.

## Addons, die T-Racks verwenden

- [Events](https://github.com/alexplusde/events/)
- [School](https://github.com/alexplusde/school/)
- [plus_bs5](https://github.com/alexplusde/plus_bs5/)

## Lizenz

MIT-Lizenz, siehe [LICENSE.md](https://github.com/alexplusde/tracks/blob/main/LICENSE.md)

## Autoren

**Alexander Walther**  
<http://www.alexplus.de>  
<https://github.com/alexplusde>  

**Projekt-Lead**  
[Alexander Walther](https://github.com/alexplusde)
