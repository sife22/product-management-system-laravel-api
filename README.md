<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

## About the project [ Product Management System ]

This project is a product management application developed with the Laravel framework. It allows for the synchronization and importation of products from an external API and a CSV file. Here are the main features:

- Product Synchronization : 
    - The application pulls product data from an external REST API.
    - Products are updated in the database or created if they do not exist.
- Product Importation :
    - Users can import products via a CSV file.
    - Data validation is performed to ensure the integrity and conformity of the information.
- Variation Management :
    - Products can have variations (such as color and size), which are also managed and stored in the database.
- Soft Deletion :
    - The application allows for the deletion of existing products before a new import if necessary.

## Methods Overview 

    - syncProducts()
        - Purpose: Synchronizes products from an external API.
        - Functionality:
            - Sends a GET request to the specified API endpoint.
            - If the request is successful, it retrieves the product data and updates or creates each product in the database based on its ID.
            - Returns a success message or an error message if the API call fails.
            
    ------------------------------------------------------------------------------------------------------------
            
    - validateData(array $fields)
        - Purpose: Validates the product data extracted from the CSV file.
        - Functionality:
            - Takes an array of fields and checks:
                If the product ID is present and numeric.
                If the price is numeric and greater than or equal to zero.
            - Returns the validated data as an associative array or false if validation fails.
    
    ------------------------------------------------------------------------------------------------------------

    - import(Request $request)
        - Purpose: Imports products from a CSV file.
        - Functionality:
            - Validates the uploaded file to ensure it is a required CSV file.
            - Reads the contents of the CSV file and processes each line, skipping the header.
            - Retrieves existing product IDs from the database.
            - For each line:
                - Trims whitespace from the product ID and checks if it exists.
                - Deletes existing products with the same ID before updating.
                - Validates the data using validateData().
                - Creates new products in the database.
                - Handles variations if they exist and are properly formatted.
                - Collects imported product IDs for further processing.
                - Marks products not present in the imported file as deleted.
            - Returns a success message with the count of imported products or an error message if validation fails.
            
    ------------------------------------------------------------------------------------------------------------
    
## Summary 
    
    - syncProducts(): Fetches and updates/creates products from an API.
    - validateData(): Checks the validity of product data.
    - import(): Handles the importation of products from a CSV file, including validation and variation management.

## Technologies Used
 * Laravel : PHP framework for web application development.
 * Eloquent ORM : For fluent database manipulation.
 * REST API : For communication with external services and data retrieval.

## Project Goals

* To ensure efficient data synchronization between the application and external sources.
* To facilitate the import of large amounts of data from CSV files.


