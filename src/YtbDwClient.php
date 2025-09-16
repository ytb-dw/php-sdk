<?php

namespace YtbDw;

use Exception;

/**
 * SDK PHP pour l'API YouTube Downloader
 * 
 * @package YtbDw
 * @version 1.0.0
 */
class YtbDwClient
{
    private string $apiKey;
    private string $baseUrl;
    private int $timeout;
    private array $defaultHeaders;

    /**
     * Constructeur du client SDK
     *
     * @param string $apiKey Votre clé API
     * @param string $baseUrl URL de base de l'API (optionnel)
     * @param int $timeout Timeout des requêtes en secondes (optionnel)
     */
    public function __construct(string $apiKey, string $baseUrl = 'https://ytb-dw-api.onrender.com', int $timeout = 30)
    {
        $this->apiKey = $apiKey;
        $this->baseUrl = rtrim($baseUrl, '/');
        $this->timeout = $timeout;
        $this->defaultHeaders = [
            'User-Agent: YtbDw-PHP-SDK/1.0.0',
            'Accept: application/json, */*',
        ];
    }

    /**
     * Télécharge une vidéo YouTube
     *
     * @param string $youtubeUrl URL de la vidéo YouTube
     * @param string $format Format de téléchargement ('audio' ou 'video')
     * @param string|null $quality Qualité (ex: '720', '480', '192kbps')
     * @param string|null $outputPath Chemin de sauvegarde du fichier
     * @return YtbDwDownloadResult
     * @throws YtbDwException
     */
    public function download(string $youtubeUrl, string $format = 'video', ?string $quality = null, ?string $outputPath = null): YtbDwDownloadResult
    {
        $params = [
            'api_key' => $this->apiKey,
            'url' => $youtubeUrl,
            'format' => $format
        ];

        if ($quality) {
            $params['quality'] = $quality;
        }

        $downloadUrl = $this->baseUrl . '/download.php?' . http_build_query($params);

        $context = stream_context_create([
            'http' => [
                'method' => 'GET',
                'timeout' => $this->timeout,
                'header' => implode("\r\n", $this->defaultHeaders)
            ]
        ]);

        $response = file_get_contents($downloadUrl, false, $context);
        
        if ($response === false) {
            $error = error_get_last();
            throw new YtbDwException("Erreur lors du téléchargement: " . ($error['message'] ?? 'Erreur inconnue'));
        }

        // Vérifier si la réponse est une erreur JSON
        $jsonResponse = json_decode($response, true);
        if ($jsonResponse && isset($jsonResponse['error'])) {
            throw new YtbDwException($jsonResponse['error'], $jsonResponse['code'] ?? 0);
        }

        // Générer un nom de fichier par défaut si non spécifié
        if (!$outputPath) {
            $extension = $format === 'audio' ? 'mp3' : 'mp4';
            $outputPath = 'ytb_download_' . uniqid() . '.' . $extension;
        }

        // Sauvegarder le fichier
        $saved = file_put_contents($outputPath, $response);
        
        if ($saved === false) {
            throw new YtbDwException("Impossible de sauvegarder le fichier à: $outputPath");
        }

        return new YtbDwDownloadResult($outputPath, $saved, $format);
    }

    /**
     * Récupère les métadonnées d'une vidéo sans la télécharger
     *
     * @param string $youtubeUrl URL de la vidéo YouTube
     * @return YtbDwVideoInfo
     * @throws YtbDwException
     */
    public function getVideoInfo(string $youtubeUrl): YtbDwVideoInfo
    {
        $params = [
            'api_key' => $this->apiKey,
            'url' => $youtubeUrl
        ];

        $infoUrl = $this->baseUrl . '/video_info.php?' . http_build_query($params);

        $context = stream_context_create([
            'http' => [
                'method' => 'GET',
                'timeout' => $this->timeout,
                'header' => implode("\r\n", $this->defaultHeaders)
            ]
        ]);

        $response = file_get_contents($infoUrl, false, $context);
        
        if ($response === false) {
            $error = error_get_last();
            throw new YtbDwException("Erreur lors de la récupération des informations: " . ($error['message'] ?? 'Erreur inconnue'));
        }

        $data = json_decode($response, true);
        
        if (!$data) {
            throw new YtbDwException("Réponse JSON invalide");
        }

        if (!$data['success']) {
            throw new YtbDwException($data['error'] ?? 'Erreur inconnue', $data['code'] ?? 0);
        }

        return new YtbDwVideoInfo($data);
    }

    /**
     * Télécharge en audio MP3
     *
     * @param string $youtubeUrl URL de la vidéo YouTube
     * @param string|null $quality Qualité audio (ex: '192kbps')
     * @param string|null $outputPath Chemin de sauvegarde
     * @return YtbDwDownloadResult
     * @throws YtbDwException
     */
    public function downloadAudio(string $youtubeUrl, ?string $quality = null, ?string $outputPath = null): YtbDwDownloadResult
    {
        return $this->download($youtubeUrl, 'audio', $quality, $outputPath);
    }

    /**
     * Télécharge en vidéo MP4
     *
     * @param string $youtubeUrl URL de la vidéo YouTube
     * @param string|null $quality Qualité vidéo (ex: '720', '1080')
     * @param string|null $outputPath Chemin de sauvegarde
     * @return YtbDwDownloadResult
     * @throws YtbDwException
     */
    public function downloadVideo(string $youtubeUrl, ?string $quality = '720', ?string $outputPath = null): YtbDwDownloadResult
    {
        return $this->download($youtubeUrl, 'video', $quality, $outputPath);
    }
}

/**
 * Classe représentant le résultat d'un téléchargement
 */
class YtbDwDownloadResult
{
    private string $filePath;
    private int $fileSize;
    private string $format;

    public function __construct(string $filePath, int $fileSize, string $format)
    {
        $this->filePath = $filePath;
        $this->fileSize = $fileSize;
        $this->format = $format;
    }

    public function getFilePath(): string
    {
        return $this->filePath;
    }

    public function getFileSize(): int
    {
        return $this->fileSize;
    }

    public function getFileSizeFormatted(): string
    {
        $bytes = $this->fileSize;
        $units = ['B', 'KB', 'MB', 'GB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }

    public function getFormat(): string
    {
        return $this->format;
    }

    public function isAudio(): bool
    {
        return $this->format === 'audio';
    }

    public function isVideo(): bool
    {
        return $this->format === 'video';
    }
}

/**
 * Classe représentant les informations d'une vidéo
 */
class YtbDwVideoInfo
{
    private array $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function getTitle(): ?string
    {
        return $this->data['title'] ?? null;
    }

    public function getDuration(): ?string
    {
        return $this->data['duration'] ?? null;
    }

    public function getUploader(): ?string
    {
        return $this->data['uploader'] ?? null;
    }

    public function getFormats(): array
    {
        return $this->data['formats'] ?? [];
    }

    public function getRawData(): array
    {
        return $this->data;
    }

    public function hasAudioFormats(): bool
    {
        foreach ($this->getFormats() as $format) {
            if (stripos($format['type'] ?? '', 'audio') !== false) {
                return true;
            }
        }
        return false;
    }

    public function hasVideoFormats(): bool
    {
        foreach ($this->getFormats() as $format) {
            if (stripos($format['type'] ?? '', 'video') !== false) {
                return true;
            }
        }
        return false;
    }
}

/**
 * Exception personnalisée pour les erreurs de l'API
 */
class YtbDwException extends Exception
{
    public function __construct(string $message = "", int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    /**
     * Retourne une description conviviale de l'erreur selon le code
     */
    public function getFriendlyMessage(): string
    {
        switch ($this->code) {
            case 400:
                return "Paramètres de requête invalides. Vérifiez l'URL YouTube et les paramètres.";
            case 401:
                return "Clé API invalide. Vérifiez votre clé API.";
            case 403:
                return "Accès interdit. Upgradez votre compte ou vérifiez vos permissions.";
            case 404:
                return "Vidéo non trouvée. Vérifiez que l'URL YouTube est correcte et que la vidéo existe.";
            case 429:
                return "Trop de requêtes. Vous avez dépassé votre quota quotidien.";
            default:
                return $this->message;
        }
    }
}

// Fichier d'exemple d'utilisation
/**
 * Exemple d'utilisation du SDK
 */
/*
require_once 'YtbDwClient.php';

use YtbDw\YtbDwClient;
use YtbDw\YtbDwException;

try {
    // Initialiser le client avec votre clé API
    $client = new YtbDwClient('ytb-dw-votre-cle-api');
    
    $youtubeUrl = 'https://youtube.com/watch?v=dQw4w9WgXcQ';
    
    // Récupérer les informations de la vidéo
    echo "Récupération des informations...\n";
    $videoInfo = $client->getVideoInfo($youtubeUrl);
    echo "Titre: " . $videoInfo->getTitle() . "\n";
    echo "Durée: " . $videoInfo->getDuration() . "\n";
    echo "Auteur: " . $videoInfo->getUploader() . "\n";
    
    // Télécharger en audio MP3
    echo "\nTéléchargement audio...\n";
    $audioResult = $client->downloadAudio($youtubeUrl, null, 'ma_musique.mp3');
    echo "Audio téléchargé: " . $audioResult->getFilePath() . " (" . $audioResult->getFileSizeFormatted() . ")\n";
    
    // Télécharger en vidéo
    echo "\nTéléchargement vidéo...\n";
    $videoResult = $client->downloadVideo($youtubeUrl, '720', 'ma_video.mp4');
    echo "Vidéo téléchargée: " . $videoResult->getFilePath() . " (" . $videoResult->getFileSizeFormatted() . ")\n";
    
} catch (YtbDwException $e) {
    echo "Erreur API: " . $e->getFriendlyMessage() . "\n";
} catch (Exception $e) {
    echo "Erreur: " . $e->getMessage() . "\n";
}
*/
?>
