# LGI Fixed Asset

**LGI Fixed Asset** is an automated application designed to streamline the process of generating asset numbers and sending notifications to relevant divisions via email. This application runs as a cron scheduled job, ensuring that asset management tasks are handled efficiently and on time.

## Table of Contents
- [Overview](#overview)
- [Features](#features)
- [Technology Stack](#technology-stack)
- [Installation](#installation)
- [Configuration](#configuration)
- [Cron Job Setup](#cron-job-setup)
- [Usage](#usage)
- [Contact](#contact)

## Overview
This application is intended for automating the asset number generation process and notifying the relevant divisions via email. It operates as a background job, triggered at regular intervals through a cron schedule.

## Features
- **Automated Asset Number Generation**: Automatically generates unique asset numbers for new assets.
- **Email Notifications**: Sends automated email notifications to the relevant divisions regarding new or updated asset information.
- **Scheduled Execution**: Runs as a cron job, ensuring tasks are performed at specified intervals without manual intervention.

## Technology Stack
- **Backend**: [Laravel](https://laravel.com/) - A powerful PHP framework for handling server-side logic.
- **Database**: [MSSQL](https://www.microsoft.com/en-us/sql-server/sql-server-downloads) - A robust relational database management system for storing asset data.
- **Email**: Laravel's built-in email functionality for sending notifications.

## Installation

### Prerequisites
- [PHP](https://www.php.net/) >= 7.4
- [Composer](https://getcomposer.org/) for dependency management
- [MSSQL](https://www.microsoft.com/en-us/sql-server/sql-server-downloads) for the database

### Steps
1. **Clone the repository**:
   ```bash
   git clone https://github.com/Antonius1712/LGI-FIXED-ASSET.git
   cd lgi-fixed-asset
   ```
2. **Install backend dependencies**:
   ```bash
   composer install
   ```
3. **Environment setup**:
   Copy `.env.example` to `.env`:
   ```bash
   cp .env.example .env
   ```
   Configure the .env file with your MSSQL database credentials and other environment-specific variables.

4. **Database migration: Run the migrations to set up the required tables in your MSSQL database**:
   ```bash
   php artisan migrate
   ```
5. **Generate application key**:
   ```bash
   php artisan key:generate
   ```
6. **Start the development server**:
   ```bash
   php artisan serve
   ```

## Configuration

Edit the `.env` file to configure your database connection, mail server, and other environment-specific settings. Make sure to set the cron job schedule as per your requirements.

## Cron Job Setup

To ensure that the application runs as a scheduled job, set up a cron job on your server:

1. Open the crontab file:
    ```bash
    crontab -e
    ```
2. Add the following line to schedule the job:
    ```bash
    * * * * * php /path-to-your-project/artisan schedule:run >> /dev/null 2>&1
    ```
    Replace /path-to-your-project/ with the actual path to your Laravel project. Adjust the cron timing (* * * * *) based on your scheduling needs.

## Usage

The application runs automatically based on the cron schedule. It generates asset numbers and sends email notifications without manual intervention.


## Contact

For any questions or support, please reach out to:

- **Name**: Antonius Christian
- **Email**: antonius1712@gmail.com
- **Phone**: +6281297275563
- **LinkedIn**: [Antonius Christian](https://www.linkedin.com/in/antonius-christian/)

Feel free to connect with me via email or LinkedIn for any inquiries or further information.