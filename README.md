# PHP Backend for Mobile
A backend api targeted at mobile devices

## Features
- Device fingerprint authentication
- JWT authentication using RSA generated keys
- Access token renewal via refresh token
- User agent validation
- Dynamic routing
- MVC architecture
- Custom error codes

## Usage
- Create a controller in the ```Controller/``` directory and extend ```BaseController.php```
- Define the route in ```app.php```
- The following authentication mechanisms are available in ```Library/Auth.php```

  ```php
  // For only user agent authentication:
  Auth::isUserAgentValid();
  
  // For only fingerprint
  Auth::isFingerprintValid();
  
  //For only access token verification
  Auth::isAccessTokenValid();
  
  // For all mechanisms
  Auth::authenticateUser();
  ```
- PDO instance available in ```Library/Database.php```

  ```php
  $pdo = Database::getpdo();
  ```
  
- Add API keys to .env file. It is automatically loaded into the systems environment variables by ```Library/Env.php```
- To generate new RSA public & private keys for JWT authentication, check out ```Library/genkey.php```
