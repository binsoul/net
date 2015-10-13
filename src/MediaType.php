<?php

namespace BinSoul\Net;

/**
 * Represents a media type according to RFC 6838.
 */
class MediaType
{
    /** restricted name characters */
    const NAME = '[a-z0-9][a-z0-9\!\#\$\&\-\^\_\.]*';
    /** Regex which matches a media type string */
    const REGEX = '#^(?<type>'.self::NAME.')/(?<subtype>'.self::NAME.')(?<suffix>\+[a-z]+)?(?<parameters>;[^;]*)?$#i';

    /**
     * Map of some common media types.
     *
     * @var string[]
     */
    private static $knownTypes = [
        '7z' => 'application/x-7z-compressed', // 7-Zip
        'swf' => 'application/x-shockwave-flash', // Adobe Flash
        'pdf' => 'application/pdf', // Adobe Portable Document Format
        'aac' => 'audio/x-aac', // Advanced Audio Coding (AAC)
        'atom' => 'application/atom+xml', // Atom Syndication Format
        'aif' => 'audio/x-aiff', // Audio Interchange File Format
        'bmp' => 'image/bmp', // Bitmap Image File
        'torrent' => 'application/x-bittorrent', // BitTorrent
        'sh' => 'application/x-sh', // Bourne Shell Script
        'bz' => 'application/x-bzip', // Bzip Archive
        'css' => 'text/css', // Cascading Style Sheets (CSS)
        'csv' => 'text/csv', // Comma-Seperated Values
        'dtd' => 'application/xml-dtd', // Document Type Definition
        'eml' => 'message/rfc822', // Email Message
        'f4v' => 'video/x-f4v', // Flash Video
        'flv' => 'video/x-flv', // Flash Video
        'gif' => 'image/gif', // Graphics Interchange Format
        'h264' => 'video/h264', // H.264
        'html' => 'text/html', // HyperText Markup Language (HTML)
        'ics' => 'text/calendar', // iCalendar
        'ico' => 'image/x-icon', // Icon Image
        'js' => 'application/javascript', // JavaScript
        'json' => 'application/json', // JavaScript Object Notation (JSON)
        'jpg' => 'image/jpeg', // JPEG Image
        'm3u' => 'audio/x-mpegurl', // M3U (Multimedia Playlist)
        'm4v' => 'video/x-m4v', // M4v
        'mdb' => 'application/x-msaccess', // Microsoft Access
        'asf' => 'video/x-ms-asf', // Microsoft Advanced Systems Format (ASF)
        'exe' => 'application/x-msdownload', // Microsoft Application
        'cab' => 'application/vnd.ms-cab-compressed', // Microsoft Cabinet File
        'eot' => 'application/vnd.ms-fontobject', // Microsoft Embedded OpenType
        'xls' => 'application/vnd.ms-excel', // Microsoft Excel
        'chm' => 'application/vnd.ms-htmlhelp', // Microsoft Html Help File
        'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation', // Ms Office Presentation
        'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', // Ms Office - Spreadsheet
        'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', // Ms Office Word
        'ppt' => 'application/vnd.ms-powerpoint', // Microsoft PowerPoint
        'mpp' => 'application/vnd.ms-project', // Microsoft Project
        'pub' => 'application/x-mspublisher', // Microsoft Publisher
        'xap' => 'application/x-silverlight-app', // Microsoft Silverlight
        'vsd' => 'application/vnd.visio', // Microsoft Visio
        'wma' => 'audio/x-ms-wma', // Microsoft Windows Media Audio
        'wmv' => 'video/x-ms-wmv', // Microsoft Windows Media Video
        'doc' => 'application/msword', // Microsoft Word
        'xps' => 'application/vnd.ms-xpsdocument', // Microsoft XML Paper Specification
        'mid' => 'audio/midi', // MIDI - Musical Instrument Digital Interface
        'mpga' => 'audio/mpeg', // MPEG Audio
        'mpeg' => 'video/mpeg', // MPEG Video
        'mp4a' => 'audio/mp4', // MPEG-4 Audio
        'mp4' => 'video/mp4', // MPEG-4 Video
        'oga' => 'audio/ogg', // Ogg Audio
        'ogv' => 'video/ogg', // Ogg Video
        'weba' => 'audio/webm', // Open Web Media Project - Audio
        'webm' => 'video/webm', // Open Web Media Project - Video
        'odc' => 'application/vnd.oasis.opendocument.chart', // OpenDocument Chart
        'odb' => 'application/vnd.oasis.opendocument.database', // OpenDocument Database
        'odf' => 'application/vnd.oasis.opendocument.formula', // OpenDocument Formula
        'odg' => 'application/vnd.oasis.opendocument.graphics', // OpenDocument Graphics
        'odi' => 'application/vnd.oasis.opendocument.image', // OpenDocument Image
        'odp' => 'application/vnd.oasis.opendocument.presentation', // OpenDocument Presentation
        'ods' => 'application/vnd.oasis.opendocument.spreadsheet', // OpenDocument Spreadsheet
        'odt' => 'application/vnd.oasis.opendocument.text', // OpenDocument Text
        'otf' => 'application/x-font-otf', // OpenType Font File
        'psd' => 'image/vnd.adobe.photoshop', // Photoshop Document
        'ai' => 'application/postscript', // PostScript
        'pgp' => 'application/pgp-signature', // Pretty Good Privacy - Signature
        'rar' => 'application/x-rar-compressed', // RAR Archive
        'ram' => 'audio/x-pn-realaudio', // Real Audio Sound
        'rm' => 'application/vnd.rn-realmedia', // RealMedia
        'rtf' => 'application/rtf', // Rich Text Format
        'rss' => 'application/rss+xml', // RSS - Really Simple Syndication
        'svg' => 'image/svg+xml', // Scalable Vector Graphics (SVG)
        'tiff' => 'image/tiff', // Tagged Image File Format
        'tar' => 'application/x-tar', // Tar File (Tape Archive)
        'txt' => 'text/plain', // Text File
        'ttf' => 'application/x-font-ttf', // TrueType Font
        'vcs' => 'text/x-vcalendar', // vCalendar
        'vcf' => 'text/x-vcard', // vCard
        'wav' => 'audio/x-wav', // Waveform Audio File Format (WAV)
        'woff' => 'application/x-font-woff', // Web Open Font Format
        'webp' => 'image/webp', // WebP Image
        'xhtml' => 'application/xhtml+xml', // XHTML - The Extensible HyperText Markup Language
        'xml' => 'application/xml', // XML - Extensible Markup Language
        'xslt' => 'application/xslt+xml', // XML Transformations
        'yaml' => 'text/yaml', // YAML Ain't Markup Language / Yet Another Markup Language
        'zip' => 'application/zip', // Zip Archive
    ];

    /** @var string */
    private $type;
    /** @var string */
    private $subType;
    /** @var string */
    private $parameters;

    /**
     * Constructs an instance of this class.
     *
     * @param string $mediaType
     *
     * @throws \InvalidArgumentException
     */
    public function __construct($mediaType)
    {
        if (!preg_match(self::REGEX, trim($mediaType), $matches)) {
            throw new \InvalidArgumentException(sprintf('"%s" is not a valid media type.', $mediaType));
        }

        $this->type = strtolower($matches['type']);
        $this->subType = strtolower($matches['subtype'].(isset($matches['suffix']) ? $matches['suffix'] : ''));
        $this->parameters = isset($matches['parameters']) ? substr($matches['parameters'], 1) : '';
    }

    /**
     * Returns an string representation of the media type.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->type.'/'.$this->subType.($this->parameters != '' ? ';'.$this->parameters : '');
    }

    /**
     * Checks if the given string is a valid media type.
     *
     * @param string $mediaType
     *
     * @return bool
     */
    public static function isValid($mediaType)
    {
        return (bool) preg_match(self::REGEX, trim($mediaType));
    }

    /**
     * Checks if the given extension is known.
     *
     * @param string $extension
     *
     * @return bool
     */
    public static function isKnownExtension($extension)
    {
        return isset(self::$knownTypes[strtolower($extension)]);
    }

    /**
     * Constructs a new media type object from the given extension.
     *
     * @param string $extension
     *
     * @throws \InvalidArgumentException if extension is unknown
     *
     * @return MediaType
     */
    public static function fromExtension($extension)
    {
        if (!self::isKnownExtension($extension)) {
            throw new \InvalidArgumentException(sprintf('Unknown extension "%s" given.', $extension));
        }

        return new self(self::$knownTypes[strtolower($extension)]);
    }

    /**
     * Checks if the given media type is a generic type like application/octet-stream.
     *
     * @param string $mediaType
     *
     * @return bool
     */
    private static function isFallback($mediaType)
    {
        return strpos($mediaType, 'application/octet-stream') !== false || strpos($mediaType, 'text/plain') !== false;
    }

    /**
     * Checks if the given command is callable on the command line.
     *
     * @param string $command
     *
     * @return bool
     */
    private static function isCommandAvailable($command)
    {
        if (!is_callable('shell_exec') || stripos(ini_get('disable_functions'), 'shell_exec') !== false) {
            return false;
        }

        $result = null;
        if (strtoupper(substr(PHP_OS, 0, 3)) != 'WIN') {
            $result = @shell_exec('which '.$command);
        }

        return !empty($result);
    }

    /**
     * Uses the unix "file" command to guess the media type.
     *
     * @param string $filename
     *
     * @return string
     */
    private static function guessFromFileCommand($filename)
    {
        $result = 'application/octet-stream';

        if (!self::isCommandAvailable('file')) {
            return $result;
        }

        $info = trim(shell_exec('file -bi '.escapeshellarg($filename)));

        return $info != '' ? $info : $result;
    }

    /**
     * Uses finfo_file to guess the media type.
     *
     * @param string $filename
     *
     * @return string
     */
    private static function guessFromFinfo($filename)
    {
        $result = 'application/octet-stream';

        if (!function_exists('finfo_file')) {
            return $result;
        }

        $handle = finfo_open(FILEINFO_MIME);
        if ($handle === false) {
            return $result;
        }

        $info = finfo_file($handle, $filename);
        finfo_close($handle);

        return $info !== false ? $info : $result;
    }

    /**
     * Constructs a new media type object for the given file name.
     *
     * @param string $filename
     * @param bool   $allowGuessByExtension
     *
     * @return string
     */
    public static function fromFile($filename, $allowGuessByExtension)
    {
        $result = 'application/octet-stream';

        if (!@is_file($filename) || !@is_readable($filename)) {
            return new self($result);
        }

        $result = self::guessFromFinfo($filename);
        if (self::isFallback($result)) {
            $result = self::guessFromFileCommand($filename);
        }

        if ($allowGuessByExtension && self::isFallback($result)) {
            $extension = pathinfo($filename, PATHINFO_EXTENSION);
            if (self::isKnownExtension($extension)) {
                return self::fromExtension($extension);
            }
        }

        return new self($result);
    }
}
