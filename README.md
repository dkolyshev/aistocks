# Project overview:

The small app with a service that generates stock reports based on settings we configure.

- App example: reportManager.html
- Service output example: 6AIStocks.pdf, 6AIStocks.html. 6AIStocks flipbook.html

Basically the settings congigured via app will dictate the companies included and format of output of the reports.
Reports will be generated in 3 formats: HTML, PDF and flipbook.

# Front-end CRUD app

- Features:
  - Add/update/delete settings for report generation. Settings are stored in reportSettings.json
  - Generate reports button to run reports service.
  - Allows upload of images to /images folder and upload of pdf to /reports folder
- Settings:
  - Report File Name - eg. 6AIStocks
  - Report Title - eg. Today's Top 6 AI Stocks
  - Author Name - eg. Today's Top Stocks
  - API call - Just a placeholder field for now, API service will be integrated later. Use data.csv
  - Number of Stocks - eg. 6
  - PDF Cover Image - eg. see PDF
  - Article Image (180x180) - eg. articleImage.jpg
  - Disclaimer HTML - eg. disclaimer.html
  - Report intro HTML - eg. reportIntro.html
  - Stock Block HTML - eg. stockBlock.html
  - Upload PDF - uploads PDF to /reports/

# Generate reports service

- Uses reportSettings.json and data.csv to generate HTML, PDF and flipbook reports
  - It will generate report for each entry in reportSettings.json
  - Generated files are overwritten on every generation
  - Filenames are specified in settings
  - Number of stocks is used to limit the number of companies so if we set it to 3, the output would use top 3 companies from data.csv.
- Reports should be generated in /reports/ folder.
  - Example report output:
    - /reports/6AIStocks.html
    - /reports/6AIStocks.pdf
    - /reports/6AIStocks
- Use Trading View service for embedding charts - see 6AIStocks.html
  - https://www.tradingview.com/widget-docs/widgets/charts/symbol-overview/
- Following short codes should be supported:
  - [Current Date] - eg. format 2026-01-22 05:11:24
  - [Chart] - Trading view chart embed.
  - data.csv columns - [Company], [Exchange], [Ticker], [Price]....

# How to use

## Prerequisites

- Docker installed on your machine
- Docker Compose installed

---

## Quick Start

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

**http://localhost:8080**

You'll be automatically redirected to the Report Manager.

### 5. Stop the container

```bash
docker-compose down
```

---
