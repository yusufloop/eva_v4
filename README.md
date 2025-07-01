step to use the system:
1. change config/example.database.php into config/database.php
2. fill in the inside of it, the database name, password etc
3. change config/example.mail.php into config/mail.php
4. fill in the inside of mail into your configuration.
5. open your localhost, and the login is in the first page.


i have change the file directory to become more clean and readable:

eva_system/
├── config/
│   ├── app.php              (application settings)
│   ├── database.php         (database connection)
│   ├── mail.php            (email settings)
│   └── permissions.php      (role-based permissions)
│
├── includes/
│   ├── header.php          (HTML head, navigation)
│   ├── footer.php          (closing tags, scripts)
│   ├── sidebar.php         (left sidebar navigation)
│   └── alerts.php          (message display functions)
│
├── helpers/
│   ├── auth_helper.php     (login checks & role functions)
│   ├── search_helper.php   (search & filter functions)
│   ├── export_helper.php   (CSV/Excel export functions)
│   ├── format_helper.php   (date, currency formatting)
│   └── validation_helper.php (form validation functions)
│
├── functions/              (Database + Actions Combined)
│   ├── user_functions.php      (user process)
│   ├── device_functions.php    (device process)
│   ├── dependent_functions.php (family process )
│   ├── call_log_functions.php  (call history process )
│   ├── alert_functions.php     (alerts process)
│   ├── inventory_functions.php (inventory process )
│   └── auth_functions.php      (login/register )
│
├── pages/
│   ├── auth/
│   │   ├── index.php       (login/register)
│   │   ├── forgot.php      (forgot password)
│   │   └── reset.php       (reset password)
│   │
│   ├── dashboard.php       (overview - role-based content)
│   ├── devices.php         (device management - role-based)
│   ├── family.php          (family members - role-based)
│   ├── call_logs.php       (call history - role-based)
│   ├── alerts.php          (alert history - role-based)
│   ├── settings.php        (settings - role-based)
│   ├── profile.php         (user profile)
│   ├── users.php           (admin only - user management)
│   └── inventory.php       (admin only - device inventory)
│
├── components/
│   ├── data_table.php      (responsive data tables)
│   ├── search_bar.php      (search input component)
│   ├── pagination.php      (pagination controls)
│   ├── export_buttons.php  (export CSV/Excel buttons)
│   └── stats_card.php      (dashboard statistics cards)
│
├── assets/
│   ├── css/
│   │   ├── main.css        (base + mobile responsive)
│   │   ├── dashboard.css   (dashboard styles)
│   │   ├── forms.css       (form styles)
│   │   └── components.css  (component styles)
│   │
│   ├── js/
│   │   ├── main.js         (common functions)
│   │   ├── dashboard.js    (dashboard interactions)
│   │   ├── forms.js        (form validation, modals)
│   │   └── tables.js       (table features, search)
│   │
│   └── images/
│
├── exports/                
├── uploads/              
└── vendor/               
