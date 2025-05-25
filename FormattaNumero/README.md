# FormattaNumero.php

Questo script PHP fornisce una funzione per formattare un numero in base a diverse modalità specificate.

## Sommario

- [Formattazione del numero](#formattazione-del-numero)
  - [Costanti di Formattazione](#costanti-di-formattazione)
  - [Gruppi e gerarchia dei modi](#gruppi-e-gerarchia-dei-modi)
  - [Formattazioni particolari](#formattazioni-particolari)
- [Utilizzo di FormattaNumero](#utilizzo-di-formattanumero)
  - [$numero](#numero)
  - [$modo](#modo)
  - [$cifre_significative](#cifre_significative)
  - [Valore restituito](#valore-restituito)
- [Esempi di utilizzo](#esempi-di-utilizzo)
- [Eccezioni](#eccezioni)
- [Note](#note)
- [Avvertenze](#avvertenze)
- [Dettagli](#dettagli)

## Formattazione del numero

La funzione `FormattaNumero` accetta un numero e lo formatta in base a diverse modalità (o modi) di formattazione, specificate dall'utente. Le modalità sono definite da costanti che possono essere combinate tra loro utilizzando l'operatore BITWISE OR (`|`).

La formattazione standard è quella che la funzione applica se l'utente non specifica la modalità. Anche se la modalità viene specificata, essa può non cambiare certi aspetti della formattazione standard.

La formattazione standard è qualcosa di simile a `n.dek`, dove `n` è la parte intera del numero, `d` è la parte decimale, `.` è il separatore decimale, `k` è l'esponente ed `e` è il separatore dell'esponente. Non sempre la notazione esponenziale (`ek`) viene mostrata nella la formattazione standard.

### Costanti di Formattazione

| Costante | Descrizione | Requisiti |
|---|---|---|
| `MODO_BASE` | Alias per la formattazione standard. | - |
| `MODO_NOTAZIONE_SCIENTIFICA` | Rappresenta il numero in notazione scientifica. | - |
| `MODO_CIFRE_SIGNIFICATIVE` | Formatta il numero conoscendo le sue cifre significative. | Cifre significative valide (maggiori di 0). |
| `MODO_LASCIA_5` | Non arrotonda la cifra incerta se la cifra successiva è 5. | La cifra successiva a quella incerta deve essere 5. |
| `MODO_5_PARI_DISPARI` | Arrotonda la cifra incerta secondo la regola del pari/dispari se la cifra successiva è 5 (per difetto se la cifra incerta è pari, per eccesso se è dispari). | La cifra successiva a quella incerta deve essere 5. |
| `MODO_5_NEGATIVO` | Arrotonda la cifra incerta per difetto se la successiva è 5 e il numero è negativo. | La cifra successiva a quella incerta deve essere 5. |
| `MODO_VIRGOLA` | Utilizza la virgola come separatore decimale. | - |
| `MODO_E`| Usa la lettera "E" maiuscola come separatore dell’esponente. | - |
| `MODO_X10` | Usa "x10^(k)" come notazione esponenziale, dove k è l'esponente. | - |
| `MODO_LATEX` | Usa "\\cdot10^{k}" (in LaTex) come notazione esponenziale, dove k è l'esponente. | - |
| `MODO_FORZA_SEGNO` | Forza la visualizzazione del segno del numero. | Il numero deve essere maggiore di 0. |
| `MODO_FORZA_SEGNO_ESPONENTE` | Forza la visualizzazione del segno dell’esponente. | L'esponente deve essere maggiore di 0. |
| `MODO_UMANIZZA` | Umanizza la rappresentazione del numero, evitando di scrivere la notazione esponenziale se l'esponente è compreso tra -2 e 2. | L'esponente deve essere compreso tra -2 e 2. |

### Gruppi e gerarchia dei modi

I modi sono divisi in diversi gruppi, ognuno con una specifica funzione.

I modi di formattazione hanno una gerarchia che determina quale modalità prevale in caso di conflitto. Modi di gruppi diversi non interferiscono tra di loro, quindi ogni gruppo ha una gerarchia propria.

Nell'elenco sottostante, si utilizza `a > b` (dove `a` e `b` sono modalità) per indicare la priorità che `a` ha su `b`, `a[ b ]` per indicare che `b` è valida solo se `a` è specificata, `!a[ b ]` per indicare che `b` è valida solo se `a` non è specificata, e `a | b` per indicare che `a` e `b` sono equivalenti. Si suppone che i requisiti per le modalità siano soddisfatti, come per esempio che il numero di cifre significative sia maggiore di 0 quando si utilizza `MODO_CIFRE_SIGNIFICATIVE`.

- MODO_NOTAZIONE_SCIENTIFICA > MODO_CIFRE_SIGNIFICATIVE > MODO_BASE | formattazione standard
- MODO_CIFRE_SIGNIFICATIVE[ MODO_LASCIA_5 > MODO_5_PARI_DISPARI | MODO_5_NEGATIVO > formattazione standard]
- MODO_VIRGOLA > formattazione standard
- !MODO_UMANIZZA[ MODO_LATEX > MODO_X10 > MODO_E > formattazione standard ]
- MODO_FORZA_SEGNO > formattazione standard
- !MODO_UMANIZZA[ MODO_FORZA_SEGNO_ESPONENTE > formattazione standard ]
- MODO_UMANIZZA > formattazione standard
- !MODO_NOTAZIONE_SCIENTIFICA[ !MODO_CIFRE_SIGNIFICATIVE[ formattazione standard ] ]

### Formattazioni particolari

Ci sono tre casi per cui la formattazione non viene applicata in modo regolare:

- Se il numero dato è `0`, allora viene restituita la stringa (meglio dire carattere) `'0'`, senza particolari formattazioni.
- Se il numero dato è `NAN`, viene restituita la stringa `"NaN"`.
- Se il numero dato è `INF` (o -`INF`), allora possono essere applicate le modalità `MODO_LATEX` e `MODO_FORZA_SEGNO` per formattare ulteriormente il risultato, altrimenti viene restituita la stringa `"Inf"` (`"-Inf"` se negativo").

| Modalità | Formattazione di INF (-INF) |
|---|---|
| Formattazione standard | "Inf" ("-Inf") |
| `MODO_LATEX` | "\infty" ("-\infty") |
| `MODO_FORZA_SEGNO` | "+Inf" ("-Inf") |

## Utilizzo di FormattaNumero

```php
/**
 * Formatta un numero in base alle modalità specificate
 * @param int|float|string $numero Il numero da formattare
 * @param int $modo Il modo di formattazione - utilizzare l'operatore BITWISE OR ( | ) per specificare più modalità. Esempio: MODO_NOTAZIONE_SCIENTIFICA | MODO_UMANIZZA
 * @param ?int $cifreSignificative Il numero di cifre significative da mostrare, se si utilizza MODO_CIFRE_SIGNIFICATIVE
 * @return string Il numero formattato
 * @throws Exception Se il numero dato non è in un formato numerico accettabile
 * @throws Exception Se il numero di cifre significative è minore di 1 in modalità MODO_CIFRE_SIGNIFICATIVE
 * @version 1.0.0
 * @author MGC
 */
function FormattaNumero(int|float|string $numero, int $modo, ?int $cifre_significative): string {}
```

`FormattaNumero` richiede come attributi $numero e $modo. $cifre_significative è da specificare solo in modalità CIFRE_SIGNIFICATIVE.

### $numero

Il numero da formattare. Deve essere un valore in formato numerico, può essere di tipo `int`, `float` o `string` (altri tipi potrebbero essere instabili).

Il numero può essere anche un float particolare come `INF` e `NAN`.

### $modo

Il modo di formattazione da applicare al numero. Può essere una combinazione delle costanti definite sopra, utilizzando l'operatore BITWISE OR (`|`).

### $cifre_significative

Da specificare solo in modalità `CIFRE_SIGNIFICATIVE`. Deve essere un numero intero maggiore di 0 che indica quante cifre significative specificare. Se non specificato o se minore di 1, la funzione lancerà un'eccezione.

### Valore restituito

La funzione restituisce una stringa contenente il numero formattato secondo le opzioni specificate.

## Esempi di utilizzo

```php
// Frammento di codice

echo FormattaNumero(12345.6789e2, MODO_BASE); // "1234567.89"

echo FormattaNumero(12345.6789e2, MODO_CIFRE_SIGNIFICATIVE | MODO_FORZA_SEGNO, 4); // "+1.235e6"

$modo = MODO_CIFRE_SIGNIFICATIVE | MODO_FORZA_SEGNO | MODO_FORZA_SEGNO_ESPONENTE | MODO_LASCIA_5 | MODO_VIRGOLA | MODO_LATEX;

echo FormattaNumero(12345.6789e2, $modo, 4); // "+1,2345\\cdot10^{+6}"
```

```php
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>test</title>
    <!-- Latex -->
    <script src="https://cdn.jsdelivr.net/npm/mathjax@3/es5/tex-mml-chtml.js"></script>
</head>
<body>
    <?php
        include_once "FormattaNumero.php";

        $a = 12345.6789e2;

        echo "Numero da formattare: $a<br>";

        try{
            $modo =  MODO_CIFRE_SIGNIFICATIVE | MODO_FORZA_SEGNO | MODO_FORZA_SEGNO_ESPONENTE | MODO_LASCIA_5 | MODO_VIRGOLA | MODO_LATEX;
            $result = FormattaNumero($a, $modo, 4);
            echo "\($result\)<br>"; // "+1,2345\\cdot10^{+6}"
        }
        catch(Exception $e){
            echo "Preso: $e <br>";
        }
    ?>
</body>
</html>
```

## Eccezioni

La funzione può lanciare un'eccezione se:

- Il numero dato non è in un formato numerico accettabile.
- Il numero di cifre significative specificato in modalità `MODO_CIFRE_SIGNIFICATIVE` è minore di 1.

## Note

- Si consiglia di utilizzare l'operatore BITWISE OR (`|`) per combinare più opzioni di formattazione.
- La funzione arrotonda il numero in base alle cifre significative specificate.

Per ulteriori dettagli, consultare il codice sorgente in `FormattaNumero.php`.

## Avvertenze

I file precedenti sono e devono essere compatibili con quelli successivi, ma quelli successivi potrebbero non essere compatibili con quelli precedenti.

## Dettagli

Autori: MGC

FormattaNumero.php: v.1.0.0

Ultima datazione: 2025-05-25

Avvertenze sull'utilizzo: L'utente può utilizzare FormattaNumero.php come meglio crede
