The small app with a service that generates stock reports based on settings we configure. The settings configured via the app will dictate the companies included and the format of the output of the reports. Reports will be generated in 3 formats: HTML, PDF, and flipbook.

## Table of Contents

- [The app features](#the-app-features)
- [Project Structure](#project-structure)
- [How to use](#how-to-use)
  - [Prerequisites](#prerequisites)
  - [Quick Start](#quick-start)
- [Added UI improvements](#added-ui-improvements)

# The app features

> [!IMPORTANT]
> For improved testing results and better user experience, I've added the extended data source file `extended-data.csv` (in additional to the original `data.csv`). Basically, it's the same file but with two additional columns, "Exchange" and "Target Price," which are missing in `data.csv` but used in the example reports. You can check this file here [data/extended-data.csv](data/extended-data.csv).

- [x] The app generates a report in 3 formats: HTML, PDF, and flipbook.
- [x] Add/update/delete settings for report generation. Settings are stored in the `reportSettings.json`.
- [x] The `Generate reports` button to run the reports service.
- [x] The app allows upload of images to the `/images` folder and upload of PDF to the `/reports` folder.
- [x] The `Report File Name` field is configurable.
- [x] The `Report Title` field is configurable.
- [x] The `Author Name` field is configurable.
- [x] Instead of an API, the app uses the provided CSV file as a source of the data (`extended-data.csv` or `data.csv`, based on the user's choice).
- [x] The `Number of Stocks` field is configurable.
- [x] The `PDF Cover Image` is configurable.
- [x] The `Article Image` field is configurable.
- [x] The `Article Image` size is limited to 200x200 (based on the articleImage.jpg size from examples).
- [x] The `Disclaimer HTML` field is configurable. By default - uses predefined template: `data/disclaimer.html`.
- [x] The `Report intro HTML` is configurable. By default - uses predefined template: `data/reportIntro.html`.
- [x] The `Stock Block HTML` is configurable. By default - uses predefined template: `data/stockBlock.html`.
- [x] The `Upload PDF` field should upload the PDF to /reports/ and use it instead of the PDF report generation.
- [x] The app generates a report for each entry in `reportSettings.json`.
- [x] Generated files are overwritten on every generation.
- [x] Filenames are specified in settings.
- [x] Number of stocks is used to limit the number of companies, so if we set it to 3, the output would use the top 3 companies from `extended-data.csv`.
- [x] Reports are generated in the `/reports/` folder.
- [x] Use `Trading View` service for embedding charts.
- [x] Following short codes are supported: `[Current Date]`, `[Chart]`, and each column as a shortcode from the provided CSV file (`[Company]`, `[Exchange]`, `[Ticker]`, `[Price]`, etc).

> [!IMPORTANT]
> I intentionally apply a filter on filenames that removes special characters and replaces spaces with hyphens. Such filtering helps maintain stability, compatibility, and safety, and guarantees that the generated reports' file names will appear and work the same way across different systems. How it works: if you put the string "Top 6 AI Stocks report" in the `Report File Name` field and generate reports, the output will be:
>
> - `Top-6-AI-Stocks-report.html`
> - `Top-6-AI-Stocks-report-flipbook.html`
> - `Top-6-AI-Stocks-report.pdf`

# Project Structure

```
aiStocks/
├── app/                                    # Application core (MVC architecture)
│   ├── config/
│   │   └── config.php                      # App configuration and paths
│   ├── controllers/
│   │   ├── Contracts/
│   │   │   └── ControllerInterface.php     # Controller contract
│   │   ├── Support/
│   │   │   ├── ReportGenerationOrchestrator.php  # Coordinates report generation
│   │   │   └── ShortcodeProvider.php       # Provides shortcode replacements
│   │   ├── ReportController.php            # Routes requests to handlers
│   │   ├── ReportFileController.php        # Handles report file requests
│   │   └── SettingsController.php          # Handles settings requests
│   ├── helpers/
│   │   ├── StockFormatter.php              # Formats stock data values
│   │   └── View.php                        # Renders view templates
│   ├── models/
│   │   ├── Contracts/
│   │   │   ├── CsvDataReaderInterface.php  # CSV reader contract
│   │   │   ├── FileSystemInterface.php     # File system contract
│   │   │   └── SettingsManagerInterface.php  # Settings manager contract
│   │   ├── Support/
│   │   │   └── FileSystem.php              # File system operations
│   │   ├── CsvDataReader.php               # Parses CSV stock data
│   │   └── SettingsManager.php             # Manages report settings
│   ├── services/
│   │   ├── Contracts/
│   │   │   └── ReportGeneratorInterface.php  # Report generator contract
│   │   ├── Support/
│   │   │   ├── FileLoaderService.php       # Loads template files
│   │   │   └── ImageService.php            # Handles image processing
│   │   ├── BaseReportGenerator.php         # Abstract base for generators
│   │   ├── HtmlReportGenerator.php         # Generates HTML reports
│   │   ├── PdfReportGenerator.php          # Generates PDF reports
│   │   ├── FlipbookGenerator.php           # Generates flipbook reports
│   │   ├── ShortcodeProcessor.php          # Processes template shortcodes
│   │   └── FileUploadHandler.php           # Handles file uploads
│   └── views/
│       ├── report-manager/                 # Report manager UI templates
│       │   ├── layout.php                  # Main layout wrapper
│       │   ├── index.php                   # Dashboard page
│       │   ├── form.php                    # Settings form
│       │   ├── active-config-table.php     # Active config display
│       │   ├── reports-table.php           # Reports listing table
│       │   └── alert.php                   # Alert messages
│       └── reports/                        # Report output templates
│           ├── flipbook/                   # Flipbook format templates
│           │   ├── layout.php              # Flipbook layout
│           │   ├── controls.php            # Navigation controls
│           │   ├── cover.php               # Cover page
│           │   ├── intro.php               # Introduction page
│           │   ├── disclaimer.php          # Disclaimer page
│           │   └── stock-page.php          # Stock detail page
│           ├── html/                       # HTML format templates
│           │   ├── layout.php              # HTML report layout
│           │   ├── cover.php               # Cover section
│           │   └── stock-block.php         # Stock block section
│           └── pdf/                        # PDF format templates
│               └── layout.php              # PDF report layout
├── data/                                   # Data files and templates
│   ├── data.csv                            # Original stock data
│   ├── extended-data.csv                   # Extended data with extra columns
│   ├── disclaimer.html                     # Default disclaimer template
│   ├── reportIntro.html                    # Default intro template
│   └── stockBlock.html                     # Default stock block template
├── .docker/                                # Docker configuration
│   ├── docker-compose.yml                  # Docker Compose config
│   ├── Dockerfile                          # Docker image definition
│   └── .env.example                        # Environment variables example
├── example/                                # Example output files
├── images/                                 # Uploaded images storage
├── public/                                 # Web root
│   ├── index.php                           # Application entry point
│   └── assets/css/                         # Stylesheets
├── reports/                                # Generated reports output
├── reportSettings.json                     # Report generation settings
├── .htaccess                               # Apache URL rewriting
└── README.md
```

# How to use

## Prerequisites

- Git installed
- Docker installed
- Docker Compose installed

---

## Quick Start

You can check the deployed app here https://aistocks.fly.dev or deploy it on your local machine/remote server:

### 1. Navigate to the directory where you want to deploy the app

```bash
cd ~/apps
```

### 2. Clone my GitHub repository with the app

```bash
git clone https://github.com/dkolyshev/aistocks.git
```

### 3. Navigate to the Docker directory inside the app root

```bash
cd aiStocks/.docker
```

### 4. Copy environment file

```bash
cp .env.example .env
```

### 5. Start the Docker container

```bash
docker-compose up -d
```

This will:

- Build the PHP 5.5 + Apache image
- Install wkhtmltopdf for PDF generation
- Mount the project directory
- Start the web server on port 8080

### 6. Access the application

Open your browser and navigate to:

**http://localhost:8080** or **http://localhost:8080/reportManager.html**

You'll be redirected to the Report Manager.

### 7. Stop the container (when you don't need the app working)

```bash
docker-compose down
```

---

# Added UI improvements

- Added pagination in flipbook.
- Added the "Generated Reports" panel to the Report Manager (see bottom part).
- Show messages about success/failure after.
- Added "click-to-copy" feature for shortcodes.
- Customizable data source (select list based on the available .csv files).
- Theme selector for the Report Manager page (see footer).
- More flexible settings for content fields: "Report Intro HTML", "Stock Block HTML", "Disclaimer HTML".
