# MediFind

A pharmacy inventory and medicine-finder web app with separate portals for administrators, pharmacies, and users.

## Overview

MediFind helps users find which pharmacies have a medicine in stock, while pharmacies keep their own stock levels up to date and administrators manage the whole system.

It is built with plain PHP and MySQL and runs locally on XAMPP. A database manual (PDF) describing the schema is included in the repository.

## Features

- User portal: register, log in, search for medicines, view results by pharmacy, and place orders
- Substitute medicine suggestions in search results
- Pharmacy portal: log in, update stock levels, and see low-stock alerts
- Admin portal: manage medicines, pharmacies, and users; view orders, reports, and low-stock alerts
- Hashed passwords, prepared statements, and CSRF tokens on forms

## Tech Stack

| Layer | Technology |
| --- | --- |
| Backend | PHP |
| Database | MySQL |
| Frontend | HTML, CSS, JavaScript |
| Local server | XAMPP (Apache + MySQL) |

## Setup

1. Install XAMPP and place this folder in its `htdocs` directory (as `MediFind`).
2. Start Apache and MySQL from the XAMPP Control Panel.
3. In phpMyAdmin, import `database/medifind.sql`.
4. If your MySQL credentials differ from the XAMPP defaults, update `includes/db_connect.php`.
5. Open http://localhost/MediFind/.

## Usage

| Role | Entry point |
| --- | --- |
| User | `/user/login.php` (or register at `/user/register.php`) |
| Pharmacy | `/pharmacy/login.php` |
| Admin | `/admin/login.php` |

Demo accounts for each role are created by `database/medifind.sql`. They are sample data for local use only.

## Project Structure

```
admin/       admin portal (medicines, pharmacies, users, orders, reports)
pharmacy/    pharmacy portal (dashboard, stock updates)
user/        user portal (register, search, orders)
includes/    shared auth, database connection, header/footer, helpers
database/    medifind.sql schema and sample data
css/ js/ images/
```

## Future Improvements

- Location-aware search to rank nearby pharmacies
- Automated tests for the core ordering and stock flows
