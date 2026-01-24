The small app with a service that generates stock reports based on settings we configure. Basically the settings congigured via app will dictate the companies included and format of output of the reports. Reports will be generated in 3 formats: HTML, PDF and flipbook.

## Table of Contents

- [The app features](#the-app-features)
- [Project Structure](#project-structure)
- [How to use](#how-to-use)
  - [Prerequisites](#prerequisites)
  - [Quick Start](#quick-start)

# The app features

- [x] The app generates report in 3 formats: HTML, PDF and flipbook.
- [ ] Add/update/delete settings for report generation. Settings are stored in the `reportSettings.json`.
- [ ] Generate reports button to run reports service.
- [ ] The app allows upload of images to `/images` folder and upload of pdf to `/reports` folder.
- [ ] The `Report File Name` field should be configurable.
- [ ] The `Report Title` field should be configurable.
- [ ] The `Author Name` field should be configurable.
- [ ] The app uses the `data.csv` instead of an API.
- [ ] The `Number of Stocks` field should be configurable.
- [ ] The `PDF Cover Image` should be configurable.
- [ ] The `Article Image` field should be configurable. Image size should be limited by 180x180.
- [ ] The `Disclaimer HTML` field should be configurable. By default - uses predefined template: `data/disclaimer.html`.
- [ ] The `Report intro HTML` should be configurable. By default - uses predefined template: `data/reportIntro.html`.
- [ ] The `Stock Block HTML` should be configurable. By default - uses predefined template: `data/stockBlock.html`.
- [ ] The `Upload PDF` field should upload PDF to /reports/ and uses it instead the PDF report generation.
- [ ] The app generates report for each entry in `reportSettings.json`
- [ ] Generated files are overwritten on every generation.
- [ ] Filenames are specified in settings.
- [ ] Number of stocks is used to limit the number of companies so if we set it to 3, the output would use top 3 companies from `data.csv`.
- [ ] Reports should be generated in the `/reports/` folder.
- [ ] Use `Trading View` service for embedding charts.
- [ ] Following short codes should be supported: `[Current Date]`, `[Chart]`, each `data.csv` column as shortcode (`[Company]`, `[Exchange]`, `[Ticker]`, `[Price]`, etc).

# Project Structure

```
aiStocks/
├── app/                        # Application core (MVC architecture)
│   ├── config/                 # Configuration files
│   │   └── config.php          # App configuration
│   ├── controllers/            # Request handlers
│   │   ├── ReportController.php
│   │   ├── ReportFileController.php
│   │   └── SettingsController.php
│   ├── helpers/                # Utility classes
│   │   ├── StockFormatter.php  # Stock data formatting
│   │   └── View.php            # View rendering helper
│   ├── models/                 # Data layer
│   │   ├── CsvDataReader.php   # CSV data parser
│   │   └── SettingsManager.php # Settings management
│   ├── services/               # Business logic
│   │   ├── BaseReportGenerator.php
│   │   ├── HtmlReportGenerator.php
│   │   ├── PdfReportGenerator.php
│   │   ├── FlipbookGenerator.php
│   │   ├── ShortcodeProcessor.php
│   │   └── FileUploadHandler.php
│   └── views/                  # View templates
│       ├── report-manager/     # Report manager UI
│       └── reports/            # Report templates
├── data/                       # Data files and templates
│   ├── data.csv                # Stock data source
│   ├── disclaimer.html         # Disclaimer template
│   ├── reportIntro.html        # Report intro template
│   └── stockBlock.html         # Stock block template
├── .docker/                    # Docker configuration
│   ├── docker-compose.yml
│   ├── Dockerfile
│   └── .env.example
├── example/                    # Example output files
├── images/                     # Uploaded images storage
├── public/                     # Web root
│   ├── index.php               # Application entry point
│   └── assets/css/             # Stylesheets
├── reports/                    # Generated reports output
├── reportSettings.json         # Report generation settings
├── .htaccess                   # Apache URL rewriting
└── README.md
```

# How to use

## Prerequisites

- Docker installed on your machine
- Docker Compose installed

---

## Quick Start

You can check the deployed app here https://aistocks.fly.dev or deploy it on your local machine/remote server:

### 1. Navigate to the Docker directory inside the app root

```bash
cd ~/aiStocks/.docker
```

### 2. Copy environment file (optional)

```bash
cp .env.example .env
```

### 3. Start the Docker container

```bash
docker-compose up -d
```

This will:

- Build the PHP 5.5 + Apache image
- Install wkhtmltopdf for PDF generation
- Mount the project directory
- Start the web server on port 8080

### 4. Access the application

Open your browser and navigate to:

**http://localhost:8080** or **http://localhost:8080/reportManager.html**

You'll be redirected to the Report Manager.

### 5. Stop the container

```bash
docker-compose down
```

---
