<?php

	session_start();
	
	if (isset($_POST['email']))
	{
		//zmienna sprawdzająca czy wszystko jest okej
		$wszystko_OK=true;
		
		//Sprawdzamy nick
		$nick = $_POST['nick'];
		
		//Sprawdzenie długości nicka
		if ((strlen($nick)<5) || (strlen($nick)>15))
		{
			$wszystko_OK=false;
			$_SESSION['error_nick']="<span style= 'color:red'>Nick musi nie może mieć mniej niż 5 znaków, ani więcej niż 15</span>";
		}
		
		if (ctype_alnum($nick)==false)
		{
			$wszystko_OK=false;
			$_SESSION['error_nick']="<span style= 'color:red'>Nick to muszą być litery lub cyfry. Polskie znaki są niedozwolone!!</span>";
		}
		
		// Czy email jest ok
		$email = $_POST['email'];
		$emailB = filter_var($email, FILTER_SANITIZE_EMAIL);
		
		if ((filter_var($emailB, FILTER_VALIDATE_EMAIL)==false) || ($emailB!=$email))
		{
			$wszystko_OK=false;
			$_SESSION['e_email']="<span style= 'color:red'>Adres email jest niepoprawny!";
		}
		
		//Sprawdź poprawność hasła
		$haslopierwsze = $_POST['haslopierwsze'];
		$haslodrugie = $_POST['haslodrugie'];
		
		if ((strlen($haslopierwsze)<8) || (strlen($haslopierwsze)>20))
		{
			$wszystko_OK=false;
			$_SESSION['error_haslo']="<span style= 'color:red'> Hasło musi posiadać od 8 do 20 znaków!</span>";
		}
		
		if ($haslopierwsze!=$haslodrugie)
		{
			$wszystko_OK=false;
			$_SESSION['error_haslo']="<span style= 'color:red'> Podane hasła nie są identyczne!</span>";
		}	

		$haslo_hash = password_hash($haslopierwsze, PASSWORD_DEFAULT);
		
		//Czy zaakceptowano regulamin?
		if (!isset($_POST['regulamin']))
		{
			$wszystko_OK=false;
			$_SESSION['error_regulamin']="<span style= 'color:red'>Potwierdź akceptację regulaminu!</span>";
		}				
		
		//Bot or not? Oto jest pytanie!
		$sekret = "6LcE5h0pAAAAAHsCMdstKw3n2SStSubg5JfwHvAi";
		
		$sprawdz = file_get_contents('https://www.google.com/recaptcha/api/siteverify?secret='.$sekret.'&response='.$_POST['g-recaptcha-response']);
		
		$odpowiedz = json_decode($sprawdz);
		
		if ($odpowiedz->success==false)
		{
			$wszystko_OK=false;
			$_SESSION['error_bot']="<span style= 'color:red'> Potwierdź, że nie jesteś botem!</span>";
		}		
		
		//Zapamiętaj wprowadzone dane
		$_SESSION['zapamietany_nick'] = $nick;
		$_SESSION['zapamietany_email'] = $email;
		$_SESSION['zapamietany_haslopierwsze'] = $haslopierwsze;
		$_SESSION['zapamietany_haslodrugie'] = $haslodrugie;
		if (isset($_POST['regulamin'])) $_SESSION['zapamietany_regulamin'] = true;
		
		require_once "laczeniezbazadanych.php";
		mysqli_report(MYSQLI_REPORT_STRICT);
		
		try 
		{
			$polaczenie = new mysqli($host, $db_user, $db_password, $db_name);
			if ($polaczenie->connect_errno!=0)
			{
				throw new Exception(mysqli_connect_errno());
			}
			else
			{
				$rezultat = $polaczenie->query("SELECT id_u FROM uzytkownik WHERE email='$email'");
				
				if (!$rezultat) throw new Exception($polaczenie->error);
				
				$ile_takich_maili = $rezultat->num_rows;
				if($ile_takich_maili>0)
				{
					$wszystko_OK=false;
					$_SESSION['e_email']="<span style= 'color:red'>Istnieje już konto przypisane do tego adresu e-mail!</span>";
				}		

				$rezultat = $polaczenie->query("SELECT id_u FROM uzytkownik WHERE nick='$nick'");
				
				if (!$rezultat) throw new Exception($polaczenie->error);
				
				$ile_takich_nickow = $rezultat->num_rows;
				if($ile_takich_nickow>0)
				{
					$wszystko_OK=false;
					$_SESSION['error_nick']="<span style= 'color:red'> Istnieje już gracz o takim nicku! Wybierz inny.</span>";
				}
				
				if ($wszystko_OK==true)
				{
					
					if ($polaczenie->query("INSERT INTO uzytkownik VALUES (NULL, 'uzytkownik' ,  '$nick','$email', '$haslo_hash')"))
					{
						$_SESSION['zalogowany'] = true;
						$_SESSION['id_u'] = $polaczenie->insert_id;
						$_SESSION['nick'] = $nick;
						$_SESSION['udanarejestracja']=true;
						header('Location: index.php');
					}
					else
					{
						throw new Exception($polaczenie->error);
					}
					
				}
				
				$polaczenie->close();
			}
			
		}
		catch(Exception $e)
		{
			echo '<span style="color:red;">Błąd serwera! Przepraszamy za niedogodności i prosimy o rejestrację w innym terminie!</span>';
			echo '<br />Informacja developerska: '.$e;
		}
		
	}
	
	
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styl.css">
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

    <!-- Tytuł na środku strony -->
    <div class="centered-title">
        myPerfume
    </div>

    <!-- Treść strony -->

    
    <form method="post">
    
        <label for="nick">Nickname: </label><br /> 
        <input type="text" value="<?php
            if (isset($_SESSION['zapamietany_nick'])) {
                echo $_SESSION['zapamietany_nick'];
                unset($_SESSION['zapamietany_nick']);
            }
        ?>" name="nick" /><br />
        
        <?php
            if (isset($_SESSION['error_nick'])) {
                echo '<div class="error">'.$_SESSION['error_nick'].'</div>';
                unset($_SESSION['error_nick']);
            }
        ?>
        
        <label for="email">E-mail: </label><br /> 
        <input type="text" value="<?php
            if (isset($_SESSION['zapamietany_email'])) {
                echo $_SESSION['zapamietany_email'];
                unset($_SESSION['zapamietany_email']);
            }
        ?>" name="email" /><br />
        
        <?php
            if (isset($_SESSION['e_email'])) {
                echo '<div class="error">'.$_SESSION['e_email'].'</div>';
                unset($_SESSION['e_email']);
            }
        ?>
        
        <label for="haslopierwsze">Twoje hasło: </label><br /> 
        <input type="password"  value="<?php
            if (isset($_SESSION['zapamietany_haslopierwsze'])) {
                echo $_SESSION['zapamietany_haslopierwsze'];
                unset($_SESSION['zapamietany_haslopierwsze']);
            }
        ?>" name="haslopierwsze" /><br />
        
        <?php
            if (isset($_SESSION['error_haslo'])) {
                echo '<div class="error">'.$_SESSION['error_haslo'].'</div>';
                unset($_SESSION['error_haslo']);
            }
        ?>        
        
        <label for="haslodrugie">Powtórz hasło: </label><br /> 
        <input type="password" value="<?php
            if (isset($_SESSION['zapamietany_haslodrugie'])) {
                echo $_SESSION['zapamietany_haslodrugie'];
                unset($_SESSION['zapamietany_haslodrugie']);
            }
        ?>" name="haslodrugie" /><br />
        
     <div class="checkbox-group">
			<label for="regulamin">Akceptuję regulamin oraz politykę prywatności</label>
            <input type="checkbox" name="regulamin" id="regulamin" />
            
        </div>

        <?php
            if (isset($_SESSION['error_regulamin'])) {
                echo '<div class="error">'.$_SESSION['error_regulamin'].'</div>';
                unset($_SESSION['error_regulamin']);
            }
        ?>  
        
        <div class="g-recaptcha" data-sitekey="6LcE5h0pAAAAABfg7Od6eQU_SojQZvmYFVp0fjm2"></div>
        
        <?php
            if (isset($_SESSION['error_bot'])) {
                echo '<div class="error">'.$_SESSION['error_bot'].'</div>';
                unset($_SESSION['error_bot']);
            }
        ?>  
        
        <br />
        
        <input type="submit" value="Zarejestruj się" />

 
        <a href="stronalogowania.php" id="login-link">Masz już konto? Zaloguj się!</a>
        <script src="https://www.google.com/recaptcha/api.js" async defer></script>
    </form>
    <footer>
        <p>&copy; myPerfume 2023. Wszelkie prawa zastrzeżone. <a href="#">Polityka prywatności</a></p>
    </footer>

</body>
</html>