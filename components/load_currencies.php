<?php
include "config.php";

function loadCurrencies() {
    global $pdo;
    $query = "SELECT currencyCode, currencySymbol FROM Currency";
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $currencies = [];
    foreach ($rows as $row) {
        $currencies[$row['currencyCode']] = $row['currencySymbol'];
    }

    return $currencies;
}
?>