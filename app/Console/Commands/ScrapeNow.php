<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Goutte\Client;
use Symfony\Component\HttpClient\NativeHttpClient;
use Illuminate\Support\Facades\Storage;
use App\Models\Attribute;
use App\Models\Course;
use App\Models\Instructor;

class ScrapeNow extends Command
{
    protected $signature = 'scrape:now {--bla=}';

    protected $description = 'Scrape now';

    protected $sections = array();
    protected $data = array();

    public function __construct()
    {
        parent::__construct();
    }

    public function find_courses_instructors_geneds() {
        $courses = array();
        $instructors = array();
        $gened = array();
        foreach ($this->data as $semester_key => $semcode) {
            echo $semester_key."\n";
            foreach ($semcode as $course) {
                echo $course['Catalog Subject Number']."\n";
                if (!array_key_exists($course['Catalog Subject Number'],$courses) and
                    !array_key_exists($course['Catalog Subject Number'],$course['Catalog Subject Number'])) {
                    $courses[$course['Catalog Subject Number']] = $course;
                } else {
                    $old = $courses[$course['Catalog Subject Number']];
                }
            }
        }
    }

    public function handle()
    {
        $bla = $this->option('bla');
        $subject = "BIOL";
        if ($bla == 1) {
            $min_year = 1999;
            $max_year = date("Y")+1; // Get one year into the future for giggles
            ScrapeNow::scrape_years($min_year, $max_year, $subject, 1);
        } elseif ($bla == 2) {
            ScrapeNow::scrape_years(2018, 2018, $subject, 1);
            #$this->find_courses_instructors_geneds();
        } elseif ($bla == 3) {
            ScrapeNow::scrape_semester(2018, 4, $subject, 1);
        } elseif ($bla == 4) {
            print_r(ScrapeNow::scrape_sections("https://student.apps.utah.edu/uofu/stu/ClassSchedules/main/1184/sections.html?subj=BIOL&catno=2010"));
        }
        return 0;
    }

// https://student.apps.utah.edu/uofu/stu/ClassSchedules/main/1216/
// http://www.acs.utah.edu/student/schedules.html
// https://student.apps.utah.edu/uofu/stu/ClassSchedules/main/1194/class_list.html?subject=BIOL
// https://student.apps.utah.edu/uofu/stu/ClassSchedules/main/1194/sections.html?subj=BIOL&catno=1010
// https://student.apps.utah.edu/uofu/stu/ClassSchedules/main/1194/description.html?subj=BIOL&catno=1210&section=001
// https://student.apps.utah.edu/uofu/stu/ClassSchedules/main/1194/sections.html?subj=BIOL&catno=1210
// https://student.apps.utah.edu/uofu/stu/class-tools/syllabus/1194/1203/Biol%201210_Syllabus-19_Rose.pdf
// https://faculty.utah.edu/u0034951-GARY_J_ROSE/teaching/index.hml
// https://student.apps.utah.edu/uofu/stu/SCFStudentResults/publicReports;jsessionid=933914feb8df6416ad772983b9db?cmd=showReport&strm=1194&class_nbr=1203
// https://student.apps.utah.edu/uofu/stu/SCFStudentResults/publicReports?cmd=showReport&strm=1198&class_nbr=8301
// https://symfony.com/doc/current/components/dom_crawler.html

    public function scrape_years($min_year, $max_year, $subject, $use_files=false)
    {
        $semesters = array();
        for ($year = $min_year; $year <= $max_year; $year++) {       // real year = $year + 1900 (120+1900 = 2020)
            foreach (array(4, 6, 8) as $semester) {                            // 4 = spring, 6 = summer, 8 = fall
                ScrapeNow::scrape_semester($year, $semester, $subject, $use_files);
            }
        }
        return 0;
    }

    public function scrape_sections($link)
    {
        echo "{$link}\n";
        $client = new Client();
        $crawler = $client->request('GET', $link);
        $section_data = $crawler->filter('tbody > tr')->each(function($item, $i) {
            $course = $item->filter('td:nth-child(1)')->text();
            $subject = $item->filter('td:nth-child(2)')->text();
            $catalog = $item->filter('td:nth-child(3)')->text();
            $section = $item->filter('td:nth-child(4)')->text();
            $title = $item->filter('td:nth-child(5)')->text();
            $enrollment_cap = $item->filter('td:nth-child(6)')->text();
            $wait_list = $item->filter('td:nth-child(7)')->text();
            $currently_enrolled = $item->filter('td:nth-child(8)')->text();
            $seats_available = $item->filter('td:nth-child(9)')->text();
            return array('Class' => $course,
                         'Subject' => $subject,
                         'Catalog' => $catalog,
                         'Section' => $section,
                         'Title' => $title,
                         'Enrollment Cap' => $enrollment_cap,
                         'Wait List' => $wait_list,
                         'Currently Enrolled' => $currently_enrolled,
                         'Seats Available' => $seats_available,
                        );
        });
        return $section_data;
    }

    public function scrape_description($link)
    {
        $client = new Client();
        $crawler = $client->request('GET', $link);
        $temp1 = $crawler->filter('#uu-skip-target > div')->each(function($item, $i) {
            $card_header = $item->filter('div.card-header')->text();
            if ( $card_header == "Description" ) {
                $temp2 = $item->filter('div > div > div.card-body')->each(function($item2, $i) {
                    return $item2->text();
                });
                $value_bla = $temp2[0];
            } else {
                $temp2 = $item->filter('div > div > div.card-body')->each(function($item2, $i) {
                    $temp3 = $item2->filter('div.card-body > div')->each(function($item3, $i) {
                        $temp4 = $item3->filter('div.col-md-2, span')->each(function($item4, $i) {
                            return $item4->text();
                        });
                        return $temp4;
                    });
                    return $temp3;
                });
                $value_bla = array();
                foreach ( $temp2 as $key1 => $value1 ) {
                    foreach ( $value1 as $key2 => $value2 ) {
                        $key3 = array_shift( $value2 );
                        $value_bla[$key3] = $value2;
                    }
                }
            }
            return array( $card_header => $value_bla );
        });
        // Clean it up
        $description_data = array();
        foreach ( $temp1 as $key1 => $value1 ) {
            foreach ( $value1 as $key2 => $value2 ) {
                $description_data[$key2] = $value2;
            }
        }
        return $description_data;
    }

    public function scrape_semester($year, $semester, $subject, $use_file=false)
    {
        $semcode = sprintf("%03d%d", $year-1900, $semester);
        if ($use_file and Storage::disk('local')->exists("{$semcode}.json")) {
            $this->data[$semcode] = json_decode(Storage::get("{$semcode}.json"), 1);
        } else {
            $client = new Client();
            echo "{$semcode} {$subject}\n";
            echo "https://student.apps.utah.edu/uofu/stu/ClassSchedules/main/{$semcode}/class_list.html?subject={$subject}\n";
            $crawler = $client->request('GET', "https://student.apps.utah.edu/uofu/stu/ClassSchedules/main/{$semcode}/class_list.html?subject={$subject}");
            $semester_data = $crawler->filter('.class-info')->each(function($item, $i) {
                return ScrapeNow::scrape_class_list($item, $i);
            });
            echo "\n";

            $cleanuped_data = [];
            foreach ( $semester_data as $courseArray ) {
                if ( $courseArray ) {
                    $courseArray['yea'] = $year;
                    $courseArray['sem'] = $semester;
                    array_push($cleanuped_data, $courseArray);
                }
            }
            if ($use_file) {
                Storage::disk('local')->put("{$semcode}.json", json_encode($cleanuped_data));
            }
            $this->data[$semcode] = $cleanuped_data;
        }

        foreach ( $this->data[$semcode] as $courseArray ) {
            if ( $courseArray ) {
                $course = Course::firstOrNew([
                    'cat' => $courseArray["cat"],
                    'sec' => $courseArray["sec"],
                    'com' => $courseArray["com"],
                    'sub' => $courseArray["sub"],
                    'num' => $courseArray["num"],
                    'nam' => $courseArray["nam"],
                    'enr' => $courseArray["enr"],
                    'des' => $courseArray["des"],
                    'cap' => $courseArray["cap"],
                    'typ' => $courseArray["typ"],
                    'uni' => $courseArray["uni"],
                    'yea' => $courseArray['yea'],
                    'sem' => $courseArray['sem'],
                ]);
                if ( array_key_exists("fee", $courseArray) ) {
                    $course->fee = $courseArray["fee"];
                }
                if ( array_key_exists("rek", $courseArray) ) {
                    $course->rek = $courseArray["rek"];
                }
                if ( array_key_exists("syl", $courseArray) ) {
                    $course->syl = $courseArray["syl"];
                }
                $course->save();
                if ( array_key_exists("ins", $courseArray) ) {
                    foreach ( $courseArray['ins'] as $instructorArray ) {
                        if ( $instructorArray ) {
                            $instructor = Instructor::firstOrCreate([
                                'name' => $instructorArray[0],
                                'unid' => $instructorArray[1],
                            ]);
                            $course->instructors()->save($instructor);
                        }
                    }
                }
                if ( array_key_exists("att", $courseArray) ) {
                    foreach ( $courseArray['att'] as $attributeText ) {
                        if ( $attributeText ) {
                            $attribute = Attribute::firstOrCreate([
                                'attr' => $attributeText,
                            ]);
                            $course->attributes()->save($attribute);
                        }
                    }
                }
                // $course->req = $courseArray["req"];
                // $course->gen = $courseArray["gen"];



            }
        }

        return;
    }











    public function scrape_class_list($item, $i)
    {
        // Main info
        $catalog_number = $item->filter('.class-info h3 > a')->text();
        $section = $item->filter('.class-info h3 > span')->text();
        if ( $item->matches('.class-info h3 > a:nth-child(3)') ) {
            $course_name = $item->filter('.class-info h3 > a:nth-child(3)')->text();
            $syllabus_url = $item->selectLink($course_name)->link()->getUri();
        } elseif ( $item->matches('.class-info h3 > span:nth-child(3)') ) {
            $course_name = $item->filter('.class-info h3 > span:nth-child(3)')->text();
            $syllabus_url = "";
        }
        $gen_ed = $item->filter('.class-info .d-md-block > div:nth-child(1) > div > a.btn.btn-outline-dark.btn-sm')->each(function($subitem) {
            return $subitem->text();
        });
        $course = array(
            'Catalog Subject Number' => $catalog_number,
            'Course Name' => $course_name,
            'Section' => $section,
        );
        if ( $gen_ed ) {
            $course['Gen Ed'] = $gen_ed;
        }
        if ( $syllabus_url ) {
            $course['Syllabus URL'] = $syllabus_url;
        }
        // Extra stuff
        $stuff = $item->filter('.class-info .d-md-block > div:nth-child(2) > ul > li')->each(function($subitem) {
            preg_match('/^([^:]+): ?([^:]*)$/', $subitem->text(), $matches1);
            $key = $matches1[1];
            $instructor_name = $matches1[2];
            $instructor_url = null;
            if ( $key == "Instructor" ) {
                if ( isset($instructor_name) && preg_match('/^(.*) - View Feedback$/', $instructor_name, $matches2) ) {
                    $instructor_name = $matches2[1];
                }
                $instructor_url = $subitem->selectLink($instructor_name)->link()->getUri();
                if ( preg_match('/^http:\/\/faculty.utah.edu\/(.*)\/teaching\/index.hml$/', $instructor_url, $matches3) ) {
                    $unid = $matches3[1];
                }
                return array($key, $instructor_name, $unid);
            } else {
                return array($key, $instructor_name);
            }
        });
        foreach ($stuff as $value1) {
            if ( $value1[0] == "Instructor" ) {
                array_shift($value1);
                if (array_key_exists("Instructor", $course)) {
                    array_push( $course["Instructor"], $value1 );
                } else {
                    $course["Instructor"] = array($value1);
                }

            } else {
                $course[$value1[0]] = $value1[1];
            }
        }

//         if ($course["Component"]=="Discussion" ||
//             $course["Component"]=="Laboratory"
//            ) {
//             return null;
//         }

        // Scrape Sections Page
        $sections_link = $item->selectLink($catalog_number)->link()->getUri();
        if (!array_key_exists($sections_link, $this->sections)) {
            $this->sections[$sections_link] = ScrapeNow::scrape_sections($sections_link);
        }
        $test = true;
        foreach ($this->sections[$sections_link] as $section) {
            if ( $course['Class Number'] == $section['Class'] ||
                 $course['Section'] == $section['Section']
               ) {
                if ( $course['Course Name'] != $section['Title'] ) {
                    $course['Title'] = $course['Course Name'];
                }
                $course['Class Number'] = $section['Class'];
                $course['Catalog Subject'] = $section['Subject'];
                $course['Catalog Number'] = $section['Catalog'];
                $course['Enrollment Cap'] = $section['Enrollment Cap'];
                $course['Wait List'] = $section['Wait List'];
                $course['Currently Enrolled'] = $section['Currently Enrolled'];
                $course['Seats Available'] = $section['Seats Available'];
                $test = false;
            }
        }
        if ( $test ) {#$section
            echo "Bad course\n";
            print_r($course);
            print_r($this->sections[$sections_link]);
            return null;
        }

        $course['Sections URL'] = $sections_link;
        // Scrape Description Page
        $description_link = $item->selectLink("Class Details")->link()->getUri();
        $description = ScrapeNow::scrape_description($description_link);
        $course['Description URL'] = $description_link;
        // Clean it up!
        if ( array_key_exists("Enrollment Information", $description) ) {
            if ( array_key_exists("Course Attribute:", $description['Enrollment Information']) ) {
                $course['Course Attributes'] = $description['Enrollment Information']['Course Attribute:'];
                unset($description['Enrollment Information']['Course Attribute:']);
            }
            if ( array_key_exists("Requirement Designation:", $description['Enrollment Information']) ) {
                unset($description['Enrollment Information']['Requirement Designation:']);
            }
            if ( array_key_exists("Enrollment Requirement:", $description['Enrollment Information']) ) {
                $course['Enrollment Requirements'] = $description['Enrollment Information']['Enrollment Requirement:'];
                unset($description['Enrollment Information']['Enrollment Requirement:']);
                $course['Enrollment Requirements'] = preg_replace('/Prerequisites: /', '', $course['Enrollment Requirements']);
            }
            if ( sizeof($description['Enrollment Information']) == 0 ) {
                unset($description['Enrollment Information']);
            }
        }
        if ( array_key_exists("Course Detail", $description) ) {
            if ( array_key_exists("Units:", $description["Course Detail"]) ) {
                unset($description['Course Detail']['Units:']);
            }
            if ( array_key_exists("Course Components:", $description["Course Detail"]) ) {
                if ( sizeof($description['Course Detail']['Course Components:']) != 0 ) {
                    $course['Course Components'] = $description['Course Detail']['Course Components:'];
                }
                unset($description['Course Detail']['Course Components:']);
            }
            if ( sizeof($description['Course Detail']) == 0 ) {
                unset($description['Course Detail']);
            }
        }
        if ( array_key_exists("Description", $description) ) {
            $course['Description'] = $description['Description'];
            unset($description['Description']);
        }
        if ( sizeof($description) == 0 ) {
            unset($description);
        } else {
            print_r($course);
            print_r($description);
            throw new Exception("Unhandled description page stuff.");
        }
        echo ".";

        $course2 = array();
        $course2['cat'] = (int)$course["Catalog Number"];
        $course2['sec'] = (int)$course["Section"];
        $course2['com'] = $course["Component"];
        $course2['sub'] = $course["Catalog Subject"];
        $course2['num'] = (int)$course["Class Number"];
        $course2['nam'] = $course["Course Name"];
        $course2['enr'] = (int)$course["Currently Enrolled"];
        $course2['des'] = $course["Description"];
        $course2['cap'] = (int)$course["Enrollment Cap"];
        $course2['typ'] = $course["Type"];
        $course2['uni'] = $course["Units"];

        if (array_key_exists('Instructor',$course)) {
            $course2['ins'] = $course["Instructor"];
            unset($course['Instructor']);
        }
        if (array_key_exists('Course Attributes',$course)) {
            $course2['att'] = $course["Course Attributes"];
            unset($course['Course Attributes']);
        }
        if (array_key_exists('Enrollment Requirements',$course)) {
            $course2['req'] = $course["Enrollment Requirements"];
            unset($course['Enrollment Requirements']);
        }
        if (array_key_exists('Fees',$course)) {
            $course2['fee'] = $course["Fees"];
            unset($course['Fees']);
        }
        if (array_key_exists('Gen Ed',$course)) {
            $course2['gen'] = $course["Gen Ed"];
            unset($course['Gen Ed']);
        }
        if (array_key_exists('Requisites',$course)) {
            $course2['rek'] = $course["Requisites"];
            unset($course['Requisites']);
        }
        if (array_key_exists('Syllabus URL',$course)) {
            $course2['syl'] = $course["Syllabus URL"];
            unset($course['Syllabus URL']);
        }

        unset($course['Catalog Number']);
        unset($course['Section']);
        unset($course['Component']);
        unset($course['Catalog Subject']);
        unset($course['Catalog Subject Number']);
        unset($course['Class Number']);
        unset($course['Course Name']);
        unset($course['Currently Enrolled']);
        unset($course['Description']);
        unset($course['Enrollment Cap']);
        unset($course['Type']);
        unset($course['Units']);
        unset($course['Course Components']);
        unset($course['Description URL']);
        unset($course['Seats Available']);
        unset($course['Sections URL']);
        unset($course['Title']);
        unset($course['Wait List']);
        if ( sizeof($course) != 0 ) {
            print_r($course);
            throw new Exception("Not empty course.");
        }
        return $course2;
    }
}
