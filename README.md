# Mon SDK PHP

Un SDK PHP moderne pour interagir avec l'API de Mon Service.

## Installation

### Via Composer (Recommandé)

```bash
composer require mon-entreprise/mon-sdk
```

### Installation manuelle

1. Téléchargez les fichiers du SDK
2. Incluez le fichier principal :

```php
require_once 'src/SDK.php';
```

## Configuration rapide

```php
<?php
require_once 'vendor/autoload.php';

use MonSDK\SDK;

// Initialisation
$sdk = new SDK('https://api.monservice.com', 'votre-api-key');

// Test de connexion
if ($sdk->ping()) {
    echo "Connexion réussie !";
} else {
    echo "Erreur de connexion";
}
```

## Utilisation

### Authentification

```php
// Connexion utilisateur
try {
    $response = $sdk->auth()->login('user@example.com', 'password');
    echo "Connecté avec succès !";
} catch (\MonSDK\SDKException $e) {
    echo "Erreur: " . $e->getMessage();
}

// Déconnexion
$sdk->auth()->logout();
```

### Gestion des utilisateurs

```php
// Récupérer le profil
$profile = $sdk->users()->getProfile();

// Mettre à jour le profil
$sdk->users()->updateProfile([
    'name' => 'Nouveau nom',
    'email' => 'nouveau@email.com'
]);

// Lister les utilisateurs
$users = $sdk->users()->getUsers(['limit' => 10]);
```

### Gestion des erreurs

```php
try {
    $result = $sdk->users()->getProfile();
} catch (\MonSDK\SDKException $e) {
    if ($e->getStatusCode() === 401) {
        // Token expiré, reconnexion nécessaire
        $sdk->auth()->login($email, $password);
    } else {
        // Autre erreur
        echo "Erreur: " . $e->getMessage();
    }
}
```

## Configuration avancée

```php
$sdk = new SDK('https://api.monservice.com');

// Configuration du timeout
$sdk->setTimeout(60);

// Configuration de l'API Key
$sdk->setApiKey('your-api-key');

// Configuration du token Bearer
$sdk->setToken('your-bearer-token');
```

## Logs

Le SDK génère automatiquement des logs dans le dossier `logs/`.

```php
$logger = $sdk->getLogger();
$logger->info('Message d\'information');
$logger->error('Message d\'erreur');
```

## Tests

```bash
composer install --dev
./vendor/bin/phpunit tests/
```

## Documentation API

Pour plus d'informations sur les endpoints disponibles, consultez la documentation de l'API.

## Support

- Issues GitHub : https://github.com/votre-compte/mon-sdk/issues
- Email : support@monentreprise.com

## Licence

MIT License

# .gitignore
vendor/
logs/
.env
composer.lock
.phpunit.result.cache

# example.php - Exemple d'utilisation complète
<?php
require_once 'vendor/autoload.php';

use MonSDK\SDK;
use MonSDK\SDKException;

// Configuration
$apiUrl = 'https://api.monservice.com';
$apiKey = 'votre-api-key';

try {
    // Initialisation du SDK
    $sdk = new SDK($apiUrl, $apiKey);
    
    // Test de connectivité
    if (!$sdk->ping()) {
        throw new Exception("Impossible de se connecter à l'API");
    }
    
    echo "✅ Connexion à l'API réussie\n";
    
    // Authentification
    $email = 'user@example.com';
    $password = 'password123';
    
    $authResponse = $sdk->auth()->login($email, $password);
    echo "✅ Authentification réussie\n";
    
    // Récupération du profil
    $profile = $sdk->users()->getProfile();
    echo "✅ Profil récupéré: " . $profile['name'] . "\n";
    
    // Mise à jour du profil
    $updateData = [
        'name' => 'Nom mis à jour',
        'phone' => '+33123456789'
    ];
    
    $sdk->users()->updateProfile($updateData);
    echo "✅ Profil mis à jour\n";
    
    // Récupération de la liste des utilisateurs
    $users = $sdk->users()->getUsers(['limit' => 5]);
    echo "✅ " . count($users['data']) . " utilisateurs récupérés\n";
    
    // Déconnexion
    $sdk->auth()->logout();
    echo "✅ Déconnexion réussie\n";
    
} catch (SDKException $e) {
    echo "❌ Erreur SDK: " . $e->getMessage() . "\n";
    if ($e->getStatusCode()) {
        echo "Code HTTP: " . $e->getStatusCode() . "\n";
    }
} catch (Exception $e) {
    echo "❌ Erreur générale: " . $e->getMessage() . "\n";
}

# tests/SDKTest.php - Test unitaire de base
<?php

namespace MonSDK\Tests;

use PHPUnit\Framework\TestCase;
use MonSDK\SDK;
use MonSDK\SDKException;

class SDKTest extends TestCase
{
    private $sdk;

    protected function setUp(): void
    {
        $this->sdk = new SDK('https://api.example.com', 'test-api-key');
    }

    public function testSDKInitialization()
    {
        $this->assertInstanceOf(SDK::class, $this->sdk);
    }

    public function testAuthServiceExists()
    {
        $auth = $this->sdk->auth();
        $this->assertInstanceOf(\MonSDK\AuthService::class, $auth);
    }

    public function testUserServiceExists()
    {
        $users = $this->sdk->users();
        $this->assertInstanceOf(\MonSDK\UserService::class, $users);
    }

    public function testConfigurationMethods()
    {
        $this->sdk->setApiKey('new-key');
        $this->sdk->setToken('new-token');
        $this->sdk->setTimeout(60);
        
        $this->assertTrue(true); // Test basique de configuration
    }
}
