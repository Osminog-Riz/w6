<?php
header('Content-Type: text/html; charset=UTF-8');

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
  $messages = array();
  if (!empty($_COOKIE['save'])) {
    setcookie('save', '', 100000);
    $messages[] = 'Результаты отправлены в базу. Ня:3';
  }
  if (!empty($_COOKIE['notsave'])) {
    setcookie('notsave', '', 100000);
    $messages[] = 'Бака! Ошибка отправления в базу.';
  }
  if (!empty($_COOKIE['pass'])) {
      $messages['savelogin'] = sprintf(' Ты можешь <a href="login.php"> войти</a> с логином <strong>%s</strong> и паролем <strong>%s</strong> для изменения данных.',
        strip_tags($_COOKIE['login']),
        strip_tags($_COOKIE['pass']));
    }

  $errors = array();
  $errors['name'] = empty($_COOKIE['name_error']) ? '' : $_COOKIE['name_error'];
  $errors['email'] = !empty($_COOKIE['email_error']);
  $errors['powers'] = !empty($_COOKIE['powers_error']);
  $errors['bio'] = !empty($_COOKIE['bio_error']);
  $errors['check'] = !empty($_COOKIE['check_error']);

  if ($errors['name'] == 'null') {
    setcookie('name_error', '', 100000);
    $messages[] = '<div>Заполни имя.</div>';
  }
  else if ($errors['name'] == 'incorrect') {
      setcookie('name_error', '', 100000);
      $messages[] = '<div>Бака! Недопустимые символы.</div>';
  }

  if ($errors['email']) {
    setcookie('email_error', '', 100000);
    $messages[] = '<div>Заполни почту.</div>';
  }

  if ($errors['powers']) {
    setcookie('powers_error', '', 100000);
    $messages[] = '<div>Выбери хотя бы одну сверхспособность.</div>';
  }

  if ($errors['bio']) {
    setcookie('bio_error', '', 100000);
    $messages[] = '<div>Хочу что-нибудь узнать о тебе, братик!</div>';
  }

  if ($errors['check']) {
    setcookie('check_error', '', 100000);
    $messages[] = '<div>Ты не можешь отправить форму не согласившись с контрактом.</div>';
  }

  $values = array();
  $powers = array();
  $powers['levit'] = "levitation";
  $powers['tp'] = "immortality";
  $powers['walk'] = "walls-walking";
  $powers['vision'] = "invisibility";
  $values['name'] = empty($_COOKIE['name_value']) ? '' : $_COOKIE['name_value'];
  $values['email'] = empty($_COOKIE['email_value']) ? '' : $_COOKIE['email_value'];
  $values['year'] = empty($_COOKIE['year_value']) ? '' : $_COOKIE['year_value'];
  $values['sex'] = empty($_COOKIE['sex_value']) ? 'male' : $_COOKIE['sex_value'];
  $values['limbs'] = empty($_COOKIE['limbs_value']) ? '2' : $_COOKIE['limbs_value'];
  $values['bio'] = empty($_COOKIE['bio_value']) ? '' : $_COOKIE['bio_value'];
  if (!empty($_COOKIE['powers_value'])) {
      $powers_value = json_decode($_COOKIE['powers_value']);
  }
  $values['powers'] = [];
  if (isset($powers_value) && is_array($powers_value)) {
      foreach ($powers_value as $power) {
          if (!empty($powers[$power])) {
              $values['powers'][$power] = $power;
          }
      }
  }

  if (!empty($_COOKIE[session_name()]) && session_start() && !empty($_SESSION['login'])) {
    $messages['save'] = ' ';
    $messages['savelogin'] = 'Вход с логином '.$_SESSION['login'];
    try {
      $stmt = $db->prepare("SELECT * FROM app4 WHERE uid = ?");
      $stmt->execute(array(
        $_SESSION['login']
      ));
      $user_data = $stmt->fetch();
      $values['name'] = strip_tags($user_data['name']);
      $values['email'] = strip_tags($user_data['email']);
      $values['year'] = strip_tags($user_data['age']);
      $values['sex'] = strip_tags($user_data['sex']);
      $values['limbs'] = strip_tags($user_data['limbs']);
      $values['bio'] = strip_tags($user_data['bio']);
      $powers_value = explode(", ", $user_data['powers']);

      $values['powers'] = [];
      foreach ($powers_value as $power) {
        if (!empty($powers[$power])) {
          $values['powers'][$power] = $power;
        }
      }

    } catch(PDOException $e) {
      setcookie('notsave', 'Опа, Ошибка: ' . $e->getMessage());
      exit();
    }
  }
  include('form.php');
}
else {
  $errors = FALSE;
  if (empty($_POST['name'])) {
    setcookie('name_error', 'null', time() + 24 * 60 * 60);
    $errors = TRUE;
  }
  else if (!preg_match("#^[aA-zZ0-9-]+$#", $_POST["name"])) {
      setcookie('name_error', 'incorrect', time() + 24 * 60 * 60);
      $errors = TRUE;
  }
  else {setcookie('name_value', $_POST['name'], time() + 30 * 24 * 60 * 60);}

  if (empty($_POST['email'])) {
    setcookie('email_error', '1', time() + 24 * 60 * 60);
    $errors = TRUE;
  }
  else {setcookie('email_value', $_POST['email'], time() + 30 * 24 * 60 * 60);}

  $powers = array();
  foreach ($_POST['powers'] as $key => $value) {
      $powers[$key] = $value;
  }
  if (!sizeof($powers)) {
    setcookie('powers_error', '1', time() + 24 * 60 * 60);
    $errors = TRUE;
  }
  else {setcookie('powers_value', json_encode($powers), time() + 30 * 24 * 60 * 60);}

  if (empty($_POST['bio'])) {
    setcookie('bio_error', '1', time() + 24 * 60 * 60);
    $errors = TRUE;
  }
  else {setcookie('bio_value', $_POST['bio'], time() + 30 * 24 * 60 * 60);  }

  if (empty($_POST['check'])) {
    setcookie('check_error', '1', time() + 24 * 60 * 60);
    $errors = TRUE;
  }
  setcookie('year_value', $_POST['year'], time() + 30 * 24 * 60 * 60);
  setcookie('sex_value', $_POST['sex'], time() + 30 * 24 * 60 * 60);
  setcookie('limbs_value', $_POST['limbs'], time() + 30 * 24 * 60 * 60);

  if ($errors) {
    header('Location: index.php');
    exit();
  }
  else {
    setcookie('name_error', '', 100000);
    setcookie('email_error', '', 100000);
    setcookie('powers_error', '', 100000);
    setcookie('bio_error', '', 100000);
    setcookie('check_error', '', 100000);
  }

    $db_host="localhost";
    $db_user = "u16355";
    $db_password = "2629125";
    $db_base ="u16355";
    $db_table = "app4";

    $name = $_POST['name'];
    $email = $_POST['email'];
    $age = $_POST['year'];
    $sex = $_POST['sex'];
    $limbs = $_POST['limbs'];
    $bio = $_POST['bio'];
    $check = $_POST['check'];
    $powers_bd = array();
    foreach ($_POST['powers'] as $key => $value) {
        $powers_bd[$key] = $value;
    }
    $powers_string = implode(', ', $powers_bd);
    if (!empty($_COOKIE[session_name()]) && session_start() && !empty($_SESSION['login'])) {
    try {
        $db = new PDO('mysql:host=localhost;dbname=u16355', $db_user, $db_password, array(PDO::ATTR_PERSISTENT => true));
        $statement = $db->prepare("INSERT INTO ".$db_table." (name, email, age, sex, limbs, powers, bio) VALUES ('$name','$email',$age,'$sex',$limbs,'$powers_string','$bio')");
        $statement = $db->prepare('INSERT INTO '.$db_table.' (name, email, age, sex, limbs, powers, bio) VALUES (:name, :email, :age, :sex, :limbs, :powers, :bio)');
        $statement->execute([
            'name' => $name,
            'email' => $email,
            'age' => $age,
            'sex' => $sex,
            'limbs' => $limbs,
            'powers' => $powers_string,
            'bio' => $bio
      //      $_SESSION['login']
        ]);
        setcookie('save', '1');
    } catch (PDOException $e) {
        setcookie('notsave', '1');
    }}
    else {
    $login = uniqid("id");
    $pass = rand(100000, 999999);
    setcookie('login', $login);
    setcookie('pass', $pass);
    try {
      $stmt_form = $db->prepare("INSERT INTO form5 SET uid = ?, name = ?, email = ?, age = ?, sex = ?, limbs = ?, powers = ?, bio = ?");
      $statement->execute([
          'name' => $name,
          'email' => $email,
          'age' => $age,
          'sex' => $sex,
          'limbs' => $limbs,
          'powers' => $powers_string,
          'bio' => $bio
      ]);
      $stmt_user = $db->prepare("INSERT INTO users SET login = ?, pass = ?");
      $stmt_user->execute(array(
        $login,
        hash('sha256', $pass, false)
      ));
    } catch(PDOException $e) {
      setcookie('notsave', 'О, нет, Ошибка: ' . $e->getMessage());
      exit();
    }
  }
  header('Location: index.php');
}
