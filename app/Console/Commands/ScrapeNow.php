<?php

# php artisan scrape:now
# php artisan scrape:now --start=2020
# php artisan scrape:now --end=2020
# php artisan scrape:now --start=2020 --end=2020
#
# Other options.
#
# php artisan scrape:now --nocache
# php artisan scrape:now --nosave
# php artisan scrape:now --sub=BIOL
# php artisan scrape:now --verbose

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Goutte\Client;
use Symfony\Component\HttpClient\NativeHttpClient;
use Illuminate\Support\Facades\Storage;
use App\Models\Attr;
use App\Models\Course;
use App\Models\Instructor;

class ScrapeNow extends Command
{
    protected $signature = 'scrape:now {--start=} {--end=} {--sub=} {--sem=} {--nocache} {--nosave}';

    protected $description = 'Scrape now';

    protected $sections = array();
    protected $data = array();

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $this->verbose = $this->option('verbose');
        $this->update_mysql = $this->option('nosave') ? False : True;

        $min_year = $this->option('start');
        if ($min_year == "") {
            $min_year = 1999;
        }

        $max_year = $this->option('end');
        if ($max_year == "") {
            $max_year = date("Y")+1; // Get one year into the future for giggles
        }

        $subject = $this->option('sub');
        if ($subject == "") {
            $subject = "BIOL";
        }

        if ($this->option('nocache') == "") {
            $use_file_cache = 1;
        } else {
            $use_file_cache = 0;
        }

        $semester = $this->option('sem');
        if ($semester != "") {
            if ($semester == 4 or $semester == 6 or $semester == 8) {
                ScrapeNow::get_and_save_semester_data($min_year, $semester, $subject, $use_file_cache);
            } else {
                print("Semester must be 4, 6, or 8 (Spring, Summer, Fall)\n");
            }
        } else {
            ScrapeNow::loop_through_years($min_year, $max_year, $subject, $use_file_cache);
        }
    }

    public function loop_through_years($min_year, $max_year, $subject, $use_file_cache=false)
    {
        if ($this->verbose) {
            print("loop_through_years($min_year, $max_year, $subject, $use_file_cache=false)\n");
        }
        $semesters = array();
        for ($year = $min_year; $year <= $max_year; $year++) {
            foreach (array(4, 6, 8) as $semester) {
                ScrapeNow::get_and_save_semester_data($year, $semester, $subject, $use_file_cache);
            }
        }
    }

    public function get_and_save_semester_data($year, $semester, $subject, $use_file_cache=false)
    {
        if ($this->verbose) {
            print("get_and_save_semester_data($year, $semester, $subject, $use_file_cache=false)\n");
        }

        ############
        # Get data #
        ############

        $semcode = sprintf("%03d%d", $year-1900, $semester);
        if ($use_file_cache and Storage::disk('local')->exists("{$semcode}.json")) {
            if ($this->verbose) {
                print("Reading disk file {$semcode}.json, not loading from web.\n");
            }
            $this->data[$semcode] = json_decode(Storage::get("{$semcode}.json"), 1);
        } else {
            ScrapeNow::download_semester_data($year, $semester, $semcode, $subject, $use_file_cache=false);
        }

        ##########################
        # Save the data to MYSQL #
        ##########################

        if ( $this->update_mysql and array_key_exists($semcode, $this->data) ) {
            foreach ( $this->data[$semcode] as $courseArray ) {
                if ( $courseArray ) {
                    $course = Course::firstOrNew([
                        'cap' => $courseArray['cap'],
                        'cat' => $courseArray['cat'],
                        'com' => $courseArray['com'],
                        'des' => $courseArray['des'],
                        'nam' => $courseArray['nam'],
                        'num' => $courseArray['num'],
                        'sec' => $courseArray['sec'],
                        'sem' => $courseArray['sem'],
                        'sub' => $courseArray['sub'],
                        'typ' => $courseArray['typ'],
                        'uni' => $courseArray['uni'],
                        'yea' => $courseArray['yea'],
                    ]);

                    $course->enr = $courseArray['enr'];

                    if (!$course->exists) {
                        if ($this->verbose) {
                            print("  Saved to mysql: $courseArray[yea] $courseArray[sem] $courseArray[cat]-$courseArray[sec] $courseArray[nam]\n");
                        }
                        if ( array_key_exists('fee', $courseArray) ) {
                            $course->fee = $courseArray['fee'];
                        }
                        if ( array_key_exists('rek', $courseArray) ) {
                            $course->rek = $courseArray['rek'];
                        }
                        if ( array_key_exists('syl', $courseArray) ) {
                            $course->syl = $courseArray['syl'];
                        }

                        $course->save();
                        if ( array_key_exists('ins', $courseArray) ) {
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
                        if ( array_key_exists('att', $courseArray) ) {
                            foreach ( $courseArray['att'] as $attributeText ) {
                                if ( $attributeText ) {
                                    $attribute = Attr::firstOrCreate([
                                        'attr' => $attributeText,
                                    ]);
                                    $course->attrs()->save($attribute);
                                }
                            }
                        }
                    } else {
                        if ($this->verbose) {
                            print("  Updating enr in mysql: $courseArray[yea] $courseArray[sem] $courseArray[cat]-$courseArray[sec] $courseArray[nam]\n");
                        }
                        # Update the enrollment count.
                        # Hopefully nothing else changes!!!!! (because it isn't updated...)
                        $course->save();
                    }
                }
            }
        } else {
            print("  No data for $semcode\n");
        }
    }

###############################################################################
#
#                             SCRAPE WEBPAGES HERE
#                          THIS WILL NEED MAINTENANCE
#
# As such there are a lot of commented out verbose print_r statements below.
# They are placed where they will probably be useful. To debug this, ssh into
# the server, `sudo -s`, `cd /var/www/courses-backend`, and run
#
# `php artisan scrape:now --verbose --nocache`
#
# This all assumes that the source webpages only change a little. If they
# change a lot, this whole file may need to be reworked, not just the
# scraping code.
#
# Docs for the scraper are here.
#
#          https://symfony.com/doc/current/components/dom_crawler.html
#
# The code scrapes the following 3 types of pages:
#
# https://student.apps.utah.edu/uofu/stu/ClassSchedules/main/1214/class_list.html?subject=BIOL
# https://student.apps.utah.edu/uofu/stu/ClassSchedules/main/1214/sections.html?subj=BIOL&catno=1010
# https://student.apps.utah.edu/uofu/stu/ClassSchedules/main/1214/description.html?subj=BIOL&catno=1210&section=001
#
###############################################################################

    function download_semester_data($year, $semester, $semcode, $subject, $use_file_cache=false)
    {
        if ($this->verbose) {
            print("download_semester_data($year, $semester, $semcode, $subject, $use_file_cache=false)\n");
        }

        # Download the main subject

        $url = "https://student.apps.utah.edu/uofu/stu/ClassSchedules/main/{$semcode}/class_list.html?subject={$subject}";

        $client = new Client();
        $crawler = $client->request('GET', $url);
        $semester_data = $crawler->filter('.class-info')->each(function($item, $i) {
            return ScrapeNow::scrape_class_list_page($item, $i);
        });
        echo "\n";

        $cleaned_data = [];
        foreach ( $semester_data as $courseArray ) {
            if ( $courseArray ) {
                $courseArray['yea'] = $year;
                $courseArray['sem'] = $semester;
                array_push($cleaned_data, $courseArray);
            }
        }

        Storage::disk('local')->put("{$semcode}.json", json_encode($cleaned_data));
        $this->data[$semcode] = $cleaned_data;

        if ($this->verbose) {
//             print_r($this->data[$semcode]);
        }

//         echo "\n";
    }

    public function scrape_class_list_page($item, $i)
    {
        if ($this->verbose) {
            print("-------------------------------------------------------\n");
            print("  scrape_class_list_page(\$item, $i)\n");
        }
        $course = array();

        #############
        # Section 1 #
        #############

        # Scrape data

        $catalog_number_text = $item->filter('.class-info h3 > a')->text(); // This value is only used to scrape the sections page
        $section_text = $item->filter('.class-info h3 > span')->text();
        if ( $item->matches('.class-info h3 > a:nth-child(3)') ) {
            $course_name_text = $item->filter('.class-info h3 > a:nth-child(3)')->text();
            $syllabus_url = $item->selectLink($course_name_text)->link()->getUri();
        } elseif ( $item->matches('.class-info h3 > span:nth-child(3)') ) {
            $course_name_text = $item->filter('.class-info h3 > span:nth-child(3)')->text();
            $syllabus_url = "";
        }
        $attrs_array = $item->filter('.class-info .d-md-block > div:nth-child(1) > div > a.btn.btn-outline-dark.btn-sm')->each(function($subitem) {
            return $subitem->text();
        });

        # Store data

		preg_match('/^(\w+) (\d+)$/', $catalog_number_text, $matches1);
        $course['Catalog Subject'] = $matches1[1];
        $course['Catalog Number'] = $matches1[2];
        $course['Course Name'] = $course_name_text;
        $course['Section'] = $section_text;
        if ( $syllabus_url ) {
            $course['Syllabus URL'] = $syllabus_url;
        }
        if ( $attrs_array ) {
            $course['Attributes'] = $attrs_array;
        }

        #############
        # Section 2 #
        #############

        # Scrape data

        $section_2 = $item->filter('.class-info .d-md-block > div:nth-child(2)');

        $course_key_values = $section_2->filter('ul > li')->each(function($subitem) {
            preg_match('/^([^:]+): ?([^:]*)$/', $subitem->text(), $matches1);
            $key = $matches1[1];
            $instructor_name = $matches1[2];
            $instructor_url = null;
            if ( $key == 'Instructor' ) {
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

        # Store data

        $known_keys = array(
            'Class Number'=>1,
            'Component'=>1,
            'Fees'=>1,
            'Instructor'=>1,
            'Requisites'=>1,
            'Seats Available'=>1,
            'Type'=>1,
            'Units'=>1,
            'Wait List'=>1,
        );
        foreach ($course_key_values as $value1) {
            if (array_key_exists($value1[0], $known_keys)) {
                if ( $value1[0] == 'Instructor' ) {
                    array_shift($value1);
                    if (array_key_exists('Instructor', $course)) {
                        array_push( $course['Instructor'], $value1 );
                    } else {
                        $course['Instructor'] = array($value1);
                    }
                } else {
                    $course[$value1[0]] = $value1[1];
                }
            } else {
                throw new Exception("Unknown key: $value1[0]");
            }
        }

        #############
        # Section 3 #
        #############

        # Scrape data

        # Store data

        #############
        # Section 4 #
        #############

        # Scrape data

        $special_instructions_text = $item->filter('.class-info .d-md-block > div:nth-child(2)')->text();
        print("SPECIAL: $special_instructions_text\n");

        # Store data

        if ($this->verbose) {
//             print_r($course);
        }

        ########################
        # Scrape Sections Page #
        ########################

        # Scrape data

        $sections_link = $item->selectLink($catalog_number_text)->link()->getUri();
        if (!array_key_exists($sections_link, $this->sections)) {
            $this->sections[$sections_link] = ScrapeNow::scrape_sections_page($sections_link);
        }

        if ($this->verbose) {
//             print_r($this->sections);
        }

        # Store data

        $bad_course = true;
        foreach ($this->sections[$sections_link] as $section) {
            // Only add the data for the current section (all sections are loaded)
            if ( $course['Section'] == $section['Section'] ) {

                // Everything else
                if ( $course['Course Name'] != $section['Course Name'] ) {
                    $course['Unused Section Course Name'] = $section['Course Name'];
                }
                $course['Unused Section Class Number'] = $section['Class Number'];
                $course['Unused Section Catalog Number'] = $section['Catalog Number'];
                $course['Unused Section Catalog Subject'] = $section['Catalog Subject'];
                $course['Currently Enrolled'] = $section['Currently Enrolled'];
                $course['Enrollment Cap'] = $section['Enrollment Cap'];
                $course['Unused Section Seats Available'] = $section['Seats Available'];
                $course['Unused Section Wait List'] = $section['Wait List'];
                $bad_course = false;

            }
        }
        if ( $bad_course ) {
            print("No section data!\n");
            print_r($course);
            print_r($this->sections[$sections_link]);
            // Can't throw an exception because it happens once >:(
            // https://student.apps.utah.edu/uofu/stu/ClassSchedules/main/1068/sections.html?subj=BIOL&catno=5495 is blank!

            $course['Unused Section Catalog Number'] = '';
            $course['Unused Section Catalog Subject'] = '';
            $course['Currently Enrolled'] = '';
            $course['Enrollment Cap'] = '';
            $course['Unused Section Seats Available'] = '';
            $course['Unused Section Wait List'] = '';
        }

        if ($this->verbose) {
//             print_r($course);
        }

        ###########################
        # Scrape Description Page #
        ###########################

        # Scrape data

        $description_link = $item->selectLink('Class Details')->link()->getUri();
        $description_page_data = ScrapeNow::scrape_description_page($description_link);

        if ($this->verbose) {
//             print_r($description_page_data);
        }

        # Store data

        if ( array_key_exists('Course Attribute:', $description_page_data) ) {
            $course['Unused Course Attributes'] = $description_page_data['Course Attribute:'];
        }
        if ( array_key_exists('Requirement Designation:', $description_page_data) ) {
            $course['Unused Description Requirement Designation'] = $description_page_data['Requirement Designation:'];
        }
        if ( array_key_exists('Enrollment Requirement:', $description_page_data) ) {

            if ( sizeof($description_page_data['Enrollment Requirement:']) == 1 ) {
                $course['Enrollment Requirement'] = $description_page_data['Enrollment Requirement:'][0];
//                 $course['Enrollment Requirement'] = preg_replace('/Prerequisites: /', '', $course['Enrollment Requirement']);
            } else {
                throw new Exception("There's more than one Enrollment Requirement.");
            }
        }
        if ( array_key_exists('Units:', $description_page_data) ) {
            $course['Unused Description Units'] = $description_page_data['Units:'];
        }
        if ( array_key_exists('Course Components:', $description_page_data) ) {
            if ( sizeof($description_page_data['Course Components:']) != 0 ) {
                $course['Unused Description Course Components'] = $description_page_data['Course Components:'];
            }
        }
        if ( array_key_exists('Description', $description_page_data) ) {
            $course['Description'] = $description_page_data['Description'];
        }

        if ($this->verbose) {
//             print_r($course);
        }

        ###############
        # Clean it up #
        ###############

        $course2 = array();

        # The Class Number is sometimes missing! >:(

        if (!array_key_exists('Class Number', $course) or $course['Class Number'] == "") {
            if (array_key_exists('Unused Section Class Number', $course) and $course['Unused Section Class Number'] != "") {
                print("ERROR! There is no class number on main page! Using sections page class number.\n");
                $course['Class Number'] = $course['Unused Section Class Number'];
            } else {
                print("ERROR! There is no class number on main page or sections page! Using a hash value.\n");
                $course['Class Number'] = hexdec(substr(sha1($course['Catalog Subject'].$course['Catalog Number']), 0, 7));
            }
        }

        # Section 1

        $course2['cat'] = (int)$course['Catalog Number'];
        unset($course['Catalog Number']);

        $course2['sub'] = $course['Catalog Subject'];
        unset($course['Catalog Subject']);

        $course2['nam'] = $course['Course Name'];
        unset($course['Course Name']);

        $course2['sec'] = $course['Section'];

        unset($course['Section']);
        if (array_key_exists('Syllabus URL',$course)) {
            $course2['syl'] = $course['Syllabus URL'];
            unset($course['Syllabus URL']);
        }
        if (array_key_exists('Attributes',$course)) {
            $course2['att'] = $course['Attributes'];
            unset($course['Attributes']);
        }

        # Section 2

        $course2['num'] = (int)$course['Class Number'];
        unset($course['Class Number']);

        $course2['com'] = $course['Component'];
        unset($course['Component']);

        if (array_key_exists('Fees',$course)) {
            $course2['fee'] = $course['Fees'];
            unset($course['Fees']);
        }

        if (array_key_exists('Instructor',$course)) {
            $course2['ins'] = $course['Instructor'];
            unset($course['Instructor']);
        }

        if (array_key_exists('Requisites',$course)) {
            $course2['rek'] = $course['Requisites'];
            unset($course['Requisites']);
        }

        unset($course['Seats Available']);

        $course2['typ'] = $course['Type'];
        unset($course['Type']);

        $course2['uni'] = $course['Units'];
        unset($course['Units']);

        unset($course['Wait List']);

        # Section 3

        # Section 4

        # Sections page

        // Ignored because the data is scraped elsewhere (and is unreliable)
        if (array_key_exists('Unused Section Course Name',$course)) {
            // $course2[''] = $course['Unused Section Course Name'];
            unset($course['Unused Section Course Name']);
        }

        // Ignored because the data is scraped elsewhere
        // $course2[''] = $course['Unused Section Class Number'];
        unset($course['Unused Section Class Number']);

        // Ignored because the data is scraped elsewhere
        // $course2[''] = $course['Unused Section Catalog Number'];
        unset($course['Unused Section Catalog Number']);

        // Ignored because the data is scraped elsewhere
        // $course2[''] = $course['Unused Section Catalog Subject'];
        unset($course['Unused Section Catalog Subject']);

        $course2['enr'] = (int)$course['Currently Enrolled'];
        unset($course['Currently Enrolled']);

        $course2['cap'] = (int)$course['Enrollment Cap'];
        unset($course['Enrollment Cap']);

        // Ignored because the data is scraped elsewhere
        // $course2[''] = $course['Unused Section Seats Available'];
        unset($course['Unused Section Seats Available']);

        // Ignored because the data is scraped elsewhere
        // $course2[''] = $course['Unused Section Wait List'];
        unset($course['Unused Section Wait List']);

        # Description page

        // Ignored because the data is scraped elsewhere
        if (array_key_exists('Unused Course Attributes',$course)) {
            // $course2[''] = $course['Unused Course Attributes'];
            unset($course['Unused Course Attributes']);
        }

        // Ignored because the data is scraped elsewhere
        if (array_key_exists('Unused Description Requirement Designation',$course)) {
            // $course2[''] = $course['Unused Description Requirement Designation'];
            unset($course['Unused Description Requirement Designation']);
        }

        if (array_key_exists('Enrollment Requirement',$course)) {
            $course2['req'] = $course['Enrollment Requirement'];
            unset($course['Enrollment Requirement']);
        }

        // Ignored because the data is scraped elsewhere
        if (array_key_exists('Unused Description Units',$course)) {
            // $course2[''] = $course['Unused Description Units'];
            unset($course['Unused Description Units']);
        }

        // Ignored because the data is scraped elsewhere
        if (array_key_exists('Unused Description Course Components',$course)) {
            // $course2[''] = $course['Unused Description Course Components'];
            unset($course['Unused Description Course Components']);
        }

        $course2['des'] = $course['Description'];
        unset($course['Description']);

        # That's all folks

        echo ".";

        if ($this->verbose) {
//             print_r($course); // This should be empty
//             print_r($course2);
        }

        if ( sizeof($course) != 0 ) {
            print_r($course);
            throw new Exception("There's data still in the emptied out course variable, which means you are scraping data but not dealing with it.");
        }
        return $course2;
    }

    public function scrape_sections_page($link)
    {
        if ($this->verbose) {
            print("  scrape_sections_page($link)\n");
        }
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
            return array('Class Number' => $course,
                         'Catalog Subject' => $subject,
                         'Catalog Number' => $catalog,
                         'Section' => $section,
                         'Course Name' => $title,
                         'Enrollment Cap' => $enrollment_cap,
                         'Wait List' => $wait_list,
                         'Currently Enrolled' => $currently_enrolled,
                         'Seats Available' => $seats_available,
                        );
        });
        return $section_data;
    }

    public function scrape_description_page($link)
    {
        if ($this->verbose) {
            print("  scrape_description_page($link)\n");
        }

        # Scrape data

        $client = new Client();
        $crawler = $client->request('GET', $link);
        $scraped_data = $crawler->filter('#uu-skip-target > div')->each(function($item, $i) {
            $card_header = $item->filter('div.card-header')->text();
            if ( $card_header == 'Description' ) {
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
        if ($this->verbose) {
//             print_r($scraped_data);
        }

        # Store data

        $known_keys = array(
            'Course Detail' => array(
                'Units:'=>1,
                'Course Components:'=>1,
            ),
            'Enrollment Information' => array(
                'Course Attribute:'=>1,
                'Enrollment Requirement:'=>1,
                'Requirement Designation:'=>1,
            ),
            'Description'=>1,
        );
        $description_page_data = array();
        foreach ( $scraped_data as $key1 => $value1 ) {
            foreach ( $value1 as $key2 => $value2 ) {
                if (array_key_exists($key2, $known_keys)) {
                    if ( $key2 == 'Description' ) {
                        $description_page_data[$key2] = $value2;
                    } else {
                        foreach ( $value2 as $key3 => $value3 ) {
                            if (array_key_exists($key3, $known_keys{$key2})) {
                                $description_page_data[$key3] = $value3;
                            } else {
                                throw new Exception("Description page unknown key: $key2 -> $key3");
                            }
                        }
                    }
                } else {
                    throw new Exception("Description page unknown key: $key2");
                }
            }
        }
        if ($this->verbose) {
//             print_r($description_page_data);
        }
        return $description_page_data;
    }

}



// https://student.apps.utah.edu/uofu/stu/ClassSchedules/main/1216/
// http://www.acs.utah.edu/student/schedules.html
// https://student.apps.utah.edu/uofu/stu/class-tools/syllabus/1194/1203/Biol%201210_Syllabus-19_Rose.pdf
// https://faculty.utah.edu/u0034951-GARY_J_ROSE/teaching/index.hml
// https://student.apps.utah.edu/uofu/stu/SCFStudentResults/publicReports;jsessionid=933914feb8df6416ad772983b9db?cmd=showReport&strm=1194&class_nbr=1203
// https://student.apps.utah.edu/uofu/stu/SCFStudentResults/publicReports?cmd=showReport&strm=1198&class_nbr=8301
