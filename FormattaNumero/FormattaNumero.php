<?php

define("MODO_BASE", 0);
define("MODO_NOTAZIONE_SCIENTIFICA", 1);
define("MODO_CIFRE_SIGNIFICATIVE", 2);
define("MODO_LASCIA_5", 4);
define("MODO_5_PARI_DISPARI", 8);
define("MODO_5_NEGATIVO", 16);
define("MODO_VIRGOLA", 32);
define("MODO_E", 64);
define("MODO_X10", 128);
define("MODO_LATEX", 256);
define("MODO_FORZA_SEGNO", 512);
define("MODO_FORZA_SEGNO_ESPONENTE", 1024);
define("MODO_UMANIZZA", 2048);

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
function FormattaNumero(int|float|string $numero, int $modo, ?int $cifre_significative): string {
	// Raccolta dati e gestione dei casi particolari

	if(!is_numeric($numero)){
		throw new Exception("Il numero dato non è in un formato numerico accettabile");
	}

	if(is_float($numero) && is_nan($numero)){ // NAN
		return "NaN";
	}

	if(is_float($numero) && is_infinite($numero)){ // INF/-INF
		$inf = $modo & MODO_LATEX ? "\infty" : "Inf";
		if($modo & MODO_FORZA_SEGNO && $numero>0){ $inf = '+' . $inf; }
		if($numero<0){ $inf = '-' . $inf; }
		return  $inf;
	}

	$numero = (string)$numero;

	$punto = strpos($numero, '.');
	$E = strpos($numero, 'e');
	if($E===false){ $E = strpos($numero, 'E'); }

	$lunghezza = strlen($numero);
	if($punto!==false){ $lunghezza = $punto; }
	elseif($E!=false){ $lunghezza = $E; }

	$parte_intera = $lunghezza>0 ? substr($numero, 0, $lunghezza) : '0';
	$parte_decimale = $punto!==false ? substr($numero, $punto+1, ($E!==false ? $E-$punto-1 : strlen($numero)-$punto)) : null;
	$esponente = $E!==false ? substr($numero, $E+1, strlen($numero)-$E+1) : null;

	$segno = '+';
	if($parte_intera[0]=='-' || $parte_intera[0]=='+'){
		$segno = $parte_intera[0];
		$parte_intera = substr($parte_intera, 1);
	}

	if($parte_intera==''){ $parte_intera = '0'; }

	$segno_esponente = '+';
	if($esponente!==null && ($esponente[0]=='-' || $esponente[0]=='+')){
		$segno_esponente = $esponente[0];
		$esponente = substr($esponente, 1);
	}

	$semplifica_zeri = function ($numero, $verso = 0){
		if($numero==null){ return null;}
		$lunghezza = strlen($numero);
		if($verso==0){
			for($i=0; $i<$lunghezza; $i++){
				if($numero[$i]!='0'){ return substr($numero, $i); }
			}
		}
		else{
			for($i=$lunghezza-1; $i>=0; $i--){
				if($numero[$i]!='0'){ return substr($numero, 0, $i+1); }
			}
		}
		return '0';
	};

	$parte_intera = $semplifica_zeri($parte_intera);
	$parte_decimale = $semplifica_zeri($parte_decimale, 1);
	$esponente = $semplifica_zeri($esponente);

	$mantissa = $semplifica_zeri($parte_intera . ($parte_decimale!==null && $parte_decimale!='0' ? $parte_decimale : ''));

	if($mantissa=='0'){ // 0
		return '0';
	}

	$zeri = $parte_decimale!==null && $parte_intera=='0' ? strlen($parte_decimale) - strlen($semplifica_zeri($parte_decimale)) + 1 : 0;

	$esponente = strlen($parte_intera) - $zeri + ($esponente!==null ? (int)($segno_esponente.$esponente) : 0);

	if($esponente>0){ $segno_esponente = '+'; }
	elseif($esponente==0){ $segno_esponente = ''; }
	else{ $segno_esponente = '-'; }

	// Dalla raccolta dati si ricava (char) $segno, (string) $mantissa, (int) $esponente, (char) $segno_esponente
	// La mantissa è la parte decimale di 0.$mantissa (non 1.$mantissa come di solito è per i float)
	// Nonostante ci sia $segno_esponente, $esponente conserva il suo segno, al contrario di $mantissa

	// Formattazione

	// Al contrario della raccolta dati, si è voluto scrivere ripetizioni e si è voluto evitare complicazioni per rendere il codice più chiaro

	$numero_formattato = "";

	$separatore_decimale = '.';
	if($modo & MODO_VIRGOLA){
		$separatore_decimale = ',';
	}

	// Se $esponente dovesse venire modificato, $segno_esponente verrebbe ricalcolato successivamente (solo se dovesse essere usato)
	if($modo & MODO_NOTAZIONE_SCIENTIFICA){
		$numero_formattato = $mantissa;
		$esponente--;
	}
	elseif($modo & MODO_CIFRE_SIGNIFICATIVE){
		if($cifre_significative==null || $cifre_significative<1){
			throw new Exception("Il numero di cifre significative deve essere maggiore di 0 in modalità MODO_CIFRE_SIGNIFICATIVE");
		}

		if($cifre_significative>=strlen($mantissa)){
			$nuova_mantissa = $mantissa . str_repeat('0', $cifre_significative-strlen($mantissa));
		}
		else{

			if($modo & MODO_LASCIA_5 && $mantissa[$cifre_significative]=='5'){ $arrotonda = false; }
			elseif(
				($segno=='-' && !($modo & MODO_5_NEGATIVO))
				|| (
					$modo & MODO_5_PARI_DISPARI
					&& $mantissa[$cifre_significative]=='5'
					&& (int)($mantissa[$cifre_significative-1])%2==0
				)
			){ $arrotonda = (int)$mantissa[$cifre_significative]>5; }
			else{ $arrotonda = $arrotonda = (int)$mantissa[$cifre_significative]>=5; }

			$nuova_mantissa = $mantissa;

			if($arrotonda){
				for($i = $cifre_significative-1; $i>=-1; $i--){
					$cifra_da_controllare = $nuova_mantissa[$i];
					if($i==-1){
						$nuova_mantissa[0] = '1';
						$esponente++;
						break;
					}
					if($cifra_da_controllare!='9'){
						$nuova_mantissa[$i] = (string)((int)$nuova_mantissa[$i] + 1);
						break;
					}
					$nuova_mantissa[$i] = '0';
					$cifra_da_controllare = $nuova_mantissa[$i];
				}
			}

			if($modo & MODO_LASCIA_5 && $mantissa[$cifre_significative]=='5'){
				$nuova_mantissa = substr($nuova_mantissa, 0, $cifre_significative+1);
			}
			else{
				$nuova_mantissa = substr($nuova_mantissa, 0, $cifre_significative);
			}
		}

		$numero_formattato = $nuova_mantissa;
		$esponente--;
	}
	else{ // Formattazione standard
		$numero_formattato = $mantissa;
		$esponente--;
	}

	if(($modo & MODO_UMANIZZA && $esponente>=-2 && $esponente<=2) || !($modo & MODO_NOTAZIONE_SCIENTIFICA || $modo & MODO_CIFRE_SIGNIFICATIVE)){ // Notazione estesa
		if($esponente>=0){
			if($esponente>=strlen($numero_formattato)-1){
				$numero_formattato .= str_repeat('0', $esponente-strlen($numero_formattato)+1);
			}
			else{
				$numero_formattato = substr($numero_formattato, 0, $esponente+1) . $separatore_decimale . substr($numero_formattato, $esponente+1);
			}
		}
		else{
			$numero_formattato = '0' . $separatore_decimale . str_repeat('0', -$esponente-1) . $numero_formattato;
		}
	}
	else{ // Notazione esponenziale
		if(strlen($numero_formattato)>1){
			$numero_formattato = substr_replace($numero_formattato, $separatore_decimale, 1, 0);
		}

		if($esponente>0){ $segno_esponente = '+'; }
		elseif($esponente==0){ $segno_esponente = ''; }
		else{ $segno_esponente = '-'; }

		$esponente = (string)$esponente;

		if($modo & MODO_FORZA_SEGNO_ESPONENTE && $segno_esponente=='+'){
			$esponente = $segno_esponente . $esponente;
		}

		$separatore_esponente = 'e';
		$chiusura = '';
		if($modo & MODO_E){
			$separatore_esponente = 'E';
		}
		if($modo & MODO_X10){
			$separatore_esponente = "x10^(";
			$chiusura = ')';
		}
		if($modo & MODO_LATEX){
			$separatore_esponente = "\\cdot10^{";
			$chiusura = '}';
		}

		$numero_formattato .= $separatore_esponente . $esponente . $chiusura;
	}

	if(($modo & MODO_FORZA_SEGNO && $segno=='+') || $segno!='+'){
		$numero_formattato = $segno . $numero_formattato;
	}

	return $numero_formattato;
}

?>