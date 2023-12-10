# Symfony API

This is a Symfony project that uses Docker to facilitate the development environment. Make sure you follow the steps below to set up and run the project correctly.

# Environment Configuration

Create a `.env` file in the project root with the following configuration:

```ini
#.env
POSTGRES_DB=aaxis_test
POSTGRES_USER=root
POSTGRES_PASSWORD=qwerty
PGADMIN_DEFAULT_EMAIL=admin@example.com
PGADMIN_DEFAULT_PASSWORD=admin
```

# Running with Docker

Make sure you are in the directory that contains your `docker-compose.yml` file. Then, run the following command to lift the containers:

```bash
docker-compose up
```

Access the console of the container containing the Symfony installation:

```bash
docker exec -it symfony-api /bin/bash
```
# Installing dependencies

Once inside the container, install the dependencies using Composer:

```bash
composer install
```
# Migrations

Run the migrations to configure the database:

```bash
php bin/console doctrine:migrations:migrate
```
## Use of Endpoints

You can test the following endpoints using Postman:

### Create Product:

- **Method:** POST
- **URL:** `localhost:8000/products/create`
- **Authorization:** Include the following API key in the headers:
  - **Key:** `X-AUTH-TOKEN`
  - **Value:** `abcdef1234567890`
- **Request body (example):**
  ```json
  [
    {
      "sku": "SKU-2029",
      "product_name": "Coffee",
      "description": "The best coffee in the region."
    },
    // Other products...
  ]

### Update Product:

- **Method:** POST
- **URL:** `localhost:8000/products/update`
- **Authorization:** Include the following API key in the headers:
  - **Key:** `X-AUTH-TOKEN`
  - **Value:** `abcdef1234567890`
- **Request body (example):**
  ```json
  [
    {
      "sku": "SKU-2029",
      "product_name": "Café Tacuba 2",
      "description": "The best coffee in the region."
    },
    // Other products...
  ]

### Get Products:

- **Method:** GET
- **URL:** `localhost:8000/products`
- **Authorization:** Include the following API key in the headers:
  - **Key:** `X-AUTH-TOKEN`
  - **Value:** `abcdef1234567890`