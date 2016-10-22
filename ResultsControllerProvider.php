<?php

use Silex\Application;
use Silex\ControllerProviderInterface;

class ResultsControllerProvider implements ControllerProviderInterface
{

    private $sexes = ['male', 'female'];
    private $classes = [];
    private $class_list = [];
    private $parallels = [7, 8, 9, 10, 11];
    private $results;
    private $app;

    public function connect(Application $app)
    {
        $this->app = $app;
        $this->results = AppData::Instance()->results;
        $controllers = $app['controllers_factory'];
        $controllers->get('/{klass}', function (Application $app, $klass) {
            return $this->perclassController($app, $klass);
        });
        $controllers->get('/', function (Application $app) {
            return $this->resultsController($app);
        });
        return $controllers;
    }

    public function resultsController(Application $app)
    {
        $this->getSchoolTotals();
        $this->getParallelsTotals();
        $this->getClassesTotals();

        foreach ($this->results as $result_key => $result) {
            foreach ($result['levels'] as $level_key => $level_name) {
                $borders = explode('_', $level_key);
                $min = $borders[0];
                $max = $borders[1];
                foreach ($this->sexes as $sex) {
                    //school
                    $sql_school = 'select "school" as class, count(s.id) as num from students s left join points p on s.id = p.student_id where s.sex = "'.$sex.'" and p.'.$result_key.' >= '.$min.' and p.'.$result_key.' <= '.$max;
                    $res = $this->app['db']->fetchAssoc($sql_school);
                    $this->classes[$res['class']]['results'][$result_key]['levels'][$level_key][$sex . '_count'] = $res['num'];
                    //parallels
                    foreach ($this->parallels as $parallel) {
                        $sql_school = 'select "'.$parallel.'" as class, count(s.id) as num from students s left join points p on s.id = p.student_id where s.sex = "'.$sex.'" and p.'.$result_key.' >= '.$min.' and p.'.$result_key.' <= '.$max. ' and s.class like "'.$parallel.'%"';
                        $res = $this->app['db']->fetchAssoc($sql_school);
                        $this->classes[$res['class']]['results'][$result_key]['levels'][$level_key][$sex . '_count'] = $res['num'];
                    }
                    //classes
                    $sql = 'select s.class, count(s.id) as num from students s left join points p on s.id = p.student_id where s.sex = "'.$sex.'" and p.'.$result_key.' >= '.$min.' and p.'.$result_key.' <= '.$max.' group by s.class';
                    $res_data = $this->app['db']->fetchAll($sql);
                    foreach ($res_data as $res) {
                        $this->classes[$res['class']]['results'][$result_key]['levels'][$level_key][$sex . '_count'] = $res['num'];
                    }
                }
            }
        }

        return $this->app['twig']->render('results.twig', [
            'classes' => $this->classes,
            'class_list' => $this->class_list,
        ]);
    }

    public function perclassController(Application $app, $klass)
    {
        $results = [];
        foreach ($this->sexes as $sex) {
            $results[$sex] = [];
            $sqlCount = 'select count(id) as total from students where class = "' . $klass . '" and sex = "' . $sex . '"';
            $total = (int)$this->app['db']->fetchArray($sqlCount)[0];
            $sql = 'SELECT a.question, a.answer, (count(a.id)/' . $total . ') AS percents 
                    FROM answers a LEFT JOIN students s ON s.id = a.student_id 
                    WHERE s.class = "' . $klass . '" AND s.sex = "' . $sex . '" 
                    GROUP BY a.question, a.answer
                    ORDER BY a.question, a.answer';
            $res_data = $this->app['db']->fetchAll($sql);
            foreach ($res_data as $row) {
                $question_index = $row['question'] - 1;
                if (!isset($results[$sex][$question_index])) {
                    $results[$sex][$question_index] = [];
                }
                $answer_index = $row['answer'] - 1;
                $results[$sex][$question_index][$answer_index] = $row['percents'];

            }
        };

        return $this->app['twig']->render('perclass.twig', [
            'klass' => $klass,
            'results' => $results,
            'letters' => ['а','б','в','г','д','е']
        ]);
    }

    private function getSchoolTotals()
    {
        $sql = [
            "SELECT count(id) AS num FROM students",
            "SELECT count(id) AS num FROM students WHERE sex = 'male'",
            "SELECT count(id) AS num FROM students WHERE sex = 'female'"
        ];
        $data = [];
        foreach ($sql as $s) {
            $data[] = $this->app['db']->fetchAssoc($s);
        }
        $this->initClass('school', 'Школа', $data[0], $data[1], $data[2]);
    }

    private function getParallelsTotals()
    {
        foreach ($this->parallels as $parallel) {
            $sql = [
                "SELECT '$parallel' as class, count(s.id) AS num FROM students s where s.class like '$parallel%'",
                "SELECT '$parallel' as class, count(s.id) AS num FROM students s WHERE s.sex = 'male' and  s.class like '$parallel%'",
                "SELECT '$parallel' as class, count(s.id) AS num FROM students s WHERE s.sex = 'female' and  s.class like '$parallel%'"
            ];
            $data = [];
            foreach ($sql as $s) {
                $data[] = $this->app['db']->fetchAssoc($s);
            }
            $this->initClass($parallel, $parallel, $data[0], $data[1], $data[2]);
        }
    }

    private function getClassesTotals()
    {
        $sql = [
            "SELECT class, count(id) AS num FROM students GROUP BY class",
            "SELECT class, count(id) AS num FROM students WHERE sex = 'male' GROUP BY class",
            "SELECT class, count(id) AS num FROM students WHERE sex = 'female' GROUP BY class"
        ];
        $data = [];
        foreach ($sql as $s) {
            $data[] = $this->app['db']->fetchAll($s);
        }
        foreach ($data[0] as $class) {
            $this->class_list[] = $class['class'];
            $this->initClass($class['class'], $class['class'], $class, 0, 0);
        }
        foreach ($data[1] as $class) {
            $this->classes[$class['class']]['male'] = $class['num'];
        }
        foreach ($data[2] as $class) {
            $this->classes[$class['class']]['female'] = $class['num'];
        }
    }

    private function initClass($key, $name, $total, $male, $female)
    {
        $this->classes[$key] = [
            'name' => $name,
            'total' => $total['num'],
            'results' => [],
            'male' => $male['num'],
            'female' => $female['num'],
        ];
        foreach ($this->results as $result_key => $result) {
            $this->classes[$key]['results'][$result_key] = array(
                'title' => $result['title'],
                'levels' => array()
            );
            foreach ($result['levels'] as $level_key => $level_name) {
                $this->classes[$key]['results'][$result_key]['levels'][$level_key] = array(
                    'title' => $level_name,
                    'male_count' => 0,
                    'female_count' => 0
                );
            }
        }
    }
}
