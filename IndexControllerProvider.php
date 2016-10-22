<?php

use Silex\Application;
use Silex\ControllerProviderInterface;
use Symfony\Component\HttpFoundation\Request;

class IndexControllerProvider implements ControllerProviderInterface
{

    public function connect(Application $app)
    {
        $controllers = $app['controllers_factory'];
        $controllers->post('/add', function (Application $app, Request $request) {
            return $this->addController($app, $request);
        });
        $controllers->get('/', function (Application $app) {
            return $this->indexController($app);
        });
        $controllers->get('/delete/{id}', function (Application $app, $id) {
            return $this->deleteController($app, $id);
        });
        return $controllers;
    }

    private function addController(Application $app, Request $request)
    {
        $keys = AppData::Instance()->keys;
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
    }

    private function indexController(Application $app)
    {
        $sql = "SELECT s.id, s.class, s.sex, p.points_total, p.points_1_10, p.points_11_15 FROM students s LEFT JOIN points p ON p.student_id = s.id ORDER BY s.id DESC";
        $students = $app['db']->fetchAll($sql);
        return $app['twig']->render('index.twig', array(
            'students' => $students,
            'class' => $app['session']->get('class')
        ));
    }

    private function deleteController(Application $app, $id)
    {
        $app['db']->delete('answers', array('student_id' => (int)$id));
        $app['db']->delete('points', array('student_id' => (int)$id));
        $app['db']->delete('students', array('id' => (int)$id));
        return $app->redirect('/stats');
    }
}