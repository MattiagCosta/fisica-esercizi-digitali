# Casi.php

Questo script PHP fornisce due funzioni per salvare uno o tutti i casi che una traccia può dare dato il seme per generare i numeri random

## SalvaCaso

Salva un singolo caso sotto forma di riga di una tabella (i dati sono separati da \t e la riga termina con PHP_EOL). Vanno forniti i dati che si vogliono salvare tramite un dizionario e il percorso del file testuale su cui si vuole salvare la riga.

## SalvaTuttiICasi

Salva tutti i casi possibili dal seme 1 al seme 10000 sotto forma di una tabella. Vanno forniti lo script PHP contenente la funzione `Traccia` e il percorso del file su cui si vuole salvare la tabella. `Traccia` deve restituire un dizionario in cui, sotto la voce "dati", ci sia un dizionario con i dati del problema che si vogliono salvare. `SalvaTuttiICasi` tronca il file su cui si voglio salvare i dati

### Esempio di cosa deve restituire `Traccia`

```php
// Frammento di codice

$problema = array(
    "titolo" => $titolo,
    "testo" => "<div>" . $testo . "</div>",
    "soluzione" => $soluzione,
    "discussione" => "<div>" . $disc . "</div>",
    "dati" => array("h_M0" => $hM0, "d" => $d, "v_F0" => $vF0, "h_F0" => $hF0, "y" => $y, "delta" => $delta, "soluzione" => $soluzione)
    // "dati" è un dizionario con i dati del problema che si vogliono salvare
);
return $problema;
```

## Esempio

```php
// Frammento di codice

SalvaTuttiICasi("traccia.php", "CasiPossibili.txt");

```

## Avvertenze

Questo script deve essere usato per uno scopo teorico, non va inserito in un sito accessibile agli studenti.

Di norma, lo script `Traccia.php` dà un errore perché la funziona `Traccia` non è definita.

## Dettagli

Autori : MGC

FormattaNumero.php: v.1.0.0

Ultima datazione: 2025-04-25

Avvertenze: L'utente può utilizzare Casi.php come meglio crede
