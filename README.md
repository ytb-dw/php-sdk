# YtbDw PHP SDK

[![Version](https://img.shields.io/badge/version-1.0.0-blue.svg)](https://github.com/ytb-dw/ytbdw-php-sdk)
[![PHP](https://img.shields.io/badge/php-%5E7.4-blue.svg)](https://php.net/)
[![License](https://img.shields.io/badge/license-MIT-green.svg)](LICENSE)

SDK PHP officiel pour l'API YouTube Downloader. Téléchargez facilement des vidéos et audios YouTube dans vos applications PHP.

## 🚀 Fonctionnalités

- ✅ Téléchargement de vidéos YouTube en MP4
- ✅ Téléchargement audio en MP3
- ✅ Récupération des métadonnées des vidéos
- ✅ Gestion avancée des erreurs avec messages conviviaux
- ✅ Support de différentes qualités (audio et vidéo)
- ✅ Interface simple et intuitive
- ✅ Timeout configurables
- ✅ Headers HTTP personnalisables

## 📋 Prérequis

- PHP 7.4 ou supérieur
- Extension `json` activée
- Extension `openssl` pour les requêtes HTTPS
- Une clé API valide de YtbDw

## 📦 Installation

### Via Composer (recommandé)

```bash
composer require ytbdw/php-sdk
```

### Installation manuelle

1. Téléchargez le fichier `YtbDwClient.php`
2. Incluez-le dans votre projet :

```php
require_once 'path/to/YtbDwClient.php';
```

## 🔧 Configuration

### Obtenir une clé API

1. Rendez-vous sur [https://ytb-dw.social-networking.me](https://ytb-dw.social-networking.me)
2. Créez un compte ou connectez-vous
3. Générez votre clé API dans votre tableau de bord
4. Copiez votre clé API (format : `ytb-dw-votre-cle-api`)

### Initialisation du client

```php
use YtbDw\YtbDwClient;
use YtbDw\YtbDwException;

// Configuration de base
$client = new YtbDwClient('ytb-dw-votre-cle-api');

// Configuration avancée
$client = new YtbDwClient(
    'ytb-dw-votre-cle-api',        // Clé API
    'https://ytb-dw-api.onrender.com', // URL de base (optionnel)
    60                              // Timeout en secondes (optionnel)
);
```

## 🎯 Utilisation

### Récupération des informations d'une vidéo

```php
try {
    $videoInfo = $client->getVideoInfo('https://youtube.com/watch?v=dQw4w9WgXcQ');
    
    echo "Titre: " . $videoInfo->getTitle() . "\n";
    echo "Durée: " . $videoInfo->getDuration() . "\n";
    echo "Auteur: " . $videoInfo->getUploader() . "\n";
    
    // Vérifier les formats disponibles
    if ($videoInfo->hasAudioFormats()) {
        echo "Formats audio disponibles\n";
    }
    
    if ($videoInfo->hasVideoFormats()) {
        echo "Formats vidéo disponibles\n";
    }
    
} catch (YtbDwException $e) {
    echo "Erreur: " . $e->getFriendlyMessage() . "\n";
}
```

### Téléchargement audio (MP3)

```php
try {
    // Téléchargement audio simple
    $result = $client->downloadAudio('https://youtube.com/watch?v=dQw4w9WgXcQ');
    
    echo "Fichier téléchargé: " . $result->getFilePath() . "\n";
    echo "Taille: " . $result->getFileSizeFormatted() . "\n";
    
    // Avec qualité et nom de fichier personnalisés
    $result = $client->downloadAudio(
        'https://youtube.com/watch?v=dQw4w9WgXcQ',
        '192kbps',           // Qualité audio
        'ma_musique.mp3'     // Nom du fichier
    );
    
} catch (YtbDwException $e) {
    echo "Erreur: " . $e->getFriendlyMessage() . "\n";
}
```

### Téléchargement vidéo (MP4)

```php
try {
    // Téléchargement vidéo simple (720p par défaut)
    $result = $client->downloadVideo('https://youtube.com/watch?v=dQw4w9WgXcQ');
    
    echo "Fichier téléchargé: " . $result->getFilePath() . "\n";
    echo "Taille: " . $result->getFileSizeFormatted() . "\n";
    
    // Avec qualité et nom personnalisés
    $result = $client->downloadVideo(
        'https://youtube.com/watch?v=dQw4w9WgXcQ',
        '1080',              // Qualité vidéo
        'ma_video.mp4'       // Nom du fichier
    );
    
} catch (YtbDwException $e) {
    echo "Erreur: " . $e->getFriendlyMessage() . "\n";
}
```

### Téléchargement générique

```php
try {
    // Méthode générique pour plus de contrôle
    $result = $client->download(
        'https://youtube.com/watch?v=dQw4w9WgXcQ',
        'video',             // Format: 'video' ou 'audio'
        '720',               // Qualité
        'mon_fichier.mp4'    // Chemin de sortie
    );
    
    // Vérification du type de fichier
    if ($result->isVideo()) {
        echo "Vidéo téléchargée avec succès\n";
    } elseif ($result->isAudio()) {
        echo "Audio téléchargé avec succès\n";
    }
    
} catch (YtbDwException $e) {
    echo "Erreur: " . $e->getFriendlyMessage() . "\n";
}
```

## 📊 Classes et méthodes

### YtbDwClient

#### Méthodes principales

| Méthode | Description | Paramètres |
|---------|-------------|------------|
| `getVideoInfo($url)` | Récupère les métadonnées | URL YouTube |
| `downloadAudio($url, $quality, $output)` | Télécharge l'audio | URL, qualité (opt.), fichier (opt.) |
| `downloadVideo($url, $quality, $output)` | Télécharge la vidéo | URL, qualité (opt.), fichier (opt.) |
| `download($url, $format, $quality, $output)` | Téléchargement générique | URL, format, qualité (opt.), fichier (opt.) |

### YtbDwVideoInfo

#### Propriétés accessibles

```php
$videoInfo->getTitle();        // Titre de la vidéo
$videoInfo->getDuration();     // Durée
$videoInfo->getUploader();     // Nom de l'auteur
$videoInfo->getFormats();      // Formats disponibles
$videoInfo->hasAudioFormats(); // Formats audio disponibles?
$videoInfo->hasVideoFormats(); // Formats vidéo disponibles?
$videoInfo->getRawData();      // Données brutes
```

### YtbDwDownloadResult

#### Informations sur le téléchargement

```php
$result->getFilePath();           // Chemin du fichier
$result->getFileSize();           // Taille en bytes
$result->getFileSizeFormatted();  // Taille formatée (ex: "15.2 MB")
$result->getFormat();             // Format ('audio' ou 'video')
$result->isAudio();               // Est un fichier audio?
$result->isVideo();               // Est un fichier vidéo?
```

## ⚠️ Gestion d'erreurs

Le SDK utilise des exceptions personnalisées avec des messages conviviaux :

```php
try {
    $result = $client->downloadVideo($url);
} catch (YtbDwException $e) {
    $errorCode = $e->getCode();
    
    switch ($errorCode) {
        case 400:
            echo "Paramètres invalides";
            break;
        case 401:
            echo "Clé API invalide";
            break;
        case 403:
            echo "Quota dépassé";
            break;
        case 404:
            echo "Vidéo non trouvée";
            break;
        case 429:
            echo "Trop de requêtes";
            break;
        default:
            echo "Erreur: " . $e->getFriendlyMessage();
    }
}
```

## 🔧 Qualités supportées

### Audio
- `128kbps` - Qualité standard
- `192kbps` - Bonne qualité
- `320kbps` - Haute qualité

### Vidéo
- `360` - Résolution 360p
- `480` - Résolution 480p
- `720` - Résolution 720p HD
- `1080` - Résolution 1080p Full HD

## 📝 Exemple complet

```php
<?php

require_once 'YtbDwClient.php';

use YtbDw\YtbDwClient;
use YtbDw\YtbDwException;

try {
    // Initialisation
    $client = new YtbDwClient('ytb-dw-votre-cle-api');
    $youtubeUrl = 'https://youtube.com/watch?v=dQw4w9WgXcQ';
    
    // 1. Récupération des informations
    echo "📋 Récupération des informations...\n";
    $videoInfo = $client->getVideoInfo($youtubeUrl);
    
    echo "🎬 Titre: " . $videoInfo->getTitle() . "\n";
    echo "⏱️ Durée: " . $videoInfo->getDuration() . "\n";
    echo "👤 Auteur: " . $videoInfo->getUploader() . "\n\n";
    
    // 2. Téléchargement audio
    if ($videoInfo->hasAudioFormats()) {
        echo "🎵 Téléchargement audio...\n";
        $audioResult = $client->downloadAudio($youtubeUrl, '192kbps');
        echo "✅ Audio sauvé: " . $audioResult->getFilePath();
        echo " (" . $audioResult->getFileSizeFormatted() . ")\n\n";
    }
    
    // 3. Téléchargement vidéo
    if ($videoInfo->hasVideoFormats()) {
        echo "🎥 Téléchargement vidéo...\n";
        $videoResult = $client->downloadVideo($youtubeUrl, '720');
        echo "✅ Vidéo sauvée: " . $videoResult->getFilePath();
        echo " (" . $videoResult->getFileSizeFormatted() . ")\n";
    }
    
    echo "\n🎉 Téléchargements terminés avec succès!\n";
    
} catch (YtbDwException $e) {
    echo "❌ Erreur API: " . $e->getFriendlyMessage() . "\n";
} catch (Exception $e) {
    echo "❌ Erreur système: " . $e->getMessage() . "\n";
}
?>
```

## 🚦 Limites et recommandations

### Limites de l'API
- **Quota quotidien** : Varie selon votre plan d'abonnement
- **Taille maximale** : Dépend de votre compte
- **Timeout** : 30 secondes par défaut (configurable)

### Bonnes pratiques
- ✅ Toujours gérer les exceptions
- ✅ Vérifier les informations avant téléchargement
- ✅ Utiliser des timeouts appropriés pour les gros fichiers
- ✅ Respecter les droits d'auteur et conditions d'utilisation de YouTube
- ❌ Ne pas abuser de l'API (respecter les quotas)

## 🔒 Sécurité

- **Clé API** : Ne jamais exposer votre clé API dans le code source public
- **Variables d'environnement** : Utilisez des fichiers `.env` ou variables d'environnement
- **HTTPS** : Toutes les requêtes sont chiffrées via HTTPS

```php
// Bonne pratique : utiliser les variables d'environnement
$apiKey = $_ENV['YTBDW_API_KEY'] ?? getenv('YTBDW_API_KEY');
$client = new YtbDwClient($apiKey);
```

## 🆘 Support et contribution

### Signaler un bug
- Ouvrez une [issue sur GitHub](https://github.com/ytb-dw/ytbdw-php-sdk/issues)
- Décrivez le problème avec un exemple de code

### Contribuer
1. Fork le projet
2. Créez une branche pour votre fonctionnalité
3. Committez vos changements
4. Ouvrez une Pull Request

### Support commercial
Pour un support premium, contactez-nous à : support@ytbdw.com

## 📄 Licence

Ce projet est sous licence MIT. Voir le fichier [LICENSE](LICENSE) pour plus de détails.

## 🔗 Liens utiles

- [Documentation API officielle](https://ytb-dw.social-networking.me/docs.php)
- [Tableau de bord développeur](https://ytb-dw.social-networking.me/dashboard.php)
- [Status de l'API](https://ytb-dw.social-networking.me/)
- [Support client](https://ytb-dw.social-networking.me)

---

**⚖️ Avertissement légal** : Ce SDK est destiné à un usage personnel et éducatif. Respectez les conditions d'utilisation de YouTube et les droits d'auteur. L'utilisation commerciale nécessite une licence appropriée.
