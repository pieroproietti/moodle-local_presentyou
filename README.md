# README

Sto scrivendo una coppia di plugin gemelli, denominati: `local_presentyou` e [local_modalpresentyou](https://github.com/pieroproietti/moodle-local_modalpresentyou), entrambi di tipo `local` per Moodle 5.+, usando Visual Studio Code e l'intelligenza artificiale.

Mi occorre che all'utente, ad ogni nuovo login, sia presentato un form con due campi `department` e `position` come selectbox, e due bottoni: `Confirm` e `Logout`.

La differenza tra i due è che mentre `local_presentyou` visualizza un semplice form, `local_modalpresentyou` farà apparire una finestra modale.

I campi sono stati creati come `Campi personalizzati` opzione menu.

Tutti gli utenti, incluso `admin`, devono essere automaticamente reindirizzati sul form e confermare i campi `department` e `position`. In caso di nessuna conferma, si esegue automaticamente il logout.

## Struttura
```ascii
presentyou/
├── classes/
│   ├── form/
│   │   └── complete_profile_form.php
│   ├── privacy/
│   │   └── provider.php
│   └── observer.php
├── db/
│   └── events.php
├── lang/
│   ├── en/
│   │   └── local_presentyou.php
│   └── it/
│       └── local_presentyou.php
├── README.md
├── SUNTO.md
├── complete_profile.php
├── index.php
├── sunto.py
├── test.php
└── version.php
```
## Campi personalizzati
Creare utilizzando l'interfaaccia web si `Site administration->Users->Users profile fiels` i campi: `department` e `position`.


# git
Potete liberamente clonare questa repository con il comando: 

`git clone https://github.com/pieroproietti/moodle-local_presentyou`

Per un uso più professionale se ne consiglia, preventivamente, il [fork](https://github.com/pieroproietti/moodle-local_presentyou/fork).

In tal modo avrete una copia personale e potrete modificare liberamente, effeturare dell `REQUEST PULL` sulla versione originale ed avere la possibilità di aggiornare all'originale.

# AI 
Utilizzo [gemini 2.5 flash](https://gemini.google.com/) per analisi e codice.

Per fornire il contesto, utilizzare il file [SUNTO.md](./SUNTO.md) che fornisce all'AI tutto il codice php del plugin. 

SUNTO.md viene generato automaticamente dallo script `sunto.py` presente in questa repository. Digirare: `./sunto.py .` dall'interno della repository stessa.

# moodledev (live-iso)
* [moodledev](https://github.com/pieroproietti/moodledev)
