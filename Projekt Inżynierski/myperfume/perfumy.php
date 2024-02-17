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
    <title>Strona perfum</title>
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


<?php


$laczenie = new mysqli($host, $db_user, $db_password, $db_name);

if ($laczenie->connect_errno != 0) {
    echo "Error: " . $laczenie->connect_errno;
} else {
    // Funkcja do pobierania informacji o perfumach na podstawie ID
    function informacje($conn, $perfumeId) {
        $query = "SELECT perfumy.*, marka.nazwa AS nazwa_marki 
                  FROM perfumy 
                  JOIN marka ON perfumy.id_marki = marka.id_marki
                  WHERE perfumy.id_perfum = $perfumeId";
        $result = $conn->query($query);

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            return $row;
        } else {
            return null;
        }
    }
	
	function wyszukajnastepne($conn, $searchCriteria, $searchOrderBy, $currentIndex) {
    $sql = "SELECT * FROM perfumy WHERE " . implode(" AND ", $searchCriteria) . $searchOrderBy;
    $sql .= " LIMIT 1 OFFSET $currentIndex"; // Pomiń wyniki aż do bieżącego indeksu
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        return $result->fetch_assoc();
    } else {
        return null;
    }
}

function wyszukajpoprzednie($conn, $searchCriteria, $searchOrderBy, $currentIndex) {
    $sql = "SELECT * FROM perfumy WHERE " . implode(" AND ", $searchCriteria) . $searchOrderBy;
    $sql .= " LIMIT 1 OFFSET $currentIndex"; // Pomiń wyniki aż do bieżącego indeksu
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        return $result->fetch_assoc();
    } else {
        return null;
    }
}



if (isset($_GET['next'])) {
    // Inkrementuj indeks wyniku w sesji
    $_SESSION['search_index']++;

    $nastepne = wyszukajnastepne($laczenie, $_SESSION['search_criteria'], $_SESSION['search_order_by'], $_SESSION['search_index']);

    if ($nastepne) {
        $id_perfum = $nastepne['id_perfum'];
        header("Location: perfumy.php?perfume=" . $id_perfum);
        exit();
    } else {
        echo "Brak więcej perfum pasujących do kryteriów.";
        $_SESSION['search_index'] = 0;
    }
}

if (isset($_GET['previous'])) {
    $_SESSION['search_index'] = max($_SESSION['search_index'] - 1, 0);
    $poprzednieperfumy = wyszukajnastepne($laczenie, $_SESSION['search_criteria'], $_SESSION['search_order_by'], $_SESSION['search_index']);

    if ($poprzednieperfumy) {
        $id_perfum = $poprzednieperfumy['id_perfum'];
        header("Location: perfumy.php?perfume=" . $id_perfum);
        exit();
    } else {
    }
}

   
    if (isset($_GET['perfume'])) {
        $perfumeId = $_GET['perfume'];
        $perfumeInfo = informacje($laczenie, $perfumeId);

        if ($perfumeInfo) {
            echo '<div class="perfume-info-container">';
            
            echo "<h1>{$perfumeInfo['nazwa_marki']} - {$perfumeInfo['model']}</h1>";

            $sciezkaDoZdjecia = "zdjecia/{$perfumeInfo['nazwa_pliku']}";
            if (file_exists($sciezkaDoZdjecia)) {
                echo "<div class='perfume-image-container'><img src='{$sciezkaDoZdjecia}' alt='Zdjęcie perfum'></div>";
            } else {
                echo '<p>Brak dostępnego zdjęcia.</p>';
            }

            echo '<div class="perfume-details">';
               echo "<p><strong>Opis:</strong> {$perfumeInfo['opis']}</p>";
            echo "<p><strong>Informacje o zapachu:</strong></p>";
            echo "<ul>";
            echo "<li><strong>Akordy zapachowe:</strong> {$perfumeInfo['typ_zapachu']}</li>";
            echo "<li><strong>Kategoria zapachu:</strong> {$perfumeInfo['kategoria_zapachu']}</li>";
            echo "<li><strong>Pora roku:</strong> {$perfumeInfo['pora_roku']}</li>";
            echo "<li><strong>Typ:</strong> {$perfumeInfo['typ']}</li>";
            echo "<li><strong>Trwałość:</strong> {$perfumeInfo['trwalosc']}</li>";
            echo "<li><strong>Projekcja:</strong> {$perfumeInfo['projekcja']}</li>";
            echo "<li><strong>Płeć:</strong> {$perfumeInfo['plec']}</li>";
            echo "<li><strong>Pora:</strong> {$perfumeInfo['pora']}</li>";
            echo "</ul>";

            echo "<p><strong>Data wydania:</strong> {$perfumeInfo['data_wydania']}</p>";
            echo "<p><strong>Cena:</strong> {$perfumeInfo['cena']} zł</p>";
            echo '</div>'; 
			  if (isset($_SESSION['search_criteria'])) {
            $czypierwsze = $_SESSION['search_index'] == 0;
            $czyostatnie = wyszukajnastepne($laczenie, $_SESSION['search_criteria'], $_SESSION['search_order_by'], $_SESSION['search_index'] + 1) === null;
            $poprzednie = $_SESSION['search_index'] > 0 ? $_SESSION['search_index'] - 1 : 0;
            $czypoprzednie = $_SESSION['search_index'] > 0 && wyszukajpoprzednie($laczenie, $_SESSION['search_criteria'], $_SESSION['search_order_by'], $poprzednie) !== null;

            if (!$czyostatnie) {
                echo '<a href="perfumy.php?next=1" class="next-button">Następny wynik</a>';
            }

            if ($czypoprzednie) {
                echo '<a href="perfumy.php?previous=1" class="previous-button">Poprzedni wynik</a>';
            }
        }
    } else {
        echo "<p>Perfumy o podanym ID nie istnieją.</p>";
    }
} else {
        echo "<h1>Lista Perfum</h1>";

        $query = "SELECT id_perfum, model FROM perfumy";
        $result = $laczenie->query($query);

        if ($result->num_rows > 0) {
            echo "<ul>";
            while ($row = $result->fetch_assoc()) {
                echo "<li><a href='perfumy.php?perfume={$row['id_perfum']}'>{$row['model']}</a></li>";
            }
            echo "</ul>";
        } else {
            echo "<p>Brak dostępnych perfum.</p>";
        }
    

    }

    $laczenie->close();
}
?>
    <footer>
        <p>&copy; myPerfume 2023. Wszelkie prawa zastrzeżone. <a href="politykaprywatnosci.php">Polityka prywatności</a></p>
    </footer>

</body>
</html>