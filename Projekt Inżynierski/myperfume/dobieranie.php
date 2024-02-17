<?php
    session_start();
	require_once "laczeniezbazadanych.php";
	error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);
	ini_set('display_errors', 'Off');
	?>


<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styl.css">
    <title>Dobór perfum</title>
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



<?php


function znajdz_podobne_pory_roku($wybrana_pora_roku)
{
    $kolejnosc_podobienstwa = [
        'wiosna' => ['wiosna', 'lato', 'zima'],
        'lato' => ['lato', 'wiosna', 'jesień'],
        'jesień' => ['jesień', 'zima', 'wiosna'],
        'zima' => ['zima', 'jesień', 'wiosna'],
    ];

    return $kolejnosc_podobienstwa[$wybrana_pora_roku] ?? [];
}

function stworz_warunki_i_sortowanie_dla_pory_roku($wybrana_pora_roku)
{
    $kolejnosc_podobienstwa = znajdz_podobne_pory_roku($wybrana_pora_roku);

    $warunki = [];
    $kolejnosc = [];
    foreach ($kolejnosc_podobienstwa as $index => $pora) {
        $warunki[] = "pora_roku = '$pora'";
        $kolejnosc[] = "WHEN pora_roku = '$pora' THEN $index";
    }

    $warunki_sql = implode(" OR ", $warunki);
    $kolejnosc_sql = "CASE " . implode(" ", $kolejnosc) . " END";

    return ['warunki' => $warunki_sql, 'kolejnosc' => $kolejnosc_sql];
}

function znajdz_podobne_trwalosci($wybrana_trwalosc) {
    $kolejnosc_podobienstwa = [
        'bardzo słaba' => ['bardzo słaba', 'słaba'],
        'słaba' => ['słaba', 'przeciętna','bardzo słaba'],
        'przeciętna' => ['przeciętna','duża', 'słaba' ],
        'duża' => ['duża', 'bardzo duża','przeciętna'],
        'bardzo duża' => ['bardzo duża', 'duża']
    ];

    return $kolejnosc_podobienstwa[$wybrana_trwalosc] ?? [];
}

function stworz_warunki_i_sortowanie_dla_trwalosci($wybrana_trwalosc) {
    $kolejnosc_podobienstwa = znajdz_podobne_trwalosci($wybrana_trwalosc);

    $warunki = [];
    $kolejnosc = [];
    foreach ($kolejnosc_podobienstwa as $index => $trwalosc) {
        $warunki[] = "trwalosc = '$trwalosc'";
        $kolejnosc[] = "WHEN trwalosc = '$trwalosc' THEN $index";
    }

    $warunki_sql = implode(" OR ", $warunki);
    $kolejnosc_sql = "CASE " . implode(" ", $kolejnosc) . " END";

    return ['warunki' => $warunki_sql, 'kolejnosc' => $kolejnosc_sql];
}

function znajdz_podobne_projekcje($wybrana_projekcja) {
    $kolejnosc_podobienstwa = [
        'bliskoskórna' => ['bliskoskórna', 'łagodna'],
        'łagodna' => ['łagodna', 'przeciętna', 'bliskoskórna'],
        'przeciętna' => ['przeciętna', 'duża', ],
        'duża' => ['duża','olbrzymia','przeciętna'],
        'olbrzymia' => ['olbrzymia', 'duża']
    ];

    return $kolejnosc_podobienstwa[$wybrana_projekcja] ?? [];
}

function stworz_warunki_i_sortowanie_dla_projekcji($wybrana_projekcja) {
    $kolejnosc_podobienstwa = znajdz_podobne_projekcje($wybrana_projekcja);

    $warunki = [];
    $kolejnosc = [];
    foreach ($kolejnosc_podobienstwa as $index => $projekcja) {
        $warunki[] = "projekcja = '$projekcja'";
        $kolejnosc[] = "WHEN projekcja = '$projekcja' THEN $index";
    }

    $warunki_sql = implode(" OR ", $warunki);
    $kolejnosc_sql = "CASE " . implode(" ", $kolejnosc) . " END";

    return ['warunki' => $warunki_sql, 'kolejnosc' => $kolejnosc_sql];
}


function stworz_warunki_dla_plci($wybrana_plec) {
    $warunki = [];
    if ($wybrana_plec == 'uniseks') {
        $warunki[] = "(plec = 'uniseks' OR plec = 'mężczyzna' OR plec = 'kobieta')";
    } elseif ($wybrana_plec == 'mężczyzna' || $wybrana_plec == 'kobieta') {
        $warunki[] = "(plec = '$wybrana_plec')";
    } else {
        $warunki[] = "(plec = 'mężczyzna' OR plec = 'kobieta' OR plec = 'uniseks')";
    }
    return implode(" ", $warunki);
}

function stworz_warunki_i_sortowanie_dla_typu_zapachu($typy_zapachu, $laczenie) {
    if (empty($typy_zapachu)) {
        return ['warunki' => '1', 'sortowanie' => ''];
    }

    $warunki = [];
    foreach ($typy_zapachu as $typ) {
        $typ = $laczenie->real_escape_string($typ);
        $warunki[] = "FIND_IN_SET('$typ', typ_zapachu)";
    }

    $warunki_sql = implode(" + ", $warunki); // Suma wag dla obliczenia wagi
    $sortowanie_sql = " ORDER BY ($warunki_sql) DESC, cena ASC"; // Sortuj od najwyższej wagi, a potem po cenie

    return ['warunki' => "($warunki_sql) > 0", 'sortowanie' => $sortowanie_sql];
}
$laczenie = new mysqli($host, $db_user, $db_password, $db_name);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $cena = isset($_POST["cena"]) ? $_POST["cena"] : null;
    $data_wydania = isset($_POST["data_wydania"]) ? $_POST["data_wydania"] : null;
    $pora = isset($_POST["pora"]) ? $_POST["pora"] : null;
    $plec = isset($_POST["plec"]) ? $_POST["plec"] : null;
    $trwalosc = isset($_POST["trwalosc"]) ? $_POST["trwalosc"] : null;
    $projekcja = isset($_POST["projekcja"]) ? $_POST["projekcja"] : null;
    $typ = isset($_POST["typ"]) ? $_POST["typ"] : null;

    $pora_roku = isset($_POST["pora_roku"]) ? $_POST["pora_roku"] : [];
    $kategoria_zapachu = isset($_POST["kategoria_zapachu"]) ? $_POST["kategoria_zapachu"] : [];
    $typ_zapachu = isset($_POST["typ_zapachu"]) ? $_POST["typ_zapachu"] : [];

    $sql = "SELECT * FROM perfumy WHERE ";
    $conditions = array();

  if (!empty($cena)) {
   
    $cena = filter_var($cena, FILTER_SANITIZE_NUMBER_INT);

  
    $conditions[] = "cena BETWEEN " . ($cena - 100) . " AND " . ($cena + 100);
}
   if (!empty($data_wydania)) {

    if (preg_match('/^\d{4}$/', $data_wydania)) {
       
        $rok_wydania = intval($data_wydania);
        
      
        $conditions[] = "data_wydania >= $rok_wydania - 4 AND data_wydania <= $rok_wydania + 4";
    } else {
        $_SESSION['blad'] = "Podaj tylko rok jako datę wydania.";
       
    }
}
    if (!empty($pora)) {
        $conditions[] = "pora = '$pora'";
    }
  if (!empty($plec)) {
    $conditions[] = stworz_warunki_dla_plci($plec);
}
    if (!empty($_POST["pora_roku"])) {
        $pora_roku_sql = stworz_warunki_i_sortowanie_dla_pory_roku($_POST["pora_roku"][0]); // Zakładamy, że wybrano tylko jedną porę roku

        if (!empty($pora_roku_sql['warunki'])) {
            $conditions[] = "(" . $pora_roku_sql['warunki'] . ")";
            $order_by = " ORDER BY " . $pora_roku_sql['kolejnosc'];
        }
    }
    if (!empty($_POST["trwalosc"])) {
    $trwalosc_sql = stworz_warunki_i_sortowanie_dla_trwalosci($_POST["trwalosc"]);

    if (!empty($trwalosc_sql['warunki'])) {
        $conditions[] = "(" . $trwalosc_sql['warunki'] . ")";
        $order_by = " ORDER BY " . $trwalosc_sql['kolejnosc'];
    }
}
    if (!empty($_POST["projekcja"])) {
    $projekcja_sql = stworz_warunki_i_sortowanie_dla_projekcji($_POST["projekcja"]);

    if (!empty($projekcja_sql['warunki'])) {
        $conditions[] = "(" . $projekcja_sql['warunki'] . ")";
        $order_by = " ORDER BY " . $projekcja_sql['kolejnosc'];
    }
}
    if (!empty($kategoria_zapachu)) {
        $conditions[] = "kategoria_zapachu IN ('" . implode("','", $kategoria_zapachu) . "')";
    }
   if (!empty($_POST["typ_zapachu"])) {
    $typ_zapachu_sql = stworz_warunki_i_sortowanie_dla_typu_zapachu($_POST["typ_zapachu"], $laczenie);

    if (!empty($typ_zapachu_sql['warunki'])) {
        $conditions[] = "(" . $typ_zapachu_sql['warunki'] . ")";
        $order_by = $typ_zapachu_sql['sortowanie'];
    }
}
    $sql = "SELECT * FROM perfumy";
    if (!empty($conditions)) {
        $sql .= " WHERE " . implode(" AND ", $conditions) . $order_by;
    
        $result = $laczenie->query($sql);

        if ($result) {
            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                $id_perfum = $row['id_perfum'];
				//echo "Zapytanie SQL: " . $sql;
				//echo "Zperfumy " . $id_perfum;
				
				 $_SESSION['search_criteria'] = $conditions;
        $_SESSION['search_order_by'] = $order_by;
        $_SESSION['search_index'] = 0; // Ustaw początkowy indeks wyniku na 0
                header("Location: perfumy.php?perfume=" . $id_perfum);
                exit();
            } else {
			//	echo "Zapytanie SQL: " . $sql;
                echo "Brak pasujących perfum.";
            }
        } else {
            echo "Błąd w zapytaniu SQL: " . $laczenie->error;
        }
    } else {
		//echo "Zapytanie SQL: " . $sql;
        echo "Proszę wprowadzić co najmniej jeden warunek wyszukiwania.";
    }
	


}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
	 <link rel="stylesheet" href="styl.css"> <!-- Link do pliku CSS -->
    <title>Wyszukiwarka Perfum</title>
</head>
<body


    <h1></h1>
    <form method="post" action="">
	
	 <?php
    
    if (isset($_SESSION['blad'])) {
        // Wyświetl komunikat błędu
        echo '<div style="color: red;">' . $_SESSION['blad'] . '</div>';

       
        unset($_SESSION['blad']);
    }
    ?>
        
        <label>Cena:</label>
        <input type="text" name="cena" placeholder="Wpisz cenę">

        <label>Data wydania:</label>
        <input type="text" name="data_wydania" placeholder="Wpisz datę wydania">

        <!-- Pora -->
        <label>Pora:</label>
        <select name="pora">
            <option value="">Wybierz</option>
            <option value="dzień">Dzień</option>
            <option value="noc">Noc</option>
        </select>

        <!-- Płeć -->
        <label>Płeć:</label>
        <select name="plec">
            <option value="">Wybierz</option>
            <option value="kobieta">Kobieta</option>
            <option value="mężczyzna">Mężczyzna</option>
            <option value="uniseks">Uniseks</option>
        </select>

        <!-- Trwałość -->
        <label>Trwałość:</label>
        <select name="trwalosc">
            <option value="">Wybierz</option>
            <option value="bardzo słaba">Bardzo słaba</option>
            <option value="słaba">Słaba</option>
            <option value="przeciętna">Przeciętna</option>
            <option value="duża">Duża</option>
            <option value="bardzo duża">Bardzo duża</option>
        </select>

        <!-- Projekcja -->
        <label>Projekcja:</label>
        <select name="projekcja">
            <option value="">Wybierz</option>
            <option value="bliskoskórna">Bliskoskórna</option>
            <option value="łagodna">Łagodna</option>
            <option value="przeciętna">Przeciętna</option>
            <option value="duża">Duża</option>
            <option value="olbrzymia">Olbrzymia</option>
        </select>

        <!-- Typ -->
        <label>Typ:</label>
        <select name="typ">
            <option value="">Wybierz</option>
            <option value="mainstream">Mainstream</option>
            <option value="nisza">Nisza</option>
        </select>

        <!-- Pora roku (checkbox) -->
        <label>Pora roku:</label>
        <input type="checkbox" name="pora_roku[]" value="wiosna"> Wiosna
        <input type="checkbox" name="pora_roku[]" value="lato"> Lato
        <input type="checkbox" name="pora_roku[]" value="jesień"> Jesień
        <input type="checkbox" name="pora_roku[]" value="zima"> Zima

        <!-- Kategoria zapachu (checkbox) -->
        <label>Kategoria zapachu:</label>
        <input type="checkbox" name="kategoria_zapachu[]" value="na codzień"> Na codzień
        <input type="checkbox" name="kategoria_zapachu[]" value="eleganckie"> Eleganckie
        <input type="checkbox" name="kategoria_zapachu[]" value="na wyjścia i imprezy"> Na wyjścia i imprezy
        <input type="checkbox" name="kategoria_zapachu[]" value="na randkę"> Na randkę
        <input type="checkbox" name="kategoria_zapachu[]" value="kreatywne"> Kreatywne

        <!-- Typ zapachu (checkbox) -->
        <label>Typ zapachu:</label>
        <input type="checkbox" name="typ_zapachu[]" value="świeże"> Świeże
        <input type="checkbox" name="typ_zapachu[]" value="słodkie"> Słodkie
        <input type="checkbox" name="typ_zapachu[]" value="słone"> Słone
        <input type="checkbox" name="typ_zapachu[]" value="fougere"> Fougere
        <input type="checkbox" name="typ_zapachu[]" value="owocowe"> Owocowe
        <input type="checkbox" name="typ_zapachu[]" value="skórzane"> Skórzane
        <input type="checkbox" name="typ_zapachu[]" value="metaliczne"> Metaliczne
        <input type="checkbox" name="typ_zapachu[]" value="zielone"> Zielone
        <input type="checkbox" name="typ_zapachu[]" value="smakowite"> Smakowite
        <input type="checkbox" name="typ_zapachu[]" value="wodne"> Wodne
        <input type="checkbox" name="typ_zapachu[]" value="kwiatowe"> Kwiatowe
        <input type="checkbox" name="typ_zapachu[]" value="szyprowe"> Szyprowe
        <input type="checkbox" name="typ_zapachu[]" value="orientalne"> Orientalne
        <input type="checkbox" name="typ_zapachu[]" value="aromatyczne"> Aromatyczne
        <input type="checkbox" name="typ_zapachu[]" value="drzewne"> Drzewne

        <br>
        <input type="submit" value="Szukaj">
    </form>
    <!-- Stopka -->
    <footer>
        <p>&copy; myPerfume 2023. Wszelkie prawa zastrzeżone. <a href="politykaprywatnosci.php">Polityka prywatności</a></p>
    </footer>

</body>
</html>