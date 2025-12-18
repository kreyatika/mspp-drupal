# Permit Application Module

## Description
This module provides a permit application form for healthcare professionals to apply for practice permits. It includes form validation, file uploads, and API integration for submitting applications.

## Features
- Complete permit application form with personal, contact, and professional information
- File upload support for diplomas, ID documents, and photos
- NIF (Tax Identification Number) formatting
- Form validation
- API integration for submitting applications
- Responsive layout with informational sidebar

## Installation
1. Copy the module to `modules/custom/permit_application`
2. Enable the module: `drush en permit_application -y`
3. Clear cache: `drush cr`

## Configuration
1. Navigate to Configuration > System > Permit Application Settings
2. Set your API endpoint URL
3. Save configuration

## API Endpoint Configuration
The module expects the API endpoint to accept POST requests with the following JSON structure:

```json
{
  "nom": "string",
  "prenom": "string",
  "date_naissance": "YYYY-MM-DD",
  "lieu_naissance": "string",
  "nif": "000-000-000-0",
  "sexe": "M|F",
  "email": "email@example.com",
  "telephone": "string",
  "adresse": "string",
  "categorie": "string",
  "specialite": "string",
  "etablissement": "string",
  "annee_diplome": 2024,
  "files": {
    "diplome": {...},
    "piece_identite": {...},
    "photo": {...}
  }
}
```

## Usage
The form is accessible at: `/services/demande-de-permis-dexercer`

## File Upload Limits
- Diplôme: PDF, JPG, PNG (max 5MB)
- Pièce d'identité: PDF, JPG, PNG (max 5MB)
- Photo: JPG, PNG (max 2MB)

## Requirements
- Drupal 9 or 10
- PHP 7.4+
- GuzzleHTTP client

## Support
For issues or questions, contact: dfpss@mspp.gouv.ht
