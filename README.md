
# Plugin description

This is a custom WordPress plugin developed to support event registrations and anonymous form submissions. It was designed for real-world use cases, such as organizing symposia and collecting policy assessments (e.g. regarding alcohol and drug use in student associations). The plugin provides secure data collection, an intuitive admin interface, and export functionality, all built on WordPress core principles.

## Features

- Register users for symposia through a dynamic front-end form  
- Manage and view registrations in the WordPress admin interface  
- Export registration data to CSV format  
- Process anonymous survey submissions using UUID-based token links  
- Display results securely via tokenized result pages  
- Automatically mark expired events as 'Afgelopen' using scheduled tasks  
- AJAX-based admin controls for improved usability  
- Pagination and filtering for large datasets  
- Secure form handling using WordPress nonces and sanitization  

## Plugin Architecture

The plugin is designed with modularity and maintainability in mind:

- **Custom Post Types**:  
  - `symposium`: for event registration and participant tracking  
  - `trimbos`: for configuring forms and survey logic

- **Custom Database Tables**:  
  - `trimbos_form_submissions`: stores submission metadata  
  - `trimbos_form_answers`: stores individual answers, linked to a submission ID

- **Tokenized Access**:  
  Result pages use a secure UUID4 token instead of numeric IDs to maintain privacy

- **ACF Integration**:  
  All form questions are managed using ACF Repeater fields, allowing non-technical users to change the content of surveys

- **WordPress Cron**:  
  A scheduled task checks event dates and updates the post status accordingly (e.g. sets them to 'Afgelopen')

## Technologies Used

| Area             | Technology / Approach                                      |
|------------------|------------------------------------------------------------|
| CMS Core         | WordPress plugin API, custom post types, meta fields       |
| Forms            | ACF (Advanced Custom Fields), standard HTML forms          |
| Database Layer   | Custom SQL tables with `wpdb`, schema updates via `dbDelta`|
| Security         | Nonces, capability checks (`current_user_can`), sanitization|
| Admin Interface  | jQuery-based AJAX, WordPress admin table styling           |
| Output & Export  | CSV generation with `fputcsv`, headers for secure download |
| Routing          | Rewrite rules and query vars for public result links       |

## File Overview

- **Total files**: 25  
- **Total lines of code**: 1,731  
- **Languages**: PHP, JavaScript (jQuery), HTML, CSS  

**Key directories and files**:
```
wtjdl-main/
├── includes/
│   ├── class-event-registration-core.php
│   ├── class-symposium-registration.php
│   ├── class-trimbos-core.php
│   └── ... (modular classes)
├── public/
│   ├── registrations-page.php
│   ├── registrations-success-page.php
│   └── trimbos-results-page.php
├── admin/
│   ├── registrations-page.php
│   ├── trimbos-statistieken.php
│   └── js/admin-scripts.js
└── wtjdl-plugin.php (main plugin bootstrap file)
```

## Security

- All admin interactions are protected with WordPress nonces and capability checks  
- User input is sanitized and validated before use (`sanitize_text_field`, `sanitize_email`, etc.)  
- Output is escaped using `esc_html`, `esc_attr`, and `wp_kses` where applicable  
- AJAX actions are limited to users with the appropriate role  

## Intended Use Case

This plugin was developed to serve a dual purpose:

1. Manage and track registrations for symposia, including participant data collection, admin exports, and status changes based on event dates.  
2. Handle anonymous surveys (such as alcohol and drug policy assessments), generating personalized result pages accessible via secure links.

## Author

**Tristan Riethorst**  
[bytris.nl](https://bytris.nl)  
Plugin developed as part of the **** (****) in cooperation with ***.
Hidden for privacy purposes.
