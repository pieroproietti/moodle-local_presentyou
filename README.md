# README
Sto scrivendo un plugin denominato `local_presentyou` di tipo `local` per Moodle 5.+, usando Moodle Plugin Development ed intelligenza artificiale.

Mi occorre che all'utente, ad ogni nuovo login, sia presentato un form con due campi `department` e `position` su una selectbox, e due bottoni: `Confirm` e `Logout`.

All the users, also the admin must to set it, without execute logout.

# Structure
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

Creare utilizzando l'interfcaccia web si `Site administration->Users->Users profile fiels` i campi: `department` e `position`.

# AI 
Utilizzo [gemini 2.5 flash](https://gemini.google.com/) per analisi e codice.

Per fornire il contesto, utilizzare il file [SUNTO.md](./SUNTO.md) che fornisce all'AI tutto il codice php. 
SUNTO.md viene generato automaticamente dallo script `sunto.py`
