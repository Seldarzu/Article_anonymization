<?php
function extract_entities($text) {
    
    $authors = [];
    $emails = [];
    $institutions = [];

    
    preg_match_all('/\b[A-Z][a-z]+ [A-Z][a-z]+\b/', $text, $authors);
    $authors = $authors[0];

 
    preg_match_all('/[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}/', $text, $emails);
    $emails = $emails[0];

   
    preg_match_all('/\b[A-Z][a-zA-Z&.\-]+ (University|Institute|College|Laboratory|Academy|School|Faculty)\b/', $text, $institutions);
    $institutions = $institutions[0];

    return [
        "authors" => $authors,
        "emails" => $emails,
        "institutions" => $institutions
    ];
}
function anahtar_kelimeleri_cikar($text) {
    if (empty($text)) return '';

    $text = strtolower($text);
    $text = preg_replace('/[^a-z0-9\s]/', '', $text);
    $kelimeler = explode(' ', $text);

    $stopwords = ['the','and','of','to','in','a','is','it','this','that','we','our','with','as','by','an','be','are'];
    $frekans = [];

    foreach ($kelimeler as $kelime) {
        $kelime = trim($kelime);
        if (strlen($kelime) < 4 || in_array($kelime, $stopwords)) continue;
        $frekans[$kelime] = ($frekans[$kelime] ?? 0) + 1;
    }

    arsort($frekans);
    return implode(', ', array_slice(array_keys($frekans), 0, 5));
}

function anonymize_text($text, $entities, $secenekler) {
   
    if (in_array('yazar_ad', $secenekler)) {
        foreach ($entities["authors"] as $author) {
            $text = str_replace($author, '[Anonim Yazar]', $text);
        }
    }

    
    if (in_array('yazar_kurum', $secenekler)) {
        foreach ($entities["institutions"] as $institution) {
            $text = str_replace($institution, '[Kurum Bilgisi Gizlendi]', $text);
        }
    }

   
    if (in_array('doi', $secenekler)) {
        $text = preg_replace('/10\.\d{4,9}\/[\-._;()\/:A-Za-z0-9]+/', '[DOI Gizlendi]', $text);
    }

    return $text;
}
?>