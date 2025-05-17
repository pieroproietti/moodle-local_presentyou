# README
I'm building local_presentyou plugin for Moodle 5.+, using Moodle Plugin Development.
I need the user, when login in the site, will confirm it's:
* department selecting from departments = ['Down', 'Upper]
* position selectting from positions = ['Teacher', 'Janitor]

On the form will be two selectbox for department and position, and two buttons: ['Confirm', 'Logout']

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

Ho creato in `Site administration->Users->Users profile fiels` i campi: `department` e `position`.

# AI 
Sto utilizzando [gemini 2.5 flash](https://gemini.google.com/) per analisi e codice.

Per fornire il contesto, utilizzare il file [SUNTO.md](./SUNTO.md) che fornisce all'AI tutto il codice php. 
SUNTO.md viene generato automaticamente dallo script `sunto.py`
