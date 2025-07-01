


📁 Project Structure
🔧 Core Configuration
config/
├── 🚀 app.php              # Application settings & constants
├── 🗄️  database.php         # Database connection & config
├── 📧 mail.php             # Email/SMTP configuration
└── 🔐 permissions.php      # Role-based access rules
🎨 UI Components
includes/
├── 📄 header.php           # HTML head & navigation
├── 🦶 footer.php           # Closing tags & scripts
├── 📋 sidebar.php          # Left navigation menu
└── 🚨 alerts.php           # Message display system
🛠️ Helper Functions
helpers/
├── 🔐 auth_helper.php      # Authentication & role checking
├── 🔍 search_helper.php    # Search & filtering utilities
├── 📤 export_helper.php    # CSV/Excel export functions
├── 📅 format_helper.php    # Date/currency formatting
└── ✅ validation_helper.php # Form validation rules
⚙️ Business Logic
functions/
├── 👤 user_functions.php      # User CRUD & profile management
├── 📱 device_functions.php    # Device registration & monitoring
├── 👨‍👩‍👧‍👦 dependent_functions.php # Family member management
├── 📞 call_log_functions.php  # Call history & tracking
├── 🚨 alert_functions.php     # System alerts & notifications
├── 📦 inventory_functions.php # Device inventory management
└── 🔑 auth_functions.php      # Login/register/password reset
🌐 User Interface
pages/
├── 🔐 auth/                    # Authentication pages
│   ├── 🏠 index.php           # Login/register form
│   ├── 🔑 forgot.php          # Forgot password
│   └── 🔄 reset.php           # Password reset
├── 📊 dashboard.php           # Main dashboard (role-based)
├── 📱 devices.php             # Device management
├── 👨‍👩‍👧‍👦 family.php              # Family member management
├── 📞 call_logs.php           # Call history viewer
├── 🚨 alerts.php              # Alert history
├── ⚙️ settings.php            # User/system settings
├── 👤 profile.php             # User profile management
├── 👥 users.php               # 🔒 Admin: User management
└── 📦 inventory.php           # 🔒 Admin: Device inventory
🧩 Reusable Components
components/
├── 📊 data_table.php          # Responsive data tables
├── 🔍 search_bar.php          # Search input component
├── 📄 pagination.php          # Pagination controls
├── 📤 export_buttons.php      # Export functionality
└── 📈 stats_card.php          # Dashboard statistics cards
🎨 Frontend Assets
assets/
├── 🎨 css/
│   ├── 🌐 main.css            # Base styles + mobile responsive
│   ├── 📊 dashboard.css       # Dashboard-specific styles
│   ├── 📝 forms.css           # Form styling
│   └── 🧩 components.css      # Component styles
├── ⚡ js/
│   ├── 🔧 main.js             # Common JavaScript functions
│   ├── 📊 dashboard.js        # Dashboard interactions
│   ├── 📝 forms.js            # Form validation & modals
│   └── 📋 tables.js           # Table features & search
└── 🖼️ images/                 # Image assets
📂 Data Storage
exports/                       # 📤 Generated export files
uploads/                       # 📁 User uploaded files
vendor/                        # 📦 Composer dependencies
---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------

🚀 Quick Start
📋 Prerequisites

🐘 PHP 8.0 or higher
🗄️ MySQL 8.0 or higher
🎼 Composer (for dependencies)
🌐 Web server (Apache/Nginx)

⚡ Installation

📥 Clone the repository
git clone https://github.com/yusufloop/eva_v4.git
cd eva-v4

📦 Install dependencies
composer install

🗄️ Setup database
CREATE DATABASE eva_v3;
-- Import your database schema

⚙️ Configure settings


cp config/example.database.php config/database.php
cp config/example.mail.php config/mail.php


🎯 Access the application
http://localhost/login
