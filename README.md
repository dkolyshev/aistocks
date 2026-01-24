The small app with a service that generates stock reports based on settings we configure. Basically the settings congigured via app will dictate the companies included and format of output of the reports. Reports will be generated in 3 formats: HTML, PDF and flipbook.

## Table of Contents

- [The app features](#the-app-features)
- [Project Structure](#project-structure)
- [How to use](#how-to-use)
  - [Prerequisites](#prerequisites)
  - [Quick Start](#quick-start)

# The app features

> [!IMPORTANT]
> For improved testing results and better user experience, the original `data.csv` was replaced with its extended version `extended-data.csv`. Basically, it's the same file but with two additional columns, "Exchange" and "Target Price," which are missing in `data.csv` but used in the example reports.

- [x] The app generates report in 3 formats: HTML, PDF and flipbook.
- [x] Add/update/delete settings for report generation. Settings are stored in the `reportSettings.json`.
- [x] The `Generate reports` button to run reports service.
- [x] The app allows upload of images to `/images` folder and upload of pdf to the `/reports` folder.
- [x] The `Report File Name` field is configurable.
- [x] The `Report Title` field is configurable.
- [x] The `Author Name` field is configurable.
- [x] Instead of an API, the app uses the `extended-data.csv` - it's an extended version of the `data.csv` with added columns "Exchange" and "Target Price" that are missing in the original `data.csv`.
- [x] The `Number of Stocks` field is configurable.
- [x] The `PDF Cover Image` is configurable.
- [x] The `Article Image` field is configurable. Image size is limited by 180x180.
- [x] The `Disclaimer HTML` field is configurable. By default - uses predefined template: `data/disclaimer.html`.
- [x] The `Report intro HTML` is configurable. By default - uses predefined template: `data/reportIntro.html`.
- [x] The `Stock Block HTML` is configurable. By default - uses predefined template: `data/stockBlock.html`.
- [x] The `Upload PDF` field should upload PDF to /reports/ and uses it instead the PDF report generation.
- [x] The app generates report for each entry in `reportSettings.json`
- [x] Generated files are overwritten on every generation.
- [x] Filenames are specified in settings.
- [x] Number of stocks is used to limit the number of companies so if we set it to 3, the output would use top 3 companies from `extended-data.csv`.
- [x] Reports is generated in the `/reports/` folder.
- [x] Use `Trading View` service for embedding charts.
- [x] Following short codes is supported: `[Current Date]`, `[Chart]`, each `extended-data.csv` column as shortcode (`[Company]`, `[Exchange]`, `[Ticker]`, `[Price]`, etc).

> [!IMPORTANT]
> The important note about report filenames. I intentionally apply a filter on filenames that removes special characters and replaces spaces with hyphens. Such filtering helps to keep stability, compatibility, and safety, and guarantees that the generated reports' file names will show/work the same way on different systems. For example, if you put this string "Top 6 AI Stocks report" in the `Report File Name` field and generate reports, the output will be:
>
> - `Top-6-AI-Stocks-report.html`
> - `Top-6-AI-Stocks-report-flipbook.html`
> - `Top-6-AI-Stocks-report.pdf`

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
│   ├── extended-data.csv       # Stock data source with added columns "Exchange" and "Target Price"
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
