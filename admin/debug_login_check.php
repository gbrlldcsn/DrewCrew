<?php
require __DIR__.'/../config.php';

$u = 'drewCrewAdmin';             // change if testing another
$p = 'MyNewAdminPass123!';        // the plaintext you expect

$res = $conn->query("SELECT DATABASE() db, username, email, role, password FROM users WHERE username='$u' LIMIT 1");
$row = $res->fetch_assoc();

echo "DB: {$row['db']}\n";
echo "User: {$row['username']}\n";
echo "Role: {$row['role']}\n";
echo "Hash: {$row['password']}\n";
echo "StartsWith \$2y$: " . (str_starts_with($row['password'], '$2y$') ? 'yes' : 'no') . "\n";
echo "password_verify: " . (password_verify($p, $row['password']) ? 'MATCH' : 'NO MATCH') . "\n";