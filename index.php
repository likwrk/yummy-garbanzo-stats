<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$questions = array(
    1 => array(1 => 'а', 2 => 'б', 3 => 'в', 4 => 'г', 5 => 'д', 6 => 'е'),
    2 => array(1 => 'а', 2 => 'б', 3 => 'в', 4 => 'г'),
    3 => array(1 => 'а', 2 => 'б', 3 => 'в', 4 => 'г'),
    4 => array(1 => 'а', 2 => 'б', 3 => 'в', 4 => 'г'),
    5 => array(1 => 'а', 2 => 'б', 3 => 'в', 4 => 'г', 5 => 'д'),
    6 => array(1 => 'а', 2 => 'б', 3 => 'в', 4 => 'г', 5 => 'д'),
    7 => array(1 => 'а', 2 => 'б', 3 => 'в', 4 => 'г', 5 => 'д', 6 => 'е'),
    8 => array(1 => 'а', 2 => 'б', 3 => 'в'),
    9 => array(1 => 'а', 2 => 'б', 3 => 'в', 4 => 'г', 5 => 'д'),
    10 => array(1 => 'а', 2 => 'б', 3 => 'в', 4 => 'г'),
    11 => array(1 => 'а', 2 => 'б', 3 => 'в', 4 => 'г'),
    12 => array(1 => 'а', 2 => 'б', 3 => 'в', 4 => 'г'),
    13 => array(1 => 'а', 2 => 'б', 3 => 'в', 4 => 'г'),
    14 => array(1 => 'а', 2 => 'б', 3 => 'в', 4 => 'г', 5 => 'д'),
    15 => array(1 => 'а', 2 => 'б', 3 => 'в', 4 => 'г', 5 => 'д')
);

$keysChar = array(
    1 => 'a',
    2 => 'б',
    3 => 'в',
    4 => 'г',
    5 => 'д',
    6 => 'е'
);

$results = array(
    'points_1_10' => array(
        'title' => 'Культура ЗОЖ в отношении привычек',
        'levels' => array(
            '0_6' => 'Высокая',
            '7_12' => 'Достаточная',
            '13_19' => 'Удовлитворительная',
            '20_100' => 'Низкая'
        )
    ),
    'points_11_15' => array(
        'title' => 'Проявление вредных привычек',
        'levels' => array(
            '0_3' => 'Отсутствие',
            '4_6' => 'Незначительное',
            '7_9' => 'Умеренное',
            '10_100' => 'Существенное'
        )
    ),
    'points_total' => array(
        'title' => 'Отношение к вредным привычкам',
        'levels' => array(
            '0_9' => 'Негативное',
            '10_19' => 'Нейтральное',
            '20_29' => 'Умеренно-позитивные',
            '30_100' => 'Позитивное'
        )
    )
);

$keys = array(
    1 => array(1 => 1, 2 => 0, 3 => 2, 4 => 1, 5 => 2, 6 => 0),
    2 => array(1 => 2, 2 => 1, 3 => 1, 4 => 2),
    3 => array(1 => 0, 2 => 0, 3 => 1, 4 => 3),
    4 => array(1 => 2, 2 => 0, 3 => 0, 4 => 1),
    5 => array(1 => 1, 2 => 3, 3 => 1, 4 => 1),
    6 => array(1 => 1, 2 => 1, 3 => 1, 4 => 1, 5 => 1),
    7 => array(1 => 0, 2 => 2, 3 => 0, 4 => 1, 5 => 0, 6 => 1),
    8 => array(1 => 0, 2 => 0, 3 => 1),
    9 => array(1 => 2, 2 => 1, 3 => 0, 4 => 0, 5 => 1),
    10 => array(1 => 0, 2 => 0, 3 => 1, 4 => 1),
    11 => array(1 => 0, 2 => 0, 3 => 1, 4 => 2),
    12 => array(1 => 0, 2 => 1, 3 => 2, 4 => 4),
    13 => array(1 => 1, 2 => 2, 3 => 0, 4 => 1),
    14 => array(1 => 0, 2 => 1, 3 => 2, 4 => 2, 5 => 0),
    15 => array(1 => 0, 2 => 1, 3 => 2, 4 => 3, 5 => 0)
);

require_once __DIR__.'/vendor/autoload.php';
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

$appUrl = "http://test.wp/stats";
$app = new Silex\Application();
$app->register(new Silex\Provider\DoctrineServiceProvider(), array(
    'db.options' => array(
        'driver'   => 'pdo_mysql',
        'dbname'   => 'katestats',
        'user'     => 'kate',
        'password' => '123',
        'charset'  => 'utf8'
    ),
));
$app->register(new Silex\Provider\SessionServiceProvider());
$app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.path' => __DIR__.'/views',
));
$app['twig'] = $app->share($app->extend('twig', function($twig, $app) {
    global $questions, $keys, $appUrl, $results, $keysChar;
    $twig->addGlobal('questions', $questions);
    $twig->addGlobal('keys', $keys);
    $twig->addGlobal('appUrl', $appUrl);
    $twig->addGlobal('keysChar', $keysChar);
    $twig->addGlobal('results', $results);
    return $twig;
}));
$app['debug'] = true;

$app->post('/add', function(Request $request) use($app) {
    global $keys;
    $answers = $request->get('answer');
    $class = $request->get('class');
    $sex = $request->get('sex');
    $app['db']->insert('students', array(
        'sex' => $sex,
        'class' => $class
    ));
    $student_id = $app['db']->lastInsertId();
    $points_total = 0;
    $points_1_10 = 0;
    $points_11_15 = 0;
    foreach ($answers as $answer) {
        $qa = explode('_', $answer);
        $q = $qa[0];
        $a = $qa[1];
        $app['db']->insert('answers', array(
            'student_id' => $student_id,
            'question' => $q,
            'answer' => $a
        ));
        $points_total += $keys[$q][$a];
        if ($q <= 10) {
            $points_1_10 += $keys[$q][$a];
        }
        if ($q > 10) {
            $points_11_15 += $keys[$q][$a];
        }
    }
    $app['db']->insert('points', array(
        'student_id' => $student_id,
        'points_1_10' => $points_1_10,
        'points_11_15' => $points_11_15,
        'points_total' => $points_total
    ));
    $app['session']->set('class', $class);
    return $app->redirect('/stats');
});

$app->get('/', function() use($app) {
    $sql = "SELECT s.id, s.class, s.sex, p.points_total, p.points_1_10, p.points_11_15 FROM students s LEFT JOIN points p ON p.student_id = s.id ORDER BY s.id DESC";
    $students = $app['db']->fetchAll($sql);
    return $app['twig']->render('index.twig', array(
        'students' => $students,
        'class' => $app['session']->get('class')
    ));
});

$app->get('/results', function() use($app) {
    global $results;
    $sexes = array('male', 'female');
    $classes = array();
    $class_total_sql = "SELECT class, count(id) AS num FROM students GROUP BY class";
    $class_male_sql = "SELECT class, count(id) AS num FROM students WHERE sex = 'male' GROUP BY class";
    $class_female_sql = "SELECT class, count(id) AS num FROM students WHERE sex = 'female' GROUP BY class";
    $class_total = $app['db']->fetchAll($class_total_sql);
    $class_male = $app['db']->fetchAll($class_male_sql);
    $class_female = $app['db']->fetchAll($class_female_sql);
    // get count total in class
    foreach ($class_total as $class) {
        if (!isset($classes[$class['class']])) {
            $classes[$class['class']] = array('name' => $class['class']);
        }
        $classes[$class['class']]['total'] = $class['num'];
        $classes[$class['class']]['results'] = array();
        $classes[$class['class']]['male'] = 0;
        $classes[$class['class']]['female'] = 0;
        foreach ($results as $result_key => $result) {
            $classes[$class['class']]['results'][$result_key] = array(
                'title' => $result['title'],
                'levels' => array()
            );
            foreach ($result['levels'] as $level_key => $level_name) {
                $classes[$class['class']]['results'][$result_key]['levels'][$level_key] = array(
                    'title' => $level_name,
                    'male_count' => 0,
                    'female_count' => 0
                );
            }
        }
    }
    // get count male in class
    foreach ($class_male as $class) {
        if (!isset($classes[$class['class']])) {
            $classes[$class['class']] = array('name' => $class['class']);
        }
        $classes[$class['class']]['male'] = $class['num'];
    }
    // get count female in class
    foreach ($class_female as $class) {
        if (!isset($classes[$class['class']])) {
            $classes[$class['class']] = array('name' => $class['class']);
        }
        $classes[$class['class']]['female'] = $class['num'];
    }

    foreach ($results as $result_key => $result) {
        foreach ($result['levels'] as $level_key => $level_name) {
            $borders = explode('_', $level_key);
            $min = $borders[0];
            $max = $borders[1];
            foreach ($sexes as $sex) {
                $sql = 'select s.class, count(s.id) as num from students s left join points p on s.id = p.student_id where s.sex = "'.$sex.'" and p.'.$result_key.' >= '.$min.' and p.'.$result_key.' <= '.$max.' group by s.class';
                $res_data = $app['db']->fetchAll($sql);
                foreach ($res_data as $res) {
                    $classes[$res['class']]['results'][$result_key]['levels'][$level_key][$sex . '_count'] = $res['num'];
                }
            }
        }
    }

    return $app['twig']->render('results.twig', array(
        'classes' => $classes
    ));
});

$app->get('/delete/{id}', function ($id) use($app) {
    $app['db']->delete('answers', array('student_id' => (int)$id));
    $app['db']->delete('points', array('student_id' => (int)$id));
    $app['db']->delete('students', array('id' => (int)$id));
    return $app->redirect('/stats');
});

$app->run();