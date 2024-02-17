
<?php
session_start();
    require_once "laczeniezbazadanych.php";
$laczenie= new mysqli($host, $db_user, $db_password, $db_name);
if (!isset($_SESSION['zalogowany'])) {
    header('Location: stronalogowania.php');
    exit();
}

    
if (isset($_SESSION['id_u'])) {
    $user_id = $_SESSION['id_u'];
} else {
    echo "Brak ID użytkownika w sesji.";
    exit();
}


    function validate_data($data) {
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data);
        return $data;
    }
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['change_password'])) {
    $currentPassword = validate_data($_POST['currentPassword']);
    $newPassword = validate_data($_POST['newPassword']);
    $confirmNewPassword = validate_data($_POST['confirmNewPassword']);

    if ($newPassword != $confirmNewPassword) {
        echo "Nowe hasła się nie zgadzają!";
    } else {
        $query = "SELECT haslo FROM uzytkownik WHERE id_u = '$user_id'";
        $result = $laczenie->query($query);
        if ($row = $result->fetch_assoc()) {
            if (password_verify($currentPassword, $row['haslo'])) {
                $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

                $updateQuery = "UPDATE uzytkownik SET haslo = '$hashedPassword' WHERE id_u = '$user_id'";
                if ($laczenie->query($updateQuery)) {
                    echo "Hasło zostało zmienione.";
                } else {
                    echo "Błąd przy zmianie hasła: " . $laczenie->error;
                }
            } else {
                echo "Aktualne hasło jest niepoprawne!";
            }
        }
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['change_nick'])) {
    $newNick = validate_data($_POST['newNick']);

    $updateQuery = "UPDATE uzytkownik SET nick = '$newNick' WHERE id_u = '$user_id'";
    if ($laczenie->query($updateQuery)) {
        echo "Nick został zmieniony.";
    } else {
        echo "Błąd przy zmianie nicka: " . $laczenie->error;
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['change_email'])) {
    $newEmail = validate_data($_POST['newEmail']);

    $updateQuery = "UPDATE uzytkownik SET email = '$newEmail' WHERE id_u = '$user_id'";
    if ($laczenie->query($updateQuery)) {
        echo "Adres e-mail został zmieniony.";
    } else {
        echo "Błąd przy zmianie adresu e-mail: " . $laczenie->error;
    }
}

?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styl.css">
    <title>Profil Użytkownika</title>
</head>
<body>

    <nav class="navbar">
    <ul>
        <li><a href="index.php">Strona Główna</a></li>
        <li><a href="dobieranie.php">Dobór Perfum</a></li>
        <li class="user-profile">
            <?php 
            if (isset($_SESSION['zalogowany']) && $_SESSION['zalogowany'] == true) {
                $nazwaUzytkownika = $_SESSION['nick'];
                echo "<a href='uzytkownik.php'>Profil użytkownika: $nazwaUzytkownika</a>";
            } else {
                echo "<a href='stronalogowania.php'>Profil Użytkownika</a>";
            }
            ?>
        </li>
        <li>
            <?php
            if (isset($_SESSION['zalogowany']) && $_SESSION['zalogowany'] == true) {
                echo "<a href='wylogowanie.php'>Wyloguj się</a>";
            } else {
                echo "<a href='stronalogowania.php'>Zaloguj się</a>";
            }
            ?>
        </li>
    </ul>
</nav>

    <div class="centered-title">
        myPerfume - Mój Profil
    </div>


<form method="post" action="">
    <w2>Zmiana hasła</w2>
    <label for="currentPassword">Aktualne Hasło:</label><br>
    <input type="password" name="currentPassword" required><br>
    
    <label for="newPassword">Nowe Hasło:</label><br>
    <input type="password" name="newPassword" required><br>
    
    <label for="confirmNewPassword">Potwierdź Nowe Hasło:</label><br>
    <input type="password" name="confirmNewPassword" required><br>
    
    <input type="submit" name="change_password" value="Zmień Hasło">
</form>


   
    <form method="post" action="">
        <w2>Zmiana Nick'a</w2>
        <label for="newNick">Nowy Nick:</label><br>
        <input type="text" name="newNick" required><br>
        
        <input type="submit" name="change_nick" value="Zmień Nick">
    </form>


  <form method="post" action="">
    <w1>Zmiana Adresu E-mail</w1>
    <label for="newEmail">Nowy E-mail:</label><br>
    <input type="text" name="newEmail" class="input-class" required><br>
    
    <input type="submit" name="change_email" class="button-class" value="Zmień E-mail">
</form>

    <footer>
    <p>&copy; myPerfume 2023. Wszelkie prawa zastrzeżone. <a href="politykaprywatnosci.php">Polityka prywatności</a></p>
</footer>

</body>
</html>