# CloudyPress Framework ☁️

> Laravel‑style routing + ORM for WordPress plugins

CloudyPress is a lightweight framework that brings modern PHP conventions (routing, query builder, ORM‑style relations) into the WordPress ecosystem.  
It’s designed for plugin developers who want expressive, testable, and scalable code without fighting against WordPress’ global APIs.

---

## 🚀 Installation

Require via Composer:

```bash
composer require cloudypress/framework
```

Make sure your plugin’s composer.json has autoloading enabled and that you run composer dump-autoload after installation.

## ✨ Features

- Routing: Define clean, Laravel‑style routes for your plugin endpoints.
- Query Builder: Chainable, expressive SQL builder with bindings.
- ORM‑style Relations: Define hasMany, belongsTo, etc. for WordPress tables.
- Eager Loading: Load related models efficiently with with().
- Helpers: Common utilities for plugin development.


## 🛠️ Requirements
- PHP 8.0+
- WordPress 6.x+
- MySQL/MariaDB with mysxqli extension

---

## 📖 Documentation

Looking for guides, API references, and examples?  
You’ll find the full documentation here:

👉 [CloudyPress Documentation](https://github.com/CloudyPress/docs)

## 📜 License
2025 - CloudyPress is open‑source software licensed under the  
[GNU General Public License v2.0 or later](https://www.gnu.org/licenses/old-licenses/gpl-2.0.html).
