# README
I'm building local_presentyou plugin for Moodle 5.+, using Moodle Plugin Development.
I need the user, when login in the site, will confirm it's:
* department selecting from departments = ['Down', 'Upper]
* position selectting from positions = ['Teacher', 'Janitor]

On the form will be two selectbox for department and position, and two buttons: ['Confirm', 'Logout']

All the users, also the admin must to set it, without execute logout.

# Structure
```

local/presentyou/
├── classes/
│   └── form/
│       └── complete_profile_form.php
├── lang/
│   └── en/
│       └── local_presentyou.php
├── complete_profile.php
├── lib.php         (Optional, for helper functions)
├── middleware.php
├── version.php
└── settings.php    (Optional, for plugin settings if needed later)
```

Ho creato in `Site administration->Users->Users profile fiels` la 
categoria `presentyoy`, all'interno ho posto i campi: `department` e `position}

[Richiesta](https://gemini.google.com/app/0f9481bcd81c44db?is_sa=1&is_sa=1&android-min-version=301356232&ios-min-version=322.0&campaign_id=bkws&utm_source=sem&utm_source=google&utm_medium=paid-media&utm_medium=cpc&utm_campaign=bkws&utm_campaign=2024itIT_gemfeb&pt=9008&mt=8&ct=p-growth-sem-bkws&gad_source=1&gclid=CjwKCAjw7pO_BhAlEiwA4pMQvIZVbGVlw4CR0U6HgTcHBlWM67d5ztq_pV0qfUM-O5gSYpyQVZs-UxoCS-cQAvD_BwE&gclsrc=aw.ds)

