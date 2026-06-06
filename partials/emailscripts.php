<?php
    require '../vendor/autoload.php'; // for PHPMailer and any PDF library

    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\Exception as MailException;
    use Smalot\PdfParser\Parser; // PDF parser
    use PhpOffice\PhpWord\IOFactory; // Word parser
    use thiagoalessio\TesseractOCR\TesseractOCR;

    $dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
    $dotenv->load();

    function addUniqueMediaItem(array &$bucket, mixed $value): void {
        $value = trim((string) $value);
        if ($value === '' || in_array($value, $bucket, true)) {
            return;
        }
        $bucket[] = $value;
    }

    function ensureDirectoryExists(string $dirPath): void {
        if (!is_dir($dirPath)) {
            mkdir($dirPath, 0777, true);
        }
    }

    function cleanupExtractedMediaFiles(?array $mediaAssets): void {
        if (!is_array($mediaAssets) || empty($mediaAssets['attachments'])) {
            return;
        }

        foreach ($mediaAssets['attachments'] as $asset) {
            if (!is_array($asset) || empty($asset['path'])) {
                continue;
            }

            $assetPath = (string) $asset['path'];
            if (is_file($assetPath)) {
                @unlink($assetPath);
            }
        }
    }

    function sanitizePathSegment(string $value): string {
        $value = preg_replace('/[^A-Za-z0-9._-]+/', '_', $value);
        return trim($value, '._-');
    }

    function isImagePath(string $path): bool {
        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        return in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'svg'], true);
    }

    function createMediaAssetStore(): array {
        return [
            'inlineImages' => [],
            'attachments' => [],
            'heroImage' => null,
        ];
    }

    function normalizeMatchingText(string $text): string {
        $text = strtolower($text);
        $text = preg_replace('/[^a-z0-9]+/', ' ', $text);
        $text = preg_replace('/\s+/', ' ', $text);
        return trim($text);
    }

    function extractMatchingKeywords(string $text): array {
        $text = normalizeMatchingText($text);
        if ($text === '') {
            return [];
        }

        $stopWords = array_fill_keys([
            'the', 'and', 'for', 'with', 'that', 'this', 'from', 'into', 'your', 'have', 'has', 'was', 'were', 'are', 'you', 'will', 'been', 'can', 'any', 'all', 'new', 'course', 'material', 'module', 'lms', 'open', 'now', 'about', 'part', 'lab', 'virtual', 'machine', 'workstation'
        ], true);

        $tokens = preg_split('/\s+/', $text, -1, PREG_SPLIT_NO_EMPTY);
        if (!is_array($tokens)) {
            return [];
        }

        $keywords = [];
        foreach ($tokens as $token) {
            $token = trim($token);
            if ($token === '' || strlen($token) < 4 || isset($stopWords[$token])) {
                continue;
            }

            $keywords[$token] = true;
        }

        return array_keys($keywords);
    }

    function getImageOcrText(string $filePath): string {
        if (!class_exists('thiagoalessio\\TesseractOCR\\TesseractOCR') || !is_file($filePath)) {
            return '';
        }

        try {
            $ocr = new TesseractOCR($filePath);
            $text = $ocr->run();
            return normalizeMatchingText($text);
        } catch (\Throwable $e) {
            return '';
        }
    }

    function scoreMediaAssetForContext(array $asset, string $contextText): int {
        if (!is_array($asset) || empty($asset['path']) || !is_file($asset['path'])) {
            return 0;
        }

        $keywords = extractMatchingKeywords($contextText);
        if (empty($keywords)) {
            return 1;
        }

        $haystacks = [
            normalizeMatchingText($asset['name'] ?? ''),
            normalizeMatchingText(basename((string) ($asset['path'] ?? ''))),
            getImageOcrText($asset['path']),
        ];

        $score = 0;
        foreach ($keywords as $keyword) {
            foreach ($haystacks as $haystack) {
                if ($haystack !== '' && strpos($haystack, $keyword) !== false) {
                    $score += 3;
                }
            }
        }

        if ($score === 0 && !empty($haystacks[2])) {
            foreach (['error', 'virtualbox', 'network', 'lab', 'machine', 'setup', 'install', 'security', 'linux', 'window'] as $hint) {
                if (strpos($haystacks[2], $hint) !== false) {
                    $score += 2;
                }
            }
        }

        $score += min(5, (int) round((int) ($asset['size'] ?? 0) / 250000));
        return $score;
    }

    function selectBestHeroImageAsset(array $mediaAssets, string $summaryText, string $moduleName, string $materialTitle, string $fileContent): ?array {
        if (!is_array($mediaAssets) || empty($mediaAssets['inlineImages'])) {
            return null;
        }

        $contextText = trim($summaryText . ' ' . $moduleName . ' ' . $materialTitle . ' ' . $fileContent);
        $bestAsset = null;
        $bestScore = 0;

        foreach ($mediaAssets['inlineImages'] as $asset) {
            $score = scoreMediaAssetForContext($asset, $contextText);
            if ($score > $bestScore) {
                $bestScore = $score;
                $bestAsset = $asset;
            }
        }

        if ($bestAsset === null) {
            return $mediaAssets['inlineImages'][0] ?? null;
        }

        return $bestAsset;
    }

    function registerExtractedMediaAsset(array &$mediaAssets, string $filePath, string $displayName, string $sourceType): void {
        $filePath = $filePath;
        $displayName = trim($displayName);
        if ($filePath === '' || !is_file($filePath)) {
            return;
        }

        $asset = [
            'path' => $filePath,
            'name' => $displayName !== '' ? $displayName : basename($filePath),
            'sourceType' => $sourceType,
            'size' => filesize($filePath) ?: 0,
        ];

        $mediaAssets['attachments'][] = $asset;

        if (isImagePath($filePath)) {
            $mediaAssets['inlineImages'][] = $asset;
            if ($mediaAssets['heroImage'] === null) {
                $mediaAssets['heroImage'] = $asset;
            }
        }
    }

    function extractEmbeddedOfficeMedia(string $filePath, string $mimeType, array &$media, array &$mediaAssets): void {
        if ($mimeType !== 'application/vnd.openxmlformats-officedocument.wordprocessingml.document') {
            return;
        }

        if (!class_exists('ZipArchive')) {
            return;
        }

        $zip = new \ZipArchive();
        if ($zip->open($filePath) !== true) {
            return;
        }

        $baseName = sanitizePathSegment(pathinfo($filePath, PATHINFO_FILENAME));
        $extractDir = dirname($filePath);
        ensureDirectoryExists($extractDir);

        $mediaPrefix = 'word/media/';

        for ($i = 0; $i < $zip->numFiles; $i++) {
            $entry = $zip->getNameIndex($i);
            if (!is_string($entry) || strpos($entry, $mediaPrefix) !== 0) {
                continue;
            }

            $entryContents = $zip->getFromIndex($i);
            if ($entryContents === false) {
                continue;
            }

            $entryName = basename($entry);
            $safeEntryName = sanitizePathSegment($entryName);
            if ($safeEntryName === '') {
                $safeEntryName = 'media_' . $i;
            }

            $targetPath = $extractDir . DIRECTORY_SEPARATOR . $baseName . '_' . $safeEntryName;
            if (file_exists($targetPath)) {
                $targetPath = $extractDir . DIRECTORY_SEPARATOR . uniqid($safeEntryName . '_', true);
            }

            if (file_put_contents($targetPath, $entryContents) === false) {
                continue;
            }

            $extension = strtolower(pathinfo($targetPath, PATHINFO_EXTENSION));
            if (isImagePath($targetPath)) {
                $kind = 'images';
            } elseif (in_array($extension, ['mp4', 'webm', 'ogv', 'wmv', 'mov'], true)) {
                $kind = 'videos';
            } elseif (in_array($extension, ['mp3', 'wav', 'ogg', 'm4a', 'aac'], true)) {
                $kind = 'audio';
            } else {
                $kind = 'images';
            }

            addUniqueMediaItem($media[$kind], 'embedded:' . $entry);
            registerExtractedMediaAsset($mediaAssets, $targetPath, $entryName, $kind);
        }

        $zip->close();
    }

    function buildMediaPromptSummary(array $media, array $mediaAssets): string {
        $lines = [];
        foreach (['images', 'videos', 'iframes', 'links', 'audio'] as $key) {
            $items = $media[$key] ?? [];
            $lines[] = strtoupper($key) . ': ' . count($items);
            foreach (array_slice($items, 0, 5) as $item) {
                $lines[] = '- ' . $item;
            }
        }

        if (!empty($mediaAssets['attachments'])) {
            $lines[] = 'Extracted files:';
            foreach (array_slice($mediaAssets['attachments'], 0, 6) as $asset) {
                $lines[] = '- ' . $asset['name'] . ' (' . $asset['sourceType'] . ')';
            }
        }

        return implode("\n", $lines);
    }

    function decodeGeminiJson(string $text): ?array {
        $text = trim($text);
        if ($text === '') {
            return null;
        }

        if (preg_match('/^```(?:json)?\s*(.*?)\s*```$/s', $text, $matches)) {
            $text = trim($matches[1]);
        }

        $decoded = json_decode($text, true);
        if (is_array($decoded)) {
            return $decoded;
        }

        if (preg_match('/\{.*\}/s', $text, $matches)) {
            $decoded = json_decode($matches[0], true);
            if (is_array($decoded)) {
                return $decoded;
            }
        }

        return null;
    }

    function cleanExtractedText(string $text): string {
         // Convert non-breaking spaces to normal spaces
        $text = str_replace("\xC2\xA0", ' ', $text);

        // Remove URLs
        $text = preg_replace('/https?:\/\/\S+|www\.\S+/', ' ', $text);

        // Remove weird unicode spaces
        $text = preg_replace('/[\x{00A0}\x{2000}-\x{200B}\x{202F}\x{205F}\x{3000}]/u', ' ', $text);

        // Remove academic references / citations noise
        $text = preg_replace('/\b\d{4}\b.*?(university|press|journal|http|www)/i', ' ', $text);

        // Remove bullet symbols
        $text = preg_replace('/[•●·\x{2022}]/u', ' ', $text);

        // Fix spacing before punctuation
        $text = preg_replace('/\s+([,.!?;:])/', '$1', $text);

        // Normalize multiple spaces/newlines/tabs
        $text = preg_replace('/\s+/', ' ', $text);

        return trim($text);
    }

    function extractMeaningfulSentences(string $text): array {
        preg_match_all('/[^.!?]+[.!?]?/', $text, $matches);
        $sentences = $matches[0] ?? [];

        $filtered = [];

        foreach ($sentences as $sentence) {
            $sentence = trim($sentence);

            // remove short noise
            if (strlen($sentence) < 45) continue;

            // remove reference-like sentences
            if (preg_match('/http|www|doi|university|philip|copyright/i', $sentence)) continue;

            $filtered[] = $sentence;
        }

        return $filtered;
    }

    function buildSmartSummary(array $sentences, int $maxLength = 320): string {
        $keywords = [
            'definition','concept','method','approach','result','results',
            'conclusion','analysis','study','overview','explain','introduce'
        ];

        $ranked = [];

        foreach ($sentences as $s) {
            $score = 0;
            $lower = strtolower($s);

            foreach ($keywords as $k) {
                if (strpos($lower, $k) !== false) {
                    $score += 3;
                }
            }

            // prefer meaningful medium-length sentences
            $score += min(3, strlen($s) / 120);

            $ranked[] = ['text' => $s, 'score' => $score];
        }

        usort($ranked, fn($a, $b) => $b['score'] <=> $a['score']);

        $summary = '';
        foreach ($ranked as $item) {
            if (strlen($summary . $item['text']) > $maxLength) break;
            $summary .= $item['text'] . ' ';
        }

        return trim($summary);
    }

    function buildDocumentGeneralSummary(string $fileContent, int $maxLength = 320): string {
        if (trim($fileContent) === '') {
            return 'New course material has been uploaded. Open it to explore key concepts and learning outcomes.';
        }

        // STEP 1: clean
        $clean = cleanExtractedText($fileContent);

        // STEP 2: extract sentences
        $sentences = extractMeaningfulSentences($clean);

        // fallback if nothing usable
        if (empty($sentences)) {
            return substr($clean, 0, $maxLength);
        }

        // STEP 3: smart ranking
        return buildSmartSummary($sentences, $maxLength);
    }

    function extractMaterialTitle(string $fileContent, string $filePath): string {
        $text = trim($fileContent);
        if ($text !== '') {
            $lines = preg_split('/\R+/', $text, -1, PREG_SPLIT_NO_EMPTY);
            if (is_array($lines)) {
                foreach ($lines as $line) {
                    $line = trim($line);
                    if ($line === '') {
                        continue;
                    }

                    $line = preg_replace('/\s+/', ' ', $line);
                    if (strlen($line) > 18) {
                        return mb_substr($line, 0, 120);
                    }
                }
            }
        }

        $fallbackName = pathinfo((string) $filePath, PATHINFO_FILENAME);
        $fallbackName = preg_replace('/[_-]+/', ' ', (string) $fallbackName);
        $fallbackName = trim(preg_replace('/\s+/', ' ', $fallbackName));
        if ($fallbackName !== '') {
            return mb_substr($fallbackName, 0, 120);
        }

        return 'Uploaded Course Material';
    }
    
    function buildNotificationEmailBody(array $notification,array $mediaAssets,?string $heroCid = null,string $moduleName = '',string $materialTitle = '', string $sys_name = '', string $sys_tagline = '',): string {

        $heroSection = '';

        if ($heroCid !== null && !empty($mediaAssets['heroImage']['name'])) {

            $heroSection = '
            <div style="margin-top:30px;">
                <img 
                    src="cid:' . htmlspecialchars($heroCid, ENT_QUOTES, 'UTF-8') . '" 
                    alt="' . htmlspecialchars($mediaAssets['heroImage']['name'], ENT_QUOTES, 'UTF-8') . '" 
                    style="
                        width:100%;
                        max-width:100%;
                        border-radius:22px;
                        display:block;
                        box-shadow:
                        0 10px 25px rgba(15,23,42,0.08),
                        0 20px 48px rgba(15,23,42,0.12);
                    "
                />
            </div>';
        }

        return '
            <div style="
            margin:0;
            padding:40px 0;
            background:linear-gradient(135deg,#dbeafe 0%,#eef2ff 50%,#f8fafc 100%);
            font-family:Inter,Arial,sans-serif;
            ">

                <div style="
                    max-width:760px;
                    margin:0 auto;
                    padding:0 18px;
                ">

                    <div style="
                        background:#ffffff;
                        border-radius:28px;
                        overflow:hidden;
                        box-shadow:
                        0 10px 25px rgba(15,23,42,0.08),
                        0 20px 48px rgba(15,23,42,0.12);
                    ">

                        <!-- HERO SECTION -->

                        <div style="
                            background:linear-gradient(135deg,#4f46e5 0%,#2563eb 100%);
                            padding:25px 30px;
                            text-align:center;
                            color:white;
                        ">

                            <div style="
                                display:inline-block;
                                background:rgba(255,255,255,0.15);
                                padding:8px 16px;
                                border-radius:999px;
                                font-size:12px;
                                font-weight:700;
                                letter-spacing:.08em;
                                text-transform:uppercase;
                                margin-bottom:18px;
                            ">
                                Just Dropped
                            </div>

                            <h1 style="
                                margin:0;
                                font-size:42px;
                                line-height:1.2;
                                font-weight:800;
                                text-transform:uppercase;
                            ">
                                ' . htmlspecialchars($materialTitle, ENT_QUOTES, 'UTF-8') . '
                            </h1>

                            <p style="
                                margin-top:20px;
                                font-size:18px;
                                line-height:1.8;
                                max-width:600px;
                                margin-left:auto;
                                margin-right:auto;
                                opacity:0.95;
                            ">
                                Explore the latest learning material and continue building your knowledge with confidence.
                            </p>

                        </div>

                        <!-- CONTENT -->

                        <div style="padding:38px;">
                            <!-- SUMMARY -->

                            <div style="
                                background:#f8fafc;
                                border:1px solid #e2e8f0;
                                border-radius:20px;
                                padding:26px;
                                margin-top:20px;
                            ">

                                <h2 style="
                                    margin-top:0;
                                    color:#1e293b;
                                    font-size:22px;
                                ">
                                    Material Overview
                                </h2>

                                <p style="
                                    margin-bottom:0;
                                    font-size:16px;
                                    line-height:1.9;
                                    color:#475569;
                                ">
                                    ' . nl2br(htmlspecialchars($notification['summary'], ENT_QUOTES, 'UTF-8')) . '
                                </p>

                            </div>

                            <!-- HERO IMAGE -->

                            ' . $heroSection . '

                            <!-- MATERIAL DETAILS -->

                            <div style="
                                background:#ffffff;
                                border:1px solid #e2e8f0;
                                border-radius:20px;
                                padding:26px;
                                margin-top:30px;
                            ">

                                <table width="100%" cellpadding="0" cellspacing="0">

                                    <tr>
                                        <td style="padding-bottom:14px;color:#334155;">
                                            <strong>Module</strong>
                                        </td>

                                        <td style="
                                            padding-bottom:14px;
                                            text-align:right;
                                            color:#0f172a;
                                        ">
                                            ' . htmlspecialchars($moduleName, ENT_QUOTES, 'UTF-8') . '
                                        </td>
                                    </tr>

                                    <tr>
                                        <td style="padding-bottom:14px;color:#334155;">
                                            <strong>Material</strong>
                                        </td>

                                        <td style="
                                            padding-bottom:14px;
                                            text-align:right;
                                            color:#0f172a;
                                        ">
                                            ' . htmlspecialchars($materialTitle, ENT_QUOTES, 'UTF-8') . '
                                        </td>
                                    </tr>
                                </table>

                            </div>

                            <!-- LEARNING BLOCK -->

                            <div style="
                                margin-top:30px;
                                padding:24px;
                                border-radius:20px;
                                background:linear-gradient(135deg,#eef2ff,#dbeafe);
                            ">


                                <h3 style="
                                    color:#334155;
                                    line-height:1.9;
                                    margin:0;
                                ">
                                    This study material is designed to help you strengthen your understanding through guided concepts, explanations, and structured learning outcomes.
                                </h3>

                            </div>

                            <!-- CTA BUTTON -->

                            <div style="
                                text-align:center;
                                margin-top:38px;
                            ">

                                <a href="http://localhost/instructly/"
                                style="
                                display:inline-block;
                                background:#2563eb;
                                color:#ffffff;
                                text-decoration:none;
                                padding:18px 34px;
                                border-radius:14px;
                                font-size:17px;
                                font-weight:700;
                                box-shadow:
                                0 10px 25px rgba(37,99,235,0.25);
                                ">
                                    Open LMS →
                                </a>

                            </div>

                            <!-- FOOTER -->

                                <div style="
                                    margin-top:45px;
                                    text-align:center;
                                    font-size:13px;
                                    color:#94a3b8;
                                    line-height:1.8;
                                ">

                                    <div style="margin-bottom:18px;">

                                        <img 
                                            src="cid:companylogo"
                                            alt="' . nl2br(htmlspecialchars($sys_name, ENT_QUOTES, 'UTF-8')) . '"
                                            style="
                                                width:50px;
                                                height:auto;
                                                opacity:0.95;
                                            "
                                        />

                                    </div>

                                    <div>
                                        ' . nl2br(htmlspecialchars($sys_name, ENT_QUOTES, 'UTF-8')) . '</strong>
                                    </div>

                                    <div style="margin-top:4px;">
                                        ' . nl2br(htmlspecialchars($sys_tagline, ENT_QUOTES, 'UTF-8')) . '
                                    </div>

                                </div>

                        </div>

                    </div>

                </div>

            </div>';
    }

    function extractMediaMetadata(string $filePath, string $mimeType, ?array &$mediaAssets = null): array {
        $media = [
            'images' => [],
            'videos' => [],
            'iframes' => [],
            'links' => [],
            'audio' => [],
        ];

        if (!is_array($mediaAssets)) {
            $mediaAssets = createMediaAssetStore();
        }

        if ($mimeType === 'text/html' || $mimeType === 'application/xhtml+xml') {
            $html = @file_get_contents($filePath);
            if ($html !== false) {
                $dom = new \DOMDocument();
                libxml_use_internal_errors(true);
                $dom->loadHTML($html);
                libxml_clear_errors();

                foreach ($dom->getElementsByTagName('img') as $node) {
                    addUniqueMediaItem($media['images'], $node->getAttribute('src'));
                }
                foreach ($dom->getElementsByTagName('video') as $node) {
                    addUniqueMediaItem($media['videos'], $node->getAttribute('src'));
                }
                foreach ($dom->getElementsByTagName('source') as $node) {
                    $src = $node->getAttribute('src');
                    if ($src !== '') {
                        $type = strtolower($node->getAttribute('type'));
                        if (strpos($type, 'audio') !== false) {
                            addUniqueMediaItem($media['audio'], $src);
                        } else {
                            addUniqueMediaItem($media['videos'], $src);
                        }
                    }
                }
                foreach ($dom->getElementsByTagName('iframe') as $node) {
                    addUniqueMediaItem($media['iframes'], $node->getAttribute('src'));
                }
                foreach ($dom->getElementsByTagName('a') as $node) {
                    addUniqueMediaItem($media['links'], $node->getAttribute('href'));
                }
                foreach ($dom->getElementsByTagName('audio') as $node) {
                    addUniqueMediaItem($media['audio'], $node->getAttribute('src'));
                }
            }
        }

        if ($mimeType === 'application/vnd.openxmlformats-officedocument.wordprocessingml.document' && class_exists('ZipArchive')) {
            $zip = new \ZipArchive();
            if ($zip->open($filePath) === true) {
                $relsXml = $zip->getFromName('word/_rels/document.xml.rels');
                if ($relsXml !== false) {
                    $rels = @simplexml_load_string($relsXml);
                    if ($rels !== false) {
                        foreach ($rels->Relationship as $relationship) {
                            $type = (string) $relationship['Type'];
                            $target = (string) $relationship['Target'];
                            $targetMode = (string) $relationship['TargetMode'];

                            if (strpos($type, 'hyperlink') !== false && strtolower($targetMode) === 'external') {
                                addUniqueMediaItem($media['links'], $target);
                            }
                        }
                    }
                }

                for ($i = 0; $i < $zip->numFiles; $i++) {
                    $entry = $zip->getNameIndex($i);
                    if (strpos($entry, 'word/media/') === 0) {
                        addUniqueMediaItem($media['images'], 'embedded:' . $entry);
                    }
                }
                $zip->close();
            }

            extractEmbeddedOfficeMedia($filePath, $mimeType, $media, $mediaAssets);
        }

        if ($mimeType === 'application/pdf') {
            $pdfRaw = @file_get_contents($filePath);
            if ($pdfRaw !== false && preg_match_all('/https?:\/\/[^\s\)\]\}>"\']+/i', $pdfRaw, $matches)) {
                foreach ($matches[0] as $url) {
                    addUniqueMediaItem($media['links'], $url);
                }
            }
        }

        return $media;
    }
    
    // 1. Function to read PDF or Word content
    function extractFileContent(string $filePath, string $mimeType): string {
        if ($mimeType === 'application/pdf') {
            // PDF
            $parser = new Parser();
            $pdf = $parser->parseFile($filePath);
            $text = $pdf->getText();

            if (empty(trim($text))) {
                throw new \Exception("Failed to extract text from PDF.");
            }
            return $text;

        } elseif (
            $mimeType === 'application/msword' ||
            $mimeType === 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
        ) {
            // Word (doc or docx)
            $phpWord = IOFactory::load($filePath);
            $text = '';

            foreach ($phpWord->getSections() as $section) {
                foreach ($section->getElements() as $element) {
                    $textGetter = [$element, 'getText'];
                    if (is_callable($textGetter)) {
                        $text .= (string) call_user_func($textGetter) . "\n";
                    }
                }
            }

            if (empty(trim($text))) {
                throw new \Exception("Failed to extract text from Word file.");
            }
            return $text;

        } else {
            throw new \Exception("Unsupported file type for extraction.");
        }
    }

    // 2. Function to generate a short notification using Gemini LLM
    function generateNotification(string $fileContent, array $media, array $mediaAssets, string $moduleName, string $materialTitle): array {
        $apiKey = getenv('GEMINI_API_KEY');
        $endpoint = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-flash-latest:generateContent?key=' . urlencode($apiKey);

        $mediaSummary = buildMediaPromptSummary($media, $mediaAssets);
        $contentExcerpt = substr($fileContent, 0, 12000);
        $bridgeSummary = trim((string) $moduleName) !== '' && trim((string) $materialTitle) !== ''
            ? trim((string) $moduleName) . ' and ' . trim((string) $materialTitle)
            : 'the course material';

        $prompt = "You are writing a connected LMS notification email with a clear learning flow.\n"
            . "Goal: make students feel curious, confident, and eager to open the LMS immediately.\n"
            . "Return strict JSON with these keys only: subject, preheader, headline, summary, cta, closing.\n"
            . "Writing rules:\n"
            . "1) Tone: energetic, smooth, student-friendly, and connected.\n"
            . "2) Subject: <= 70 chars, action oriented, and relevant to the module/material.\n"
            . "3) Preheader: <= 80 chars, build anticipation and flow into the main idea.\n"
            . "4) Headline: 5-10 words, bold and motivating.\n"
            . "5) Summary: 2-4 short sentences in one smooth flow. Start by connecting the module and material title, then explain what students will learn, and end with a natural reason to open LMS now.\n"
            . "6) CTA: one direct command that makes students want to continue immediately.\n"
            . "7) Closing: one short encouraging line that keeps the momentum.\n"
            . "8) Use the module and material names naturally in the copy.\n"
            . "9) Only mention media if it is supported by evidence below. Do not invent media.\n"
            . "10) Never reference this prompt or output formatting in the final text.\n\n"
            . "Module name:\n" . $moduleName . "\n\n"
            . "Material title:\n" . $materialTitle . "\n\n"
            . "Connected flow focus:\n" . $bridgeSummary . "\n\n"
            . "Media evidence:\n" . $mediaSummary . "\n\n"
            . "Document excerpt:\n" . $contentExcerpt;

        $postData = [
            'contents' => [
                [
                    'parts' => [
                        ['text' => $prompt]
                    ]
                ]
            ],
            'generationConfig' => [
                'maxOutputTokens' => 360,
                'temperature' => 0.9
            ]
        ];

        $requestBody = json_encode($postData);
        if ($requestBody === false) {
            throw new \Exception('Failed to encode Gemini request payload.');
        }

        if (function_exists('curl_init')) {
            $ch = curl_init($endpoint);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json'
            ]);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $requestBody);

            $response = curl_exec($ch);
            if ($response === false || curl_errno($ch)) {
                throw new \Exception('Gemini API Request Error: ' . curl_error($ch));
            }
            // curl_close is deprecated on modern PHP; releasing the handle is sufficient.
            $ch = null;
        } else {
            $context = stream_context_create([
                'http' => [
                    'method' => 'POST',
                    'header' => "Content-Type: application/json\r\n",
                    'content' => $requestBody,
                    'ignore_errors' => true,
                    'timeout' => 30,
                ],
            ]);

            $response = @file_get_contents($endpoint, false, $context);
            if ($response === false) {
                throw new \Exception('Gemini API Request Error: HTTP request failed without cURL extension.');
            }
        }

        $data = json_decode($response, true);
        if (isset($data['error']['message'])) {
            error_log('Gemini API Error: ' . $data['error']['message']);

            return [
                'subject' => 'New Course Material Available for ' .
                    (trim((string) $moduleName) !== ''
                        ? trim((string) $moduleName)
                        : 'Your Course'),

                'preheader' => 'A new learning resource has been uploaded.',

                'headline' =>
                    trim((string) $moduleName) !== ''
                        ? $moduleName
                        : 'New Learning Material Available',

                'summary' => buildDocumentGeneralSummary($fileContent),

                'cta' => 'Open the LMS to continue your learning journey.',

                'closing' => 'Keep learning and keep improving.',
            ];
        }

        $generatedText = $data['candidates'][0]['content']['parts'][0]['text'] ?? '';
        $generatedData = decodeGeminiJson($generatedText);

        if (!is_array($generatedData)) {
            return [
                'subject' => 'New Course Material Available for ' . (trim((string) $moduleName) !== '' ? trim((string) $moduleName) : 'Your Course'),
                'preheader' => 'A fresh course update just landed. Dive in now.',
                'headline' => trim((string) $moduleName) !== '' ? trim((string) $moduleName) : 'New material just dropped for your course',
                'summary' => buildDocumentGeneralSummary($fileContent),
                'cta' => 'Open the LMS to explore this material and continue your learning.',
                'closing' => 'You are one focused session away from a big improvement.',
            ];
        }

        return [
            'subject' => trim((string) ($generatedData['subject'] ?? ('New Course Material Available for ' . (trim((string) $moduleName) !== '' ? trim((string) $moduleName) : 'Your Course')))),
            'preheader' => trim((string) ($generatedData['preheader'] ?? 'A fresh course update just landed. Dive in now.')),
            'headline' => trim((string) ($generatedData['headline'] ?? (trim((string) $moduleName) !== '' ? $moduleName : 'New material just dropped for your course'))),
            'summary' => trim((string) ($generatedData['summary'] ?? buildDocumentGeneralSummary($fileContent))),
            'cta' => trim((string) ($generatedData['cta'] ?? 'Open the LMS to explore this material and continue your learning.')),
            'closing' => trim((string) ($generatedData['closing'] ?? 'You are one focused session away from a big improvement.')),
        ];
    }

    // 3. Function to send email to admins, instructor, and students
    function sendEmailNotification(string $subject, string $message, array $adminEmails = [], array $instructorEmails = [], array $studentEmails = [], array $mediaAssets = [], string $sys_logo = ''): bool {
        $mail = new PHPMailer(true);

        try {
            // Server settings
            $mail->isSMTP();
            $mail->Host       = $_ENV['SMTP_HOST'];
            $mail->SMTPAuth   = true;
            $mail->Username   = $_ENV['SMTP_USERNAME'];
            $mail->Password   = $_ENV['SMTP_PASSWORD'];
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = $_ENV['SMTP_PORT'];
            $mail->CharSet    = 'UTF-8';
            $mail->Encoding   = 'base64';

            $mail->setFrom($_ENV['FROM_EMAIL'], $_ENV['FROM_NAME']);

            $logoPath = '../public/sys_data/logo/' . $sys_logo;
            if (file_exists($logoPath)) {
                $mail->addEmbeddedImage(
                    $logoPath,
                    'companylogo',
                    'logo.png'
                );
            }

            // Add TO recipients (admins)
            if (is_array($adminEmails)) {
                foreach ($adminEmails as $email) {
                    $email = trim((string) $email);
                    if ($email !== '') {
                        $mail->addAddress($email);
                    }
                }
            }

            // Add CC recipients (instructor/teacher)
            if (is_array($instructorEmails)) {
                foreach ($instructorEmails as $email) {
                    $email = trim((string) $email);
                    if ($email !== '') {
                        $mail->addCC($email);
                    }
                }
            }

            $heroImagePath = null;
            if (!empty($mediaAssets['heroImage']['path']) && is_file($mediaAssets['heroImage']['path'])) {
                $heroImagePath = $mediaAssets['heroImage']['path'];
                $heroImageCid = 'hero-media-' . md5($heroImagePath) . '@lms';
                $mail->addEmbeddedImage($heroImagePath, $heroImageCid, basename($heroImagePath));
                $message = str_replace('cid:hero-media', 'cid:' . $heroImageCid, $message);
            }

            // Keep extracted media inline in the email body only; do not add downloadable attachments.

            // Add BCC recipients (students)
            if (is_array($studentEmails)) {
                foreach ($studentEmails as $email) {
                    $email = trim((string) $email);
                    if ($email !== '') {
                        $mail->addBCC($email);
                    }
                }
            }

            // Content
            $mail->isHTML(true);
            $mail->AltBody = strip_tags($message);
            $mail->Subject = $subject;
            $mail->Body    = $message;

            if (empty($adminEmails) && empty($instructorEmails) && empty($studentEmails)) {
                throw new MailException('No valid recipients specified.');
            }

            $mail->send();
            return true;
        } catch (MailException $e) {
            error_log("Email could not be sent. Mailer Error: {$mail->ErrorInfo}");
            return false;
        }
    }
?>
