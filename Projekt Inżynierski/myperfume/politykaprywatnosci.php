<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styl.css">
    <title>Polityka prywatności</title>
<body>
<nav class="navbar">
    <ul>
        <li><a href="index.php">Strona Główna</a></li>
        <li><a href="dobieranie.php">Dobór Perfum</a></li>
        <li class="user-profile">
            <?php 
			session_start();
    require_once "laczeniezbazadanych.php";
$laczenie= new mysqli($host, $db_user, $db_password, $db_name);
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
        myPerfume - Polityka Prywatności
    </div>

  <div class="content-wrapper">
    <div class="privacy-policy-container">
        

        <h2>1. Wprowadzenie</h2>
        <p>Witamy w "myperfume". Szanujemy prywatność naszych użytkowników i jesteśmy zaangażowani w ochronę ich danych osobowych. Niniejsza Polityka Prywatności wyjaśnia, jak zbieramy, używamy, przechowujemy i chronimy dane osobowe naszych użytkowników.</p>

        <h2>2. Jakie Dane Zbieramy</h2>
        <p>Podczas zakładania konta na "myperfume", prosimy użytkowników o podanie następujących danych osobowych:</p>
        <ul>
            <li>-Adres e-mail</li>
            <li>-Hasło</li>
        </ul>

        <h2>3. Jak Przechowujemy i Ochronimy Twoje Dane</h2>
        <h3>a) Adres e-mail</h3>
        <p>Twój adres e-mail jest przechowywany w naszej bazie danych w formie widocznej. Używamy go do komunikacji z Tobą, w tym do wysyłania powiadomień dotyczących Twojego konta i ofert.</p>
        <h3>b) Hasło</h3>
        <p>Twoje hasło jest zabezpieczone poprzez proces hashowania. Oznacza to, że oryginalne hasło jest przekształcane w unikalny ciąg znaków, który nie może być odwrócony. Nie mamy dostępu do Twojego oryginalnego hasła.</p>

        <h2>4. Jak Używamy Twoich Danych</h2>
        <p>Twoje dane są wykorzystywane wyłącznie w celu:</p>
        <ul>
            <li>-Zarządzania Twoim kontem</li>
            <li>-Dostarczania ci informacji o naszych produktach i usługach</li>
            <li>-Ulepszania naszej strony i usług</li>
        </ul>

        <h2>5. Udostępnianie Danych</h2>
        <p>Nie sprzedajemy, nie wynajmujemy ani nie udostępniamy Twoich danych osobowych żadnym stronom trzecim bez Twojej wyraźnej zgody, chyba że jesteśmy do tego zobowiązani na mocy prawa.</p>

        <h2>6. Twoje Prawa</h2>
        <p>Masz prawo do dostępu do swoich danych, ich sprostowania, usunięcia lub ograniczenia przetwarzania. Możesz również wycofać zgodę na przetwarzanie danych osobowych w dowolnym momencie.</p>

        <h2>7. Zmiany w Polityce Prywatności</h2>
        <p>Zastrzegamy sobie prawo do zmiany niniejszej polityki prywatności w dowolnym czasie. O wszelkich zmianach będziemy informować na naszej stronie internetowej.</p>

        <h2>8. Kontakt</h2>
        <p>Jeśli masz jakiekolwiek pytania dotyczące naszej polityki prywatności, prosimy o kontakt przez naszą stronę internetową lub pod email kontakt@myperfume.pl</p>
		
       </div>
</div>
	 <footer>
    <p>&copy; myPerfume 2023. Wszelkie prawa zastrzeżone. <a href="politykaprywatnosci.php">Polityka prywatności</a></p>
</footer>

</body>
</html>