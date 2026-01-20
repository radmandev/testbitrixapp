<?php

declare(strict_types=1);

header("Content-Type: application/json");

echo json_encode([
    "result" => "ok",
    "message" => "Connector installation endpoint ready.",
]);
