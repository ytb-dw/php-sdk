<?php
/**
 * Exemple d'utilisation complète du SDK YTB-DW PHP
 */

require_once 'vendor/autoload.php';
// Ou si vous n'utilisez pas Composer:
// require_once 'src/YtbDwClient.php';

use YtbDw\YtbDwClient;
use YtbDw\YtbDwException;

// Configuration
$apiKey = 'ytb-dw-votre-cle-api'; // Remplacez par votre vraie clé API
$youtubeUrl = 'https://youtube.com/watch?v=dQw4w9WgXcQ';

try {
    // Initialiser le client SDK
    $client = new YtbDwClient($apiKey);
    
    echo "🔍 Récupération des informations de la vidéo...\n";
    echo "URL: $youtubeUrl\n\n";
    
    // Récupérer les métadonnées de la vidéo
    $videoInfo = $client->getVideoInfo($youtubeUrl);
    
    echo "📺 Informations de la vidéo:\n";
    echo "├── Titre: " . $videoInfo->getTitle() . "\n";
    echo "├── Durée: " . $videoInfo->getDuration() . "\n";
    echo "├── Auteur: " . $videoInfo->getUploader() . "\n";
    echo "├── Formats audio disponibles: " . ($videoInfo->hasAudioFormats() ? "✅ Oui" : "❌ Non") . "\n";
    echo "└── Formats vidéo disponibles: " . ($videoInfo->hasVideoFormats() ? "✅ Oui" : "❌ Non") . "\n\n";
    
    // Afficher tous les formats disponibles
    echo "📋 Formats disponibles:\n";
    foreach ($videoInfo->getFormats() as $index => $format) {
        echo "├── " . ($index + 1) . ". " . ($format['type'] ?? 'Type inconnu');
        if (isset($format['quality'])) {
            echo " (" . $format['quality'] . ")";
        }
        echo "\n";
    }
    echo "\n";
    
    // Télécharger en audio MP3
    if ($videoInfo->hasAudioFormats()) {
        echo "🎵 Téléchargement audio en cours...\n";
        $audioResult = $client->downloadAudio($youtubeUrl, '192kbps', 'downloads/audio.mp3');
        echo "✅ Audio téléchargé avec succès!\n";
        echo "├── Fichier: " . $audioResult->getFilePath() . "\n";
        echo "├── Taille: " . $audioResult->getFileSizeFormatted() . "\n";
        echo "└── Format: " . ($audioResult->isAudio() ? "🎵 Audio" : "🎬 Vidéo") . "\n\n";
    }
    
    // Télécharger en vidéo MP4
    if ($videoInfo->hasVideoFormats()) {
        echo "🎬 Téléchargement vidéo en cours...\n";
        $videoResult = $client->downloadVideo($youtubeUrl, '720', 'downloads/video.mp4');
        echo "✅ Vidéo téléchargée avec succès!\n";
        echo "├── Fichier: " . $videoResult->getFilePath() . "\n";
        echo "├── Taille: " . $videoResult->getFileSizeFormatted() . "\n";
        echo "└── Format: " . ($videoResult->isVideo() ? "🎬 Vidéo" : "🎵 Audio") . "\n\n";
    }
    
    echo "🎉 Tous les téléchargements terminés!\n";
    
} catch (YtbDwException $e) {
    echo "❌ Erreur API (Code " . $e->getCode() . "):\n";
    echo "Message technique: " . $e->getMessage() . "\n";
    echo "Description: " . $e->getFriendlyMessage() . "\n\n";
    
    // Gestion spécifique des erreurs courantes
    switch ($e->getCode()) {
        case 401:
            echo "💡 Solution: Vérifiez que votre clé API '$apiKey' est correcte.\n";
            break;
        case 404:
            echo "💡 Solution: Vérifiez que l'URL YouTube '$youtubeUrl' est valide et accessible.\n";
            break;
        case 429:
            echo "💡 Solution: Attendez ou upgradez votre compte pour avoir plus de quota.\n";
            break;
    }
    
} catch (Exception $e) {
    echo "❌ Erreur générale: " . $e->getMessage() . "\n";
}

// Exemple d'utilisation avancée avec gestion des erreurs spécifiques
echo "\n" . str_repeat("=", 60) . "\n";
echo "Exemple avancé avec gestion d'erreurs spécifiques\n";
echo str_repeat("=", 60) . "\n\n";

function downloadWithRetry(YtbDwClient $client, string $url, string $format = 'audio', int $maxRetries = 3): ?YtbDw\YtbDwDownloadResult 
{
    $attempts = 0;
    
    while ($attempts < $maxRetries) {
        try {
            $attempts++;
            echo "🔄 Tentative $attempts/$maxRetries...\n";
            
            if ($format === 'audio') {
                return $client->downloadAudio($url);
            } else {
                return $client->downloadVideo($url);
            }
            
        } catch (YtbDwException $e) {
            echo "❌ Échec de la tentative $attempts: " . $e->getFriendlyMessage() . "\n";
            
            // Ne pas retry si c'est une erreur de clé API ou d'URL invalide
            if (in_array($e->getCode(), [401, 404])) {
                throw $e;
            }
            
            // Attendre avant le prochain essai
            if ($attempts < $maxRetries) {
                $waitTime = $attempts * 2; // Backoff progressif
                echo "⏳ Attente de {$waitTime}s avant le prochain essai...\n\n";
                sleep($waitTime);
            }
        }
    }
    
    throw new YtbDwException("Échec après $maxRetries tentatives");
}

// Test de la fonction avec retry
try {
    $client2 = new YtbDwClient($apiKey);
    $result = downloadWithRetry($client2, $youtubeUrl, 'audio', 3);
    echo "✅ Téléchargement réussi avec retry: " . $result->getFilePath() . "\n";
} catch (YtbDwException $e) {
    echo "❌ Échec final: " . $e->getFriendlyMessage() . "\n";
}

// Exemple de traitement par lot
echo "\n" . str_repeat("=", 60) . "\n";
echo "Exemple de traitement par lot\n";
echo str_repeat("=", 60) . "\n\n";

$urlsToBatch = [
    'https://youtube.com/watch?v=dQw4w9WgXcQ',
    'https://youtube.com/watch?v=fJ9rUzIMcZQ',
    // Ajoutez d'autres URLs ici
];

function batchDownload(YtbDwClient $client, array $urls, string $format = 'audio'): array 
{
    $results = [];
    
    foreach ($urls as $index => $url) {
        echo "📥 Traitement " . ($index + 1) . "/" . count($urls) . ": $url\n";
        
        try {
            $videoInfo = $client->getVideoInfo($url);
            echo "├── Titre: " . $videoInfo->getTitle() . "\n";
            
            $filename = "batch_" . ($index + 1) . "_" . preg_replace('/[^a-zA-Z0-9]/', '_', $videoInfo->getTitle() ?? 'video');
            $filename .= $format === 'audio' ? '.mp3' : '.mp4';
            
            $downloadResult = $client->download($url, $format, null, "downloads/batch/$filename");
            
            $results[] = [
                'url' => $url,
                'success' => true,
                'file' => $downloadResult->getFilePath(),
                'size' => $downloadResult->getFileSizeFormatted(),
                'title' => $videoInfo->getTitle()
            ];
            
            echo "└── ✅ Téléchargé: " . $downloadResult->getFilePath() . " (" . $downloadResult->getFileSizeFormatted() . ")\n\n";
            
            // Pause entre les téléchargements pour respecter les limites
            sleep(1);
            
        } catch (YtbDwException $e) {
            echo "└── ❌ Erreur: " . $e->getFriendlyMessage() . "\n\n";
            
            $results[] = [
                'url' => $url,
                'success' => false,
                'error' => $e->getFriendlyMessage(),
                'code' => $e->getCode()
            ];
            
            // Arrêter le traitement par lot si quota dépassé
            if ($e->getCode() === 429) {
                echo "🛑 Quota dépassé, arrêt du traitement par lot.\n";
                break;
            }
        }
    }
    
    return $results;
}

try {
    $client3 = new YtbDwClient($apiKey);
    $batchResults = batchDownload($client3, $urlsToBatch, 'audio');
    
    echo "📊 Résumé du traitement par lot:\n";
    $successful = array_filter($batchResults, fn($r) => $r['success']);
    $failed = array_filter($batchResults, fn($r) => !$r['success']);
    
    echo "├── Réussites: " . count($successful) . "/" . count($batchResults) . "\n";
    echo "└── Échecs: " . count($failed) . "/" . count($batchResults) . "\n";
    
} catch (Exception $e) {
    echo "❌ Erreur lors du traitement par lot: " . $e->getMessage() . "\n";
}

?>
