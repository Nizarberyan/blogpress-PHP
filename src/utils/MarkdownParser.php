<?php

class MarkdownParser {
    private static $instance = null;

    private function __construct() {}

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function parse($markdown) {
        $html = htmlspecialchars($markdown);
        
        // Convert code blocks with language
        $html = preg_replace_callback('/```(\w+)?\n(.*?)\n```/s', function($matches) {
            $language = $matches[1] ?? '';
            $code = trim($matches[2]);
            return $this->formatCodeBlock($code, $language);
        }, $html);
        
        // Convert inline code
        $html = preg_replace('/`(.*?)`/', '<code class="bg-gray-100 text-sm px-1 py-0.5 rounded">$1</code>', $html);
        
        // Convert tables
        $html = $this->parseTables($html);
        
        // Convert images with optional size parameters
        $html = preg_replace_callback('/!\[(.*?)\]\((.*?)(?:\s+"(\d+)x(\d+)")?\)/', function($matches) {
            $alt = $matches[1];
            $src = $matches[2];
            $width = $matches[3] ?? null;
            $height = $matches[4] ?? null;
            
            $sizeAttrs = '';
            if ($width && $height) {
                $sizeAttrs = ' width="' . $width . '" height="' . $height . '"';
            }
            
            return '<img src="' . $src . '" alt="' . $alt . '"' . $sizeAttrs . ' class="rounded-lg shadow-md max-w-full h-auto">';
        }, $html);
        
        // Convert headers
        $html = preg_replace('/^### (.*?)$/m', '<h3 class="text-2xl font-bold mt-8 mb-4">$1</h3>', $html);
        $html = preg_replace('/^## (.*?)$/m', '<h2 class="text-3xl font-bold mt-10 mb-6">$1</h2>', $html);
        $html = preg_replace('/^# (.*?)$/m', '<h1 class="text-4xl font-bold mt-12 mb-8">$1</h1>', $html);
        
        // Convert bold and italic
        $html = preg_replace('/\*\*(.*?)\*\*/', '<strong>$1</strong>', $html);
        $html = preg_replace('/\*(.*?)\*/', '<em>$1</em>', $html);
        
        // Convert links
        $html = preg_replace('/\[(.*?)\]\((.*?)\)/', '<a href="$2" class="text-brand-blue hover:text-blue-600 transition">$1</a>', $html);
        
        // Convert lists
        $html = preg_replace('/^\- (.*?)$/m', '<li class="ml-4">$1</li>', $html);
        $html = preg_replace('/(<li.*?>.*?<\/li>)+/', '<ul class="list-disc space-y-2 my-4">$0</ul>', $html);
        
        // Convert paragraphs
        $html = '<p class="mb-4">' . preg_replace('/\n\n/', '</p><p class="mb-4">', $html) . '</p>';
        
        return $html;
    }

    private function formatCodeBlock($code, $language) {
        $languageClass = $language ? ' language-' . $language : '';
        return '<pre class="bg-gray-800 text-gray-100 rounded-lg p-4 my-4 overflow-x-auto"><code class="block' . 
               $languageClass . '">' . $code . '</code></pre>';
    }

    private function parseTables($text) {
        // Split text into lines
        $lines = explode("\n", $text);
        $inTable = false;
        $tableContent = [];
        $result = [];
        
        foreach ($lines as $line) {
            // Check if line is part of a table
            if (preg_match('/^\|.*\|$/', trim($line))) {
                if (!$inTable) {
                    $inTable = true;
                }
                $tableContent[] = $line;
            } else if ($inTable) {
                // End of table reached
                $result[] = $this->convertTableToHtml($tableContent);
                $tableContent = [];
                $inTable = false;
                $result[] = $line;
            } else {
                $result[] = $line;
            }
        }
        
        // Handle case where table is at end of text
        if ($inTable) {
            $result[] = $this->convertTableToHtml($tableContent);
        }
        
        return implode("\n", $result);
    }

    private function convertTableToHtml($tableLines) {
        if (count($tableLines) < 2) return implode("\n", $tableLines);

        $html = '<div class="overflow-x-auto my-6"><table class="min-w-full divide-y divide-gray-200">';
        
        // Process header
        $headers = $this->parseTableRow($tableLines[0]);
        $html .= '<thead class="bg-gray-50"><tr>';
        foreach ($headers as $header) {
            $html .= '<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">' 
                  . trim($header) . '</th>';
        }
        $html .= '</tr></thead>';
        
        // Skip the separator line (line with |---|---|)
        $html .= '<tbody class="bg-white divide-y divide-gray-200">';
        for ($i = 2; $i < count($tableLines); $i++) {
            $cells = $this->parseTableRow($tableLines[$i]);
            $html .= '<tr class="hover:bg-gray-50">';
            foreach ($cells as $cell) {
                $html .= '<td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">' 
                      . trim($cell) . '</td>';
            }
            $html .= '</tr>';
        }
        
        $html .= '</tbody></table></div>';
        return $html;
    }

    private function parseTableRow($row) {
        // Remove first and last pipe characters
        $row = trim($row, '|');
        // Split by pipe character
        return array_map('trim', explode('|', $row));
    }

    public function parseLine($markdown) {
        return $this->parse($markdown);
    }
} 