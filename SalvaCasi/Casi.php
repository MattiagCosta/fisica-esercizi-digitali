<?php

// ATTENZIONE: Questo script ha un errore di default perché la funzione Traccia non sarà definita finché non verrà inclusa una traccia specifica chiamando la funzione SalvaTuttiICasi

/**
 * Salva un particolare caso generato da una traccia su file
 * @param int $caso Numero del caso
 * @param array $dati Dati del caso
 * @param string $percorso_file Percorso del file dove salvare i dati
 * @return void
 * @version 1.0.0
 * @author MGC
 */
function SalvaCaso(int $caso, array $dati, string $percorso_file): void {
	$file = fopen($percorso_file, "a");
	fwrite($file, (string)$caso);
	foreach ($dati as $key => $value) {
		if($value==null) $value = "null";
		fwrite($file, "\t".(string)$value);
	}
	fwrite($file, PHP_EOL);
	fclose($file);
}

/**
 * Salva tutti i casi di test generati da una traccia su un file
 * @param string $traccia Percorso del file della traccia
 * @param string $percorso_file Percorso del file dove salvare i casi
 * @return void
 * @version 1.0.0
 * @author MGC
 */
function SalvaTuttiICasi(string $traccia, string $percorso_file): void {
	require_once($traccia);
	file_put_contents($percorso_file, "");
	for($s = 1; $s<=10000; $s++){
		mt_srand($s);
		$problema = Traccia();
		SalvaCaso($s, $problema["dati"], $percorso_file);
	}
	
}

?>