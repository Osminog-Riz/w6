<?php
header('Content-Type: text/html; charset=UTF-8');
$db_host="localhost";
$db_user = "u16355";
$db_password = "2629125";
$db_base ="u16355";
$db_table = "app6";

$db = new PDO('mysql:host=localhost;dbname=u16355', $db_user, $db_password, array(PDO::ATTR_PERSISTENT => true));

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    $messages = array();
    $messages['save'] = '';     //успешное отправление данных
    $messages['notsave'] = '';  // ошибка отправления данных
    $messages['name'] = '';     //ошибка в имени
    $messages['email'] = '';    // ошибка в email
    $messages['powers'] = '';   // ошибка в способностях
    $messages['bio'] = '';      //ошибка в биографии
    $messages['check'] = '';    //ошибка а чекбоксе

  if (!empty($_COOKIE['save'])) {
    setcookie('save', '', 100000);
    setcookie('login', '', 100000);
    setcookie('pass', '', 100000);
    $messages['save'] = 'Результаты отправлены в базу. Ня:3';
    if (!empty($_COOKIE['pass'])) {   //если есть пароль
        $messages['savelogin'] = sprintf(' Ты можешь <a href="login.php"> войти</a> с логином <strong>%s</strong> и паролем <strong>%s</strong> для изменения данных.',
          strip_tags($_COOKIE['login']),
          strip_tags($_COOKIE['pass']));
      }
  }

  if (!empty($_COOKIE['notsave'])) {  //ошибка сохранения
    setcookie('notsave', '', 100000);
    $messages['notsave'] = 'Бака! Ошибка отправления в базу.';
  }

  $errors = array();
  $errors['name'] = empty($_COOKIE['name_error']) ? '' : $_COOKIE['name_error'];
  $errors['email'] = !empty($_COOKIE['email_error']);
  $errors['powers'] = !empty($_COOKIE['powers_error']);
  $errors['bio'] = !empty($_COOKIE['bio_error']);
  $errors['check'] = !empty($_COOKIE['check_error']);

  if ($errors['name'] == 'null') { //проверка ошибок в имени
    setcookie('name_error', '', 100000);
    $messages['name'] = '<div>Заполни имя!</div>';
  }
  else if ($errors['name'] == 'incorrect') {
      setcookie('name_error', '', 100000);
      $messages['name'] = '<div>Бака! Недопустимые символы.</div>';
  }

  if ($errors['email']) { //ошибка в почте
    setcookie('email_error', '', 100000);
    $messages['email'] = '<div>Заполни почту.</div>';
  }

  if ($errors['powers']) {//ошибка в суперсвопобности
    setcookie('powers_error', '', 100000);
    $messages['povers'] = '<div>Выбери хотя бы одну сверхспособность.</div>';
  }

  if ($errors['bio']) { //ошибка в бографии
    setcookie('bio_error', '', 100000);
    $messages['bio'] = '<div>Хочу что-нибудь узнать о тебе, братик!</div>';
  }

  if ($errors['check']) { //ошибка чекбокса
    setcookie('check_error', '', 100000);
    $messages['check'] = '<div>Ты не можешь отправить форму не согласившись встепить в клуб.</div>';
  }

  $values = array();
  $powers = array(); //массив суперспособности
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
    try { //достаем даннные из базы
      $stmt = $db->prepare("SELECT * FROM app6 WHERE uid = ?");
      $stmt->execute(array(
        $_SESSION['login']
      ));
      $user_data = $stmt->fetch(); //получаем в виде массива данных
      $values['name'] = strip_tags($user_data['name']);
      $values['email'] = strip_tags($user_data['email']);
      $values['year'] = strip_tags($user_data['year']);
      $values['sex'] = strip_tags($user_data['sex']);
      $values['limbs'] = strip_tags($user_data['limbs']);
      $values['bio'] = strip_tags($user_data['bio']);
      $powers_value = explode(", ", $user_data['powers']);

      $values['powers'] = []; //массив сверхспособности
      foreach ($powers_value as $power) {
        if (!empty($powers[$power])) {
          $values['powers'][$power] = $power;
        }
      }

    } catch(PDOException $e) { //вывод ошибки при получении из бд
      setcookie('notsave', 'Опа, Ошибка: ' . $e->getMessage());
      exit();
    }
  }
  include('form.php');
}
//завершение if  методом "GET"
//если запрос методом "POST"
else {
  $errors = FALSE; //логическая переменная для проверки
  if (empty($_POST['name'])) {
    setcookie('name_error', 'null', time() + 24 * 60 * 60);
    $errors = TRUE; //если есть ошибка
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

  $powers = array(); //для суперспособности
  foreach ($_POST['powers'] as $key => $value) {
      $powers[$key] = $value; //заполняем значениями из глобальной переменной $_POST
  }
  if (!sizeof($powers)) { //если не выбраны суперспособности
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
  //для значений по умолчанию
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

  //здеснь начинаются проблемы
  if (!empty($_COOKIE[session_name()]) && session_start() && !empty($_SESSION['login'])) {
        try {
            $stmt = $db->prepare("UPDATE app6 SET name = ?, email = ?, year = ?, sex = ?, limbs = ?, powers = ?, bio = ? WHERE login = ?");
            $stmt->execute(array(
                $_POST['name'],
                $_POST['email'],
                $_POST['year'],
                $_POST['sex'],
                $_POST['limbs'],
                implode(', ', $_POST['powers']),
                $_POST['bio'],
                $_SESSION['login']
            ));
        } catch (PDOException $e) {
            setcookie('notsave', 'Ошибка: ' . $e->getMessage());
            exit();
        }

    } else {
        $login = uniqid("id");
        $pass = rand(100000, 999999);
        setcookie('login', $login);
        setcookie('pass', $pass);
        try {
            $stmt_form = $db->prepare("INSERT INTO app6 SET login = ?, pass = ?, name = ?, email = ?, year = ?, sex = ?, limbs = ?, powers = ?, bio = ?");
            $stmt_form->execute(array(
                $login,
                hash('sha256', $pass, false),
                $_POST['name'],
                $_POST['email'],
                $_POST['year'],
                $_POST['sex'],
                $_POST['limbs'],
                implode(', ', $_POST['powers']),
                $_POST['bio']
            ));
        } catch (PDOException $e) {
            setcookie('notsave', 'Ошибка: ' . $e->getMessage());
            exit();
        }
    }
  setcookie('save', '1');
  header('Location: ./');
}
