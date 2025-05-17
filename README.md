# README

Sto scrivendo un plugin denominato `local_presentyou` di tipo `local` per Moodle 5.+, usando Visual Studio Code con l'estensione Moodle Plugin Development e l'intelligenza artificiale.

Mi occorre che all'utente, ad ogni nuovo login, sia presentato un form con due campi `department` e `position` come selectbox, e due bottoni: `Confirm` e `Logout`.

I campi sono stati creati come `Campi personalizzati` opzione menu.

Tutti gli utenti, incluso `admin`, devono essere automaticamente reindirizzati sul form e confermare i campi `department` e `position`. In caso di nessuna conferma, si esegue automaticamente il logout.

## Struttura
```ascii
presentyou/
├── classes/
│   ├── form/
│   │   └── complete_profile_form.php
│   └── privacy/
│       └── provider.php
├── lang/
│   ├── en/
│   │   └── local_presentyou.php
│   └── it/
│       └── local_presentyou.php
├── complete_profile.php
├── index.php
├── middleware.php
├── sunto.py
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

Per fornire il contesto, utilizzare il file [SUNTO.md](./SUNTO.md) che fornisce all'AI tutto il codice php. 
SUNTO.md viene generato automaticamente dallo script `sunto.py` presente in questa repository. Digirare: `./sunto.py .` dall'interno della repository stessa.
