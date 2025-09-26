# Iran API

A lightweight PHP-based RESTful API app that allows you to do simple CRUD operations on Iran's cities.

> 🟡 This project marked my first interaction with APIs and how they work. REBUILT OLD PROJECT

## 🔧 Setup Instructions

1. **Clone the repository**
   ```bash
   git clone https://github.com/mmrahimi/iran-api
   ```

2. **Install dependencies**
   ```bash
   composer install
   ```
   
3. **Configure environment**
- Copy `.env.example` to `.env`
- Set your DB credentials

4. **Import the database**
- Use `iran.sql` to create tables

5. **Run the server**
   ```bash
   php -S localhost:8000
   ```

6. **Send out requests to these endpoints**

### 📡 CRUD
```
/api/v1/cities
```

### 🔐 Auth
```
/api/auth.php
```
Use one of the defined users' email to get a JWT token.

## 📦 Features
- JWT-based auth
- A caching system for the GET method results (file-based)
