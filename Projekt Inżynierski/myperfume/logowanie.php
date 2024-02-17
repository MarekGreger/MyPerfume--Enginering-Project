<?php 
session_start();
require_once "laczeniezbazadanych.php";

if((!isset($_POST['email'])) || !isset($_POST['haslo'])) {
    header('Location: stronalogowania.php');
    exit();
}

$laczenie = new mysqli($host, $db_user, $db_password, $db_name);

if ($laczenie->connect_errno != 0) {
    echo "Error: " . $laczenie->connect_errno;
} else {
    $email = mysqli_real_escape_string($laczenie, $_POST['email']);
    $haslo = $_POST['haslo'];
    
    $sql = sprintf("SELECT * FROM uzytkownik WHERE email='%s'", $email);
    if ($rezultat = $laczenie->query($sql)) {
        $ilu_userow = $rezultat->num_rows;
        if($ilu_userow > 0) {
            $wiersz = $rezultat->fetch_assoc();
            if (password_verify($haslo, $wiersz['haslo'])) {
                $_SESSION['zalogowany'] = true;
                $_SESSION['id_u'] = $wiersz['id_u'];
                $_SESSION['nick'] = $wiersz['nick'];

                unset($_SESSION['fail']);
                $rezultat->free_result();
                header('Location: index.php');
            } else {
                $_SESSION['fail'] = '<span style= "color:red"> Błędne hasło!</span>';
                header('Location: stronalogowania.php');
            }
        } else {
            $_SESSION['fail'] = '<span style= "color:red"> Nie znaleziono użytkownika z takim adresem email!</span>';;
            header('Location: stronalogowania.php');
        }
    }
    $laczenie->close();
}
?>
