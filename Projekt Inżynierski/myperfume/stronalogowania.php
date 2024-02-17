

<?php
session_start();
if ((isset($_SESSION['zalogowany'])) && ($_SESSION['zalogowany']==true)) {
    header('Location: main.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styl.css">
    <title>Logowanie</title>
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
    myPerfume
  </div>
  
  <div class="login-form">
    
  
    <form action="logowanie.php" method="post">
	<?php if(isset($_SESSION['fail'])): ?>
        <div class="error-message"><?php echo $_SESSION['fail']; unset($_SESSION['fail']); ?></div>
    <?php endif; ?>
      E-mail: <br /> <input type="text" name="email" /> <br />
      Hasło: <br /> <input type="password" name="haslo" /> <br /><br />
      <input type="submit" value="Zaloguj się" />
      <a href="rejestracja.php" id="register-link">Nie masz konta? Zarejestruj się!</a>
    </form>
  </div>
  
   <footer>
        <p>&copy; myPerfume 2023. Wszelkie prawa zastrzeżone. <a href="politykaprywatnosci.php">Polityka prywatności</a></p>
    </footer>
</body>
</html>
