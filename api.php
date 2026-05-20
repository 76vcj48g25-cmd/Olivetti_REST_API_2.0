<?php

header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER["REQUEST_METHOD"] === "OPTIONS") {
    exit;
}

$categorie = [
    "Serie A1",
    "Serie A2",
    "Serie A3",
    "Serie B",
    "Serie C",
    "Serie D",
    "Prima Divisione",
    "Seconda Divisione"
];

$giocatori = [
    [
        "id" => 1,
        "nome" => "Simone",
        "cognome" => "Giannelli",
        "eta" => 29,
        "categoria" => "Serie A1"
    ],
    [
        "id" => 2,
        "nome" => "NomeGiocatore2",
        "cognome" => "CognomeGiocatore2",
        "eta" => 22,
        "categoria" => "Serie B"
    ],
    [
        "id" => 3,
        "nome" => "NomeGiocatore3",
        "cognome" => "CognomeGiocatore3",
        "eta" => 16,
        "categoria" => "Prima Divisione"
    ]
];

function rispostaJSON($dati, $codice = 200) {
    http_response_code($codice);
    echo json_encode($dati, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit;
}

/*
    Routing compatibile anche con UniServer/Apache.
    Permette URL come:
    api.php/tutti
    api.php/categorie
    api.php/categoria/Serie%20A1
*/
$path = $_SERVER["PATH_INFO"] ?? "";

if ($path === "") {
    $uri = $_SERVER["REQUEST_URI"];
    $scriptName = $_SERVER["SCRIPT_NAME"];

    $path = str_replace($scriptName, "", $uri);
    $path = strtok($path, "?");
}

$segmenti = array_values(array_filter(explode("/", $path)));

$metodo = $_SERVER["REQUEST_METHOD"];
$rotta = $segmenti[0] ?? "";

if ($metodo === "GET" && $rotta === "tutti") {
    rispostaJSON([
        "successo" => true,
        "giocatori" => $giocatori
    ]);
}

if ($metodo === "GET" && $rotta === "categorie") {
    rispostaJSON([
        "successo" => true,
        "categorie" => $categorie
    ]);
}

if ($metodo === "GET" && $rotta === "categoria") {
    $categoriaRichiesta = urldecode($segmenti[1] ?? "");

    if ($categoriaRichiesta === "") {
        rispostaJSON([
            "successo" => false,
            "messaggio" => "Categoria non specificata"
        ], 400);
    }

    $filtrati = array_values(array_filter($giocatori, function ($giocatore) use ($categoriaRichiesta) {
        return $giocatore["categoria"] === $categoriaRichiesta;
    }));

    rispostaJSON([
        "successo" => true,
        "categoria" => $categoriaRichiesta,
        "giocatori" => $filtrati
    ]);
}

if ($metodo === "POST" && $rotta === "nuovo") {
    $input = json_decode(file_get_contents("php://input"), true);

    if (!$input) {
        rispostaJSON([
            "successo" => false,
            "messaggio" => "Dati JSON non validi"
        ], 400);
    }

    $nome = trim($input["nome"] ?? "");
    $cognome = trim($input["cognome"] ?? "");
    $eta = intval($input["eta"] ?? 0);
    $categoria = trim($input["categoria"] ?? "");

    if ($nome === "" || $cognome === "" || $eta === 0 || $categoria === "") {
        rispostaJSON([
            "successo" => false,
            "messaggio" => "Tutti i campi sono obbligatori"
        ], 400);
    }

    if ($eta < 12 || $eta > 40) {
        rispostaJSON([
            "successo" => false,
            "messaggio" => "L'età deve essere compresa tra 12 e 40 anni"
        ], 400);
    }

    if (!in_array($categoria, $categorie)) {
        rispostaJSON([
            "successo" => false,
            "messaggio" => "Categoria non valida"
        ], 400);
    }

    $nuovoGiocatore = [
        "id" => count($giocatori) + 1,
        "nome" => $nome,
        "cognome" => $cognome,
        "eta" => $eta,
        "categoria" => $categoria
    ];

    rispostaJSON([
        "successo" => true,
        "messaggio" => "Giocatore inserito correttamente",
        "giocatore" => $nuovoGiocatore
    ], 201);
}

rispostaJSON([
    "successo" => false,
    "messaggio" => "Rotta non trovata"
], 404);