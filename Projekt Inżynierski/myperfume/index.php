
<?php
session_start();
require_once "laczeniezbazadanych.php";
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styl.css">
    <style>
        .search-results {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            justify-content: center;
        }

        .perfume-card {
            max-width: 300px;
            border: 1px solid #ddd;
            border-radius: 8px;
            overflow: hidden;
        }

       .perfume-card img {
    width: 100%; 
    height: 200px; 
    object-fit: cover; 
    display: block; 
    margin: 0 auto;
	}

        .perfume-card h3, .perfume-card p {
            padding: 10px;
            text-align: center;
        }

        .perfume-card a {
            display: block;
            padding: 10px;
            text-align: center;
            background-color: #5c8a8a;
            color: white;
            text-decoration: none;
            border-top: 1px solid #ddd;
        }

		h1 {
    text-align: center;
    
}
        .perfume-card a:hover {
            background-color: #4a6c6c;
        }
    </style>
    <title>Strona Główna</title>
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

<div id="search-container">
    <form action="index.php" method="GET">
        <input type="text" id="search-box" name="search" placeholder="Wyszukaj...">
        <input type="submit" value="Szukaj">
    </form>
</div>


<?php
$laczenie = new mysqli($host, $db_user, $db_password, $db_name);

if ($laczenie->connect_errno != 0) {
    echo "Error: " . $laczenie->connect_errno;
} else {
    if (isset($_GET['search'])) {
        $searchQuery = $_GET['search'];

        $query = "SELECT perfumy.*, marka.nazwa AS nazwa_marki 
                  FROM perfumy 
                  JOIN marka ON perfumy.id_marki = marka.id_marki
                  WHERE ";  

        $keywords = explode(" ", $searchQuery);

        foreach ($keywords as $key => $word) {
            if ($key > 0) {
                $query .= " AND ";
            }
            $query .= "(perfumy.model LIKE '%$word%' OR marka.nazwa LIKE '%$word%')";
        }

        $result = $laczenie->query($query);

        echo "<h1>Wyniki wyszukiwania dla: $searchQuery</h1>";

        if ($result->num_rows > 0) {
            echo "<div class='search-results'>";
            while ($row = $result->fetch_assoc()) {
                echo "<div class='perfume-card'>";
                echo "<img src='zdjecia/{$row['nazwa_pliku']}' alt='{$row['model']}'>";
                echo "<h3>{$row['model']}</h3>";
                echo "<p>Marka: {$row['nazwa_marki']}</p>";
                echo "<a href='perfumy.php?perfume={$row['id_perfum']}'>Zobacz więcej</a>";
                echo "</div>";
            }
            echo "</div>";
        } else {
            $closestQuery = "SELECT perfumy.*, marka.nazwa AS nazwa_marki
                             FROM perfumy 
                             JOIN marka ON perfumy.id_marki = marka.id_marki
                             WHERE ";
            $counter = 0;
            foreach ($keywords as $word) {
                if ($counter > 0) {
                    $closestQuery .= " OR ";
                }
                $closestQuery .= "(perfumy.model LIKE '%$word%' OR marka.nazwa LIKE '%$word%')";
                $counter++;
            }

            $closestResult = $laczenie->query($closestQuery);

            if ($closestResult->num_rows > 0) {
                echo "<p>Brak dokładnych wyników dla zapytania: $searchQuery. Oto najbliższe dopasowania:</p>";
                echo "<div class='search-results'>";
                while ($row = $closestResult->fetch_assoc()) {
                    echo "<div class='perfume-card'>";
                    echo "<img src='zdjecia/{$row['nazwa_pliku']}' style='background-color: transparent;'>";
                    echo "<h3>{$row['nazwa_marki']} {$row['model']}</h3>";
                    echo "<a href='perfumy.php?perfume={$row['id_perfum']}'>Zobacz więcej</a>";
                    echo "</div>";
                }
                echo "</div>";
            } else {
                echo "<p>Brak wyników dla zapytania: $searchQuery</p>";
            }
        }
    }

    $laczenie->close();
}
?>
<div class="help-message">
    <h2>Pomóż mi dobrać moje wymarzone perfumy!</h2>
    <p>Zapraszamy do skorzystania z naszego narzędzia do doboru perfum. Kliknij poniższy przycisk, aby rozpocząć proces dobierania.</p>
    <a href="dobieranie.php" class="help-button">Rozpocznij dobieranie</a>
</div>

<footer>
    <p>&copy; myPerfume 2023. Wszelkie prawa zastrzeżone. <a href="politykaprywatnosci.php">Polityka prywatności</a></p>
</footer>

</body>
</html>
