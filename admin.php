<?php
if (empty($_SERVER['PHP_AUTH_USER']) ||
    empty($_SERVER['PHP_AUTH_PW']) ||
    $_SERVER['PHP_AUTH_USER'] != 'admin' ||
    md5($_SERVER['PHP_AUTH_PW']) != md5('admin')) {
    header('HTTP/1.1 401 Unanthorized');
    header('WWW-Authenticate: Basic realm="My site"');
    print('<h1>401 Требуется авторизация</h1>');
    exit();
}
// Используем метод Double Submit Cookie отсюда https://habr.com/ru/post/318748/
// Создаем токен и помещаем его в куки, а также в input формы.
$token = md5('ilovekubsu' . $_SERVER['PHP_AUTH_USER']);
setcookie('token', $token, time() + 24 * 60 * 60);

$db_user = 'u16355';   // Логин БД
$db_pass = '2629125';  // Пароль БД

$db = new PDO('mysql:host=localhost;dbname=u16355', $db_user, $db_pass, array(
    PDO::ATTR_PERSISTENT => true
));
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if ($_POST['token'] === $_COOKIE['token']) {
    try {
        $stmt = $db->prepare('DELETE FROM web6 WHERE login = ?');
        $stmt->execute(array(
            $_POST['remove']
        ));
    } catch (PDOException $e) {
        echo 'Ошибка: ' . $e->getMessage();
        exit();
    }
}
}
try {
    $stmt = $db->query('SELECT * FROM app6');
    ?>
    <head>
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Админ панель | Задание 6</title>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bulma@0.8.2/css/bulma.min.css">
        <link rel="stylesheet" href="style.css">
    </head>
    <body>
    <form action="" method="post">
        <input type="hidden" name="token" value="<?php print($token); ?>">
          <table class="table is-hoverable is-fullwidth">
              <thead>
            <tr>
                <th>Логин</th>
                <th>Пароль</th>
                <th>Имя</th>
                <th>Email</th>
                <th>Год гождения</th>
                <th>Пол</th>
                <th>Количество конечностей</th>
                <th>Сверхспособности</th>
                <th>Биография</th>
                <th>Удалить</th>
            </tr>
            </thead>
            <tbody>
            <?php
           while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                  print('<tr>');
                  foreach ($row as $cell) {
                      print('<td>' . strip_tags($cell) . '</td>');
                  }
                  print('<td><button class="button is-info is-small is-danger is-light" name="remove" type="submit" value="'
                  . strip_tags($row['login'])
                  . '">x</button></td>');
                  print('</tr>');
              }

            ?>
            </tbody>
        </table>
    </form>
    </body>
    <?php
} catch (PDOException $e) {
    echo 'Ошибка: ' . $e->getMessage();
    exit();
}
