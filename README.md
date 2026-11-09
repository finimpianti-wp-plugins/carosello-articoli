# Carosello Articoli

Carosello di articoli filtrati per tag e/o categoria, da inserire nelle pagine con lo shortcode `[carosello_articoli]`; impostazioni in Bacheca → Carosello Articoli. Con Elementor Page Translator: tag di lingua e testo del link per lingua.

## Nessun articolo da mostrare

Se nessun articolo corrisponde ai filtri, al posto del carosello compare un **biglietto "nessuna fiera in calendario"** (impostazioni → *Nessun articolo da mostrare*): scritta sul calendario, occhiello, titolo, testo, pulsante con link, altezza compatta o uguale al carosello. Con Elementor Page Translator ogni testo ha un campo per lingua (vuoto = testo predefinito tradotto) e il link porta alla traduzione della pagina, se esiste. Disattivandolo non viene mostrato nulla (gli amministratori vedono comunque quale filtro non ha trovato articoli).

## Installazione

1. Scarica lo zip dell'ultima versione da **Releases** (a destra in questa pagina): `carosello-articoli-<versione>.zip`.
2. In WordPress: *Plugin → Aggiungi nuovo → Carica plugin* → scegli lo zip → *Installa* (o *Sostituisci quella attuale*).

## Aggiornamenti

Il plugin controlla da solo le release di questo repository: quando ne esce una nuova compare in
*Bacheca → Aggiornamenti* e in *Plugin*, e si installa con un clic. Impostazioni e dati restano nel database del sito.

## Nuove versioni (per chi sviluppa)

1. Aggiorna `Version:` nell'intestazione del file principale (e la costante della versione) e aggiungi le novità in `CHANGELOG.md`.
2. Carica le modifiche sul ramo `main`: se la versione è nuova, GitHub crea da solo etichetta, zip e release (`.github/workflows/release.yml`). Si può anche pubblicare una release a mano con etichetta `v<versione>`: lo zip viene allegato automaticamente.

Per creare lo zip in locale: `bash bin/build-zip.sh` (risultato in `dist/`).

## Licenza

GPL-2.0-or-later. Include [Plugin Update Checker](https://github.com/YahnisElsts/plugin-update-checker) (MIT).
