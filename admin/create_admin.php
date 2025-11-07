<?php
   declare(strict_types=1);

   mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
   require __DIR__ . '/../config.php';

   $username = 'drewCrewAdmin';
   $email = 'drewcrewadmin@example.com';
   $plainPassword = 'drewCrewAdmind'; // change if you want
   $role = 'admin';

   $stmt = $conn->prepare('SELECT id FROM users WHERE username = ? LIMIT 1');
   $stmt->bind_param('s', $username);
   $stmt->execute();
   $stmt->store_result();

   if ($stmt->num_rows > 0) {
       exit('User drewCrewAdmin already exists. No changes made.');
   }
   $stmt->close();

   $hashed = password_hash($plainPassword, PASSWORD_BCRYPT);

   $stmt = $conn->prepare('INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)');
   $stmt->bind_param('ssss', $username, $email, $hashed, $role);
   $stmt->execute();

   echo 'Admin account created successfully. You can now log in with username drewCrewAdmin.';

   $stmt->close();
   $conn->close();