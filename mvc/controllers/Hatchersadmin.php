<?php if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Hatchersadmin extends Admin_Controller
{
    public $load;
    public $session;
    public $data;
    public $input;
    public $db;
    public $teacher_m;
    public $student_m;
    public $mentor_founder_m;
    public $hatchers_nav_item_m;
    public $setting_m;
    public $hatcher_ai_settings_m;
    public $parents_m;
    public $studentrelation_m;
    public $studentextend_m;
    public $classes_m;
    public $section_m;
    public $founder_task_m;
    public $founder_meeting_m;
    public $founder_learning_m;
    public $milestone_meta_m;
    public $hatchers_shell_m;

    public function __construct()
    {
        parent::__construct();
        $this->load->model('teacher_m');
        $this->load->model('student_m');
        $this->load->model('mentor_founder_m');
        $this->load->model('hatchers_nav_item_m');
        $this->load->model('setting_m');
        $this->load->model('hatcher_ai_settings_m');
        $this->load->model('parents_m');
        $this->load->model('studentrelation_m');
        $this->load->model('studentextend_m');
        $this->load->model('classes_m');
        $this->load->model('section_m');
        $this->load->model('founder_task_m');
        $this->load->model('founder_meeting_m');
        $this->load->model('founder_learning_m');
        $this->load->model('milestone_meta_m');
        $this->load->model('hatchers_shell_m');

        if ($this->session->userdata('usertypeID') != 1) {
            show_404();
        }

        $this->data['headerassets'] = [
            'css' => [
                'assets/hatchers/hatchers.css'
            ]
        ];
    }

    public function index()
    {
        redirect('hatchersadmin/assignments');
    }

    public function assignments()
    {
        if (!$this->db->table_exists('mentor_founder')) {
            $this->data['mentors'] = [];
            $this->data['founders'] = [];
            $this->data['assignmentMap'] = [];
            $this->data["subview"] = "hatchersadmin/assignments";
            $this->_render('hatchersadmin/assignments', 'mentoring');
            return;
        }
        $mentors = $this->teacher_m->get_teacher();

        $this->db->select('student.studentID, student.name, student.email, student.phone, student.photo, studentextend.remarks as company_brief');
        $this->db->from('student');
        $this->db->join('studentextend', 'studentextend.studentID = student.studentID', 'LEFT');
        $founders = $this->db->get()->result();

        $assignments = $this->mentor_founder_m->get_order_by_mentor_founder(['status' => 1]);
        $assignmentMap = [];
        if (customCompute($assignments)) {
            foreach ($assignments as $assignment) {
                $assignmentMap[$assignment->founder_id] = $assignment;
            }
        }

        $this->data['mentors'] = $mentors;
        $this->data['founders'] = $founders;
        $this->data['assignmentMap'] = $assignmentMap;
        $this->_render('hatchersadmin/assignments', 'mentoring');
    }

    public function assign()
    {
        if (!$this->db->table_exists('mentor_founder')) {
            redirect('hatchersadmin/assignments');
        }
        $founderID = (int) $this->input->post('founder_id');
        $mentorID = (int) $this->input->post('mentor_id');

        if ($founderID <= 0) {
            redirect('hatchersadmin/assignments');
        }

        $existing = $this->mentor_founder_m->get_single_mentor_founder(['founder_id' => $founderID]);

        if ($mentorID <= 0) {
            if (customCompute($existing)) {
                $this->mentor_founder_m->delete_mentor_founder($existing->mentor_founder_id);
            }
            redirect('hatchersadmin/assignments');
        }

        if (customCompute($existing)) {
            $this->mentor_founder_m->update_mentor_founder([
                'mentor_id' => $mentorID,
                'status' => 1,
                'assigned_at' => date('Y-m-d H:i:s')
            ], $existing->mentor_founder_id);
        } else {
            $this->mentor_founder_m->insert_mentor_founder([
                'mentor_id' => $mentorID,
                'founder_id' => $founderID,
                'status' => 1,
                'assigned_at' => date('Y-m-d H:i:s')
            ]);
        }

        redirect('hatchersadmin/assignments');
    }

    public function nav()
    {
        $navItems = [];
        if ($this->db->table_exists('hatchers_nav_items')) {
            $navItems = $this->hatchers_nav_item_m->get_order_by_hatchers_nav_item();
        }
        $this->data['nav_items'] = $navItems;
        $this->_render('hatchersadmin/nav', 'ai_tools');
    }

    public function nav_save()
    {
        if (!$this->db->table_exists('hatchers_nav_items')) {
            redirect('hatchersadmin/nav');
        }
        $id = (int) $this->input->post('hatchers_nav_item_id');
        $location = trim((string) $this->input->post('location'));
        if ($location !== 'right_ai') {
            $location = 'right_ai';
        }
        $data = [
            'label' => $this->input->post('label'),
            'icon' => $this->input->post('icon'),
            'link' => $this->input->post('link'),
            'location' => $location,
            'sort_order' => (int) $this->input->post('sort_order'),
            'active' => (int) $this->input->post('active')
        ];

        if ($id > 0) {
            $this->hatchers_nav_item_m->update_hatchers_nav_item($data, $id);
        } else {
            $this->hatchers_nav_item_m->insert_hatchers_nav_item($data);
        }

        redirect('hatchersadmin/nav');
    }

    public function nav_delete($id = 0)
    {
        $id = (int) $id;
        if ($id > 0) {
            $this->hatchers_nav_item_m->delete_hatchers_nav_item($id);
        }
        redirect('hatchersadmin/nav');
    }

    public function ai()
    {
        $aiSettings = null;
        if ($this->db->table_exists('hatcher_ai_settings')) {
            $aiSettings = $this->hatcher_ai_settings_m->get_latest_settings();
        }
        $this->data['aiSettings'] = $aiSettings;
        $this->_render('hatchersadmin/ai', 'ai_tools');
    }

    public function ai_save()
    {
        if (!$this->db->table_exists('hatcher_ai_settings')) {
            redirect('hatchersadmin/ai');
        }
        $systemPrompt = $this->input->post('system_prompt');
        $guidelines = $this->input->post('guidelines');
        $model = $this->input->post('model');
        $temperature = (float) $this->input->post('temperature');
        $maxTokens = (int) $this->input->post('max_tokens');

        $this->hatcher_ai_settings_m->upsert_settings([
            'system_prompt' => $systemPrompt,
            'guidelines' => $guidelines,
            'model' => !empty($model) ? $model : 'gpt-4o-mini',
            'temperature' => $temperature,
            'max_tokens' => $maxTokens
        ]);

        redirect('hatchersadmin/ai');
    }

    public function profiles()
    {
        $this->db->select('student.studentID, student.name, student.email, student.phone, student.username, student.photo, student.classesID, student.sectionID, student.roll, studentextend.remarks as company_brief');
        $this->db->from('student');
        $this->db->join('studentextend', 'studentextend.studentID = student.studentID', 'LEFT');
        $founders = $this->db->get()->result();

        $mentors = $this->teacher_m->get_teacher();

        $track = $this->_ensure_default_track();
        $this->data['defaultTrack'] = $track;
        $this->data['founders'] = $founders;
        $this->data['mentors'] = $mentors;
        $this->_render('hatchersadmin/profiles', 'profiles');
    }

    public function create_founder()
    {
        $result = $this->_create_founder_record($this->input->post());
        if ($result['ok']) {
            $this->session->set_flashdata('success', $result['message']);
        } else {
            $this->session->set_flashdata('error', $result['message']);
        }
        redirect('hatchersadmin/profiles');
    }

    public function create_mentor()
    {
        $result = $this->_create_mentor_record($this->input->post());
        if ($result['ok']) {
            $this->session->set_flashdata('success', $result['message']);
        } else {
            $this->session->set_flashdata('error', $result['message']);
        }
        redirect('hatchersadmin/profiles');
    }

    public function edit_founder($founderID = 0)
    {
        $founderID = (int) $founderID;
        if ($founderID <= 0) {
            redirect('hatchersadmin/profiles');
        }

        $this->db->select('student.studentID, student.name, student.email, student.phone, student.username, student.photo, student.classesID, student.sectionID, student.roll, student.parentID, student.address, student.state, student.country, student.bloodgroup, student.religion, student.sex, studentextend.remarks as company_brief');
        $this->db->from('student');
        $this->db->join('studentextend', 'studentextend.studentID = student.studentID', 'LEFT');
        $this->db->where('student.studentID', $founderID);
        $founder = $this->db->get()->row();

        if (!customCompute($founder)) {
            redirect('hatchersadmin/profiles');
        }

        $this->data['defaultTrack'] = $this->_ensure_default_track();
        $this->data['founder'] = $founder;
        $this->_render('hatchersadmin/edit_founder', 'profiles');
    }

    public function update_founder($founderID = 0)
    {
        $founderID = (int) $founderID;
        if ($founderID <= 0) {
            redirect('hatchersadmin/profiles');
        }

        $founder = $this->student_m->general_get_single_student(['studentID' => $founderID]);
        if (!customCompute($founder)) {
            redirect('hatchersadmin/profiles');
        }

        $name = trim((string) $this->input->post('name'));
        $email = trim((string) $this->input->post('email'));
        $phone = trim((string) $this->input->post('phone'));
        $username = trim((string) $this->input->post('username'));
        $password = (string) $this->input->post('password');
        $track = $this->_ensure_default_track();
        $classesID = (int) $track['classesID'];
        $sectionID = (int) $track['sectionID'];
        $roll = trim((string) $this->input->post('roll'));
        $sex = trim((string) $this->input->post('sex'));
        $address = trim((string) $this->input->post('address'));
        $state = trim((string) $this->input->post('state'));
        $country = trim((string) $this->input->post('country'));
        $religion = trim((string) $this->input->post('religion'));
        $bloodgroup = trim((string) $this->input->post('bloodgroup'));
        $companyBrief = trim((string) $this->input->post('company_brief'));

        if ($name === '' || $username === '') {
            $this->session->set_flashdata('error', 'Please fill all required fields.');
            redirect('hatchersadmin/edit_founder/' . $founderID);
        }

        if ($roll === '') {
            $roll = !empty($founder->roll) ? $founder->roll : ('F-' . $founderID);
        }

        if ($username !== $founder->username && !$this->_is_username_available($username)) {
            $this->session->set_flashdata('error', 'Username already exists.');
            redirect('hatchersadmin/edit_founder/' . $founderID);
        }
        if ($email !== '' && $email !== $founder->email && !$this->_is_email_available($email)) {
            $this->session->set_flashdata('error', 'Email already exists.');
            redirect('hatchersadmin/edit_founder/' . $founderID);
        }

        $update = [
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
            'username' => $username,
            'classesID' => $classesID,
            'sectionID' => $sectionID,
            'roll' => $roll,
            'sex' => $sex,
            'address' => $address,
            'state' => $state,
            'country' => $country,
            'religion' => $religion,
            'bloodgroup' => $bloodgroup,
            'modify_date' => date("Y-m-d H:i:s")
        ];

        if ($password !== '') {
            $update['password'] = $this->student_m->hash($password);
        }

        $this->student_m->update_student($update, $founderID);

        $section = $this->section_m->general_get_section($sectionID);
        $classes = $this->classes_m->get_classes($classesID);
        $setClasses = customCompute($classes) ? $classes->classes : null;
        $setSection = customCompute($section) ? $section->section : null;
        $this->studentrelation_m->update_studentrelation_with_multicondition([
            'srclassesID' => $classesID,
            'srclasses' => $setClasses,
            'srsectionID' => $sectionID,
            'srsection' => $setSection,
            'srroll' => $roll
        ], ['srstudentID' => $founderID]);

        $this->studentextend_m->update_studentextend_by_studentID([
            'remarks' => $companyBrief
        ], $founderID);

        if (!empty($founder->parentID)) {
            $this->parents_m->update_parents([
                'name' => $name . ' Guardian',
                'email' => $email,
                'phone' => $phone,
                'address' => $address
            ], $founder->parentID);
        }

        $this->session->set_flashdata('success', 'Founder updated.');
        redirect('hatchersadmin/profiles');
    }

    public function edit_mentor($mentorID = 0)
    {
        $mentorID = (int) $mentorID;
        if ($mentorID <= 0) {
            redirect('hatchersadmin/profiles');
        }

        $mentor = $this->teacher_m->get_single_teacher(['teacherID' => $mentorID]);
        if (!customCompute($mentor)) {
            redirect('hatchersadmin/profiles');
        }

        $this->data['mentor'] = $mentor;
        $this->_render('hatchersadmin/edit_mentor', 'profiles');
    }

    public function update_mentor($mentorID = 0)
    {
        $mentorID = (int) $mentorID;
        if ($mentorID <= 0) {
            redirect('hatchersadmin/profiles');
        }

        $mentor = $this->teacher_m->get_single_teacher(['teacherID' => $mentorID]);
        if (!customCompute($mentor)) {
            redirect('hatchersadmin/profiles');
        }

        $name = trim((string) $this->input->post('name'));
        $email = trim((string) $this->input->post('email'));
        $phone = trim((string) $this->input->post('phone'));
        $username = trim((string) $this->input->post('username'));
        $password = (string) $this->input->post('password');
        $designation = trim((string) $this->input->post('designation'));
        $dob = trim((string) $this->input->post('dob'));
        $sex = trim((string) $this->input->post('sex'));
        $address = trim((string) $this->input->post('address'));
        $religion = trim((string) $this->input->post('religion'));

        if ($name === '' || $username === '' || $dob === '' || $sex === '') {
            $this->session->set_flashdata('error', 'Please fill all required fields.');
            redirect('hatchersadmin/edit_mentor/' . $mentorID);
        }

        if ($username !== $mentor->username && !$this->_is_username_available($username)) {
            $this->session->set_flashdata('error', 'Username already exists.');
            redirect('hatchersadmin/edit_mentor/' . $mentorID);
        }
        if ($email !== '' && $email !== $mentor->email && !$this->_is_email_available($email)) {
            $this->session->set_flashdata('error', 'Email already exists.');
            redirect('hatchersadmin/edit_mentor/' . $mentorID);
        }

        $update = [
            'name' => $name,
            'designation' => $designation !== '' ? $designation : 'Mentor',
            'dob' => date("Y-m-d", strtotime($dob)),
            'sex' => $sex,
            'religion' => $religion,
            'email' => $email,
            'phone' => $phone,
            'address' => $address,
            'username' => $username,
            'modify_date' => date("Y-m-d H:i:s")
        ];

        if ($password !== '') {
            $update['password'] = $this->teacher_m->hash($password);
        }

        $this->teacher_m->update_teacher($update, $mentorID);
        $this->session->set_flashdata('success', 'Mentor updated.');
        redirect('hatchersadmin/profiles');
    }

    public function bulk_import()
    {
        $type = $this->input->post('import_type');
        if ($type !== 'mentor' && $type !== 'founder') {
            $type = 'founder';
        }
        if (!isset($_FILES['csv_file']) || $_FILES['csv_file']['error'] !== UPLOAD_ERR_OK) {
            $this->session->set_flashdata('error', 'CSV upload failed.');
            redirect('hatchersadmin/profiles');
        }

        $tmp = $_FILES['csv_file']['tmp_name'];
        $rows = $this->_parse_csv($tmp);
        if (empty($rows)) {
            $this->session->set_flashdata('error', 'CSV is empty or invalid.');
            redirect('hatchersadmin/profiles');
        }

        $created = 0;
        $errors = 0;
        foreach ($rows as $row) {
            if ($type === 'founder') {
                $result = $this->_create_founder_from_row($row);
            } else {
                $result = $this->_create_mentor_from_row($row);
            }
            if ($result['ok']) {
                $created++;
            } else {
                $errors++;
            }
        }

        $this->session->set_flashdata('success', 'Import complete. Created: ' . $created . ' | Errors: ' . $errors);
        redirect('hatchersadmin/profiles');
    }

    public function preview_import()
    {
        $type = $this->input->post('import_type');
        if ($type !== 'mentor' && $type !== 'founder') {
            $type = 'founder';
        }

        if (!isset($_FILES['csv_file']) || $_FILES['csv_file']['error'] !== UPLOAD_ERR_OK) {
            $this->session->set_flashdata('error', 'CSV upload failed.');
            redirect('hatchersadmin/profiles');
        }

        $tmp = $_FILES['csv_file']['tmp_name'];
        $rows = $this->_parse_csv($tmp);
        if (empty($rows)) {
            $this->session->set_flashdata('error', 'CSV is empty or invalid.');
            redirect('hatchersadmin/profiles');
        }

        $preview = [];
        foreach ($rows as $row) {
            if ($type === 'founder') {
                $preview[] = $this->_validate_founder_row($row);
            } else {
                $preview[] = $this->_validate_mentor_row($row);
            }
        }

        $this->data['import_type'] = $type;
        $this->data['preview_rows'] = $preview;
        $this->_render('hatchersadmin/import_preview', 'profiles');
    }

    public function import_from_preview()
    {
        $type = $this->input->post('import_type');
        if ($type !== 'mentor' && $type !== 'founder') {
            $type = 'founder';
        }

        $raw = $this->input->post('rows_json');
        $rows = json_decode((string) $raw, true);
        if (!is_array($rows)) {
            $this->session->set_flashdata('error', 'Invalid preview data.');
            redirect('hatchersadmin/profiles');
        }

        $created = 0;
        $errors = 0;
        foreach ($rows as $row) {
            if (!isset($row['data'])) {
                $errors++;
                continue;
            }
            $data = $row['data'];
            if ($type === 'founder') {
                $result = $this->_create_founder_record($data);
            } else {
                $result = $this->_create_mentor_record($data);
            }
            if ($result['ok']) {
                $created++;
            } else {
                $errors++;
            }
        }

        $this->session->set_flashdata('success', 'Import complete. Created: ' . $created . ' | Errors: ' . $errors);
        redirect('hatchersadmin/profiles');
    }

    public function download_founder_template()
    {
        $this->_download_csv('founders_template.csv', ['name', 'email', 'phone', 'username', 'password', 'registerNO', 'sex', 'dob', 'admission_date', 'address', 'state', 'country', 'company_brief']);
    }

    public function download_mentor_template()
    {
        $this->_download_csv('mentors_template.csv', ['name', 'email', 'phone', 'username', 'password', 'sex', 'dob', 'designation']);
    }

    public function delete_founder($founderID = 0)
    {
        $founderID = (int) $founderID;
        if ($founderID <= 0) {
            redirect('hatchersadmin/profiles');
        }

        $blocked = false;
        if ($this->db->table_exists('mentor_founder')) {
            $assigned = $this->mentor_founder_m->get_single_mentor_founder(['founder_id' => $founderID]);
            if (customCompute($assigned)) {
                $blocked = true;
            }
        }

        $hasData = false;
        if ($this->db->table_exists('founder_tasks') && customCompute($this->founder_task_m->get_single_founder_task(['founder_id' => $founderID]))) {
            $hasData = true;
        }
        if ($this->db->table_exists('founder_meetings') && customCompute($this->founder_meeting_m->get_single_founder_meeting(['founder_id' => $founderID]))) {
            $hasData = true;
        }
        if ($this->db->table_exists('founder_learning') && customCompute($this->founder_learning_m->get_single_founder_learning(['founder_id' => $founderID]))) {
            $hasData = true;
        }
        if ($this->db->table_exists('milestone_meta') && customCompute($this->milestone_meta_m->get_single_milestone_meta(['founder_id' => $founderID]))) {
            $hasData = true;
        }
        if ($this->db->table_exists('hatcher_ai_conversations')) {
            $conv = $this->db->get_where('hatcher_ai_conversations', ['founder_id' => $founderID], 1)->row();
            if (customCompute($conv)) $hasData = true;
        }
        if ($this->db->table_exists('hatcher_ai_context')) {
            $ctx = $this->db->get_where('hatcher_ai_context', ['founder_id' => $founderID], 1)->row();
            if (customCompute($ctx)) $hasData = true;
        }

        if ($blocked || $hasData) {
            $this->session->set_flashdata('error', 'Cannot delete founder with assignments or data. Remove tasks/meetings/learning/milestones first.');
            redirect('hatchersadmin/profiles');
        }

        $founder = $this->student_m->general_get_single_student(['studentID' => $founderID]);
        if (!customCompute($founder)) {
            redirect('hatchersadmin/profiles');
        }

        if (!empty($founder->parentID)) {
            $this->parents_m->delete_parents($founder->parentID);
        }
        $this->studentrelation_m->delete_studentrelation($founderID);
        $this->studentextend_m->delete_studentextend_by_studentID($founderID);
        $this->student_m->delete_student($founderID);

        $this->session->set_flashdata('success', 'Founder deleted.');
        redirect('hatchersadmin/profiles');
    }

    public function delete_mentor($mentorID = 0)
    {
        $mentorID = (int) $mentorID;
        if ($mentorID <= 0) {
            redirect('hatchersadmin/profiles');
        }

        if ($this->db->table_exists('mentor_founder')) {
            $assigned = $this->mentor_founder_m->get_single_mentor_founder(['mentor_id' => $mentorID]);
            if (customCompute($assigned)) {
                $this->session->set_flashdata('error', 'Cannot delete mentor with assigned founders.');
                redirect('hatchersadmin/profiles');
            }
        }

        $this->teacher_m->delete_teacher($mentorID);
        $this->session->set_flashdata('success', 'Mentor deleted.');
        redirect('hatchersadmin/profiles');
    }

    private function _parse_csv($filePath)
    {
        $rows = [];
        if (!is_readable($filePath)) {
            return $rows;
        }
        if (($handle = fopen($filePath, 'r')) !== false) {
            $headerChecked = false;
            while (($data = fgetcsv($handle, 1000, ',')) !== false) {
                if (!$headerChecked) {
                    $headerChecked = true;
                    if (isset($data[0]) && strtolower(trim((string) $data[0])) === 'name') {
                        continue;
                    }
                }
                if (!customCompute($data)) {
                    continue;
                }
                $rows[] = $data;
            }
            fclose($handle);
        }
        return $rows;
    }

    private function _validate_founder_row($row)
    {
        $data = [
            'name' => trim((string) ($row[0] ?? '')),
            'email' => trim((string) ($row[1] ?? '')),
            'phone' => trim((string) ($row[2] ?? '')),
            'username' => trim((string) ($row[3] ?? '')),
            'password' => (string) ($row[4] ?? ''),
            'registerNO' => trim((string) ($row[5] ?? '')),
            'sex' => trim((string) ($row[6] ?? '')),
            'dob' => trim((string) ($row[7] ?? '')),
            'admission_date' => trim((string) ($row[8] ?? '')),
            'address' => trim((string) ($row[9] ?? '')),
            'state' => trim((string) ($row[10] ?? '')),
            'country' => trim((string) ($row[11] ?? '')),
            'company_brief' => trim((string) ($row[12] ?? ''))
        ];

        $errors = [];
        if ($data['name'] === '' || $data['username'] === '' || $data['password'] === '') {
            $errors[] = 'Missing required fields.';
        }
        if ($data['username'] !== '' && !$this->_is_username_available($data['username'])) {
            $errors[] = 'Username exists.';
        }
        if ($data['email'] !== '' && !$this->_is_email_available($data['email'])) {
            $errors[] = 'Email exists.';
        }

        return ['data' => $data, 'errors' => $errors];
    }

    private function _validate_mentor_row($row)
    {
        $data = [
            'name' => trim((string) ($row[0] ?? '')),
            'email' => trim((string) ($row[1] ?? '')),
            'phone' => trim((string) ($row[2] ?? '')),
            'username' => trim((string) ($row[3] ?? '')),
            'password' => (string) ($row[4] ?? ''),
            'sex' => trim((string) ($row[5] ?? '')),
            'dob' => trim((string) ($row[6] ?? '')),
            'designation' => trim((string) ($row[7] ?? 'Mentor'))
        ];

        $errors = [];
        if ($data['name'] === '' || $data['username'] === '' || $data['password'] === '' || $data['sex'] === '' || $data['dob'] === '') {
            $errors[] = 'Missing required fields.';
        }
        if ($data['username'] !== '' && !$this->_is_username_available($data['username'])) {
            $errors[] = 'Username exists.';
        }
        if ($data['email'] !== '' && !$this->_is_email_available($data['email'])) {
            $errors[] = 'Email exists.';
        }

        return ['data' => $data, 'errors' => $errors];
    }

    private function _download_csv($filename, $headers)
    {
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        $out = fopen('php://output', 'w');
        fputcsv($out, $headers);
        fclose($out);
        exit;
    }

    private function _create_founder_from_row($row)
    {
        $name = trim((string) ($row[0] ?? ''));
        $email = trim((string) ($row[1] ?? ''));
        $phone = trim((string) ($row[2] ?? ''));
        $username = trim((string) ($row[3] ?? ''));
        $password = (string) ($row[4] ?? '');
        $registerNO = trim((string) ($row[5] ?? ''));
        $sex = trim((string) ($row[6] ?? ''));
        $dob = trim((string) ($row[7] ?? ''));
        $admissionDate = trim((string) ($row[8] ?? ''));
        $address = trim((string) ($row[9] ?? ''));
        $state = trim((string) ($row[10] ?? ''));
        $country = trim((string) ($row[11] ?? ''));
        $companyBrief = trim((string) ($row[12] ?? ''));

        if ($name === '' || $username === '' || $password === '') {
            return ['ok' => false, 'message' => 'Missing required fields.'];
        }
        if (!$this->_is_username_available($username)) {
            return ['ok' => false, 'message' => 'Username exists.'];
        }
        if ($email !== '' && !$this->_is_email_available($email)) {
            return ['ok' => false, 'message' => 'Email exists.'];
        }

        $payload = [
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
            'username' => $username,
            'password' => $password,
            'registerNO' => $registerNO,
            'sex' => $sex,
            'dob' => $dob,
            'admission_date' => $admissionDate,
            'address' => $address,
            'state' => $state,
            'country' => $country,
            'company_brief' => $companyBrief
        ];
        return $this->_create_founder_record($payload);
    }

    private function _create_mentor_from_row($row)
    {
        $name = trim((string) ($row[0] ?? ''));
        $email = trim((string) ($row[1] ?? ''));
        $phone = trim((string) ($row[2] ?? ''));
        $username = trim((string) ($row[3] ?? ''));
        $password = (string) ($row[4] ?? '');
        $sex = trim((string) ($row[5] ?? ''));
        $dob = trim((string) ($row[6] ?? ''));
        $designation = trim((string) ($row[7] ?? 'Mentor'));

        if ($name === '' || $username === '' || $password === '' || $sex === '' || $dob === '') {
            return ['ok' => false, 'message' => 'Missing required fields.'];
        }
        if (!$this->_is_username_available($username)) {
            return ['ok' => false, 'message' => 'Username exists.'];
        }
        if ($email !== '' && !$this->_is_email_available($email)) {
            return ['ok' => false, 'message' => 'Email exists.'];
        }

        $payload = [
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
            'username' => $username,
            'password' => $password,
            'sex' => $sex,
            'dob' => $dob,
            'designation' => $designation
        ];
        return $this->_create_mentor_record($payload);
    }

    private function _create_founder_record($payload)
    {
        $requiredTables = ['student', 'studentrelation', 'studentextend'];
        foreach ($requiredTables as $table) {
            if (!$this->db->table_exists($table)) {
                return ['ok' => false, 'message' => 'Required tables missing. Run DB updates.'];
            }
        }

        $name = trim((string) ($payload['name'] ?? ''));
        $email = trim((string) ($payload['email'] ?? ''));
        $phone = trim((string) ($payload['phone'] ?? ''));
        $username = trim((string) ($payload['username'] ?? ''));
        $password = (string) ($payload['password'] ?? '');
        $track = $this->_ensure_default_track();
        $classesID = (int) ($payload['classesID'] ?? $track['classesID']);
        $sectionID = (int) ($payload['sectionID'] ?? $track['sectionID']);
        $roll = trim((string) ($payload['roll'] ?? ''));
        $registerNO = trim((string) ($payload['registerNO'] ?? ''));
        $sex = trim((string) ($payload['sex'] ?? ''));
        $dob = trim((string) ($payload['dob'] ?? ''));
        $admission_date = trim((string) ($payload['admission_date'] ?? ''));
        $address = trim((string) ($payload['address'] ?? ''));
        $state = trim((string) ($payload['state'] ?? ''));
        $country = trim((string) ($payload['country'] ?? ''));
        $religion = trim((string) ($payload['religion'] ?? ''));
        $bloodgroup = trim((string) ($payload['bloodgroup'] ?? ''));
        $companyBrief = trim((string) ($payload['company_brief'] ?? ''));

        if ($name === '' || $username === '' || $password === '' || $classesID <= 0 || $sectionID <= 0) {
            return ['ok' => false, 'message' => 'Please fill all required fields.'];
        }

        if ($roll === '') {
            $roll = 'F-' . date('His');
        }

        if (!$this->_is_username_available($username)) {
            return ['ok' => false, 'message' => 'Username already exists.'];
        }
        if ($email !== '' && !$this->_is_email_available($email)) {
            return ['ok' => false, 'message' => 'Email already exists.'];
        }

        $schoolyearID = $this->session->userdata('defaultschoolyearID');
        $studentArray = [
            'name' => $name,
            'sex' => $sex !== '' ? $sex : 'Male',
            'religion' => $religion,
            'email' => $email,
            'phone' => $phone,
            'address' => $address,
            'classesID' => $classesID,
            'sectionID' => $sectionID,
            'roll' => $roll,
            'bloodgroup' => $bloodgroup,
            'state' => $state,
            'country' => $country,
            'registerNO' => $registerNO !== '' ? $registerNO : ('FND-' . time()),
            'username' => $username,
            'password' => $this->student_m->hash($password),
            'usertypeID' => 3,
            'parentID' => 0,
            'library' => 0,
            'hostel' => 0,
            'transport' => 0,
            'createschoolyearID' => $schoolyearID,
            'schoolyearID' => $schoolyearID,
            'create_date' => date("Y-m-d H:i:s"),
            'modify_date' => date("Y-m-d H:i:s"),
            'create_userID' => $this->session->userdata('loginuserID'),
            'create_username' => $this->session->userdata('username'),
            'create_usertype' => $this->session->userdata('usertype'),
            'active' => 1,
            'photo' => 'default.png'
        ];

        if ($dob !== '') {
            $studentArray['dob'] = date("Y-m-d", strtotime($dob));
        }
        if ($admission_date !== '') {
            $studentArray['admission_date'] = date("Y-m-d", strtotime($admission_date));
        }

        $this->student_m->insert_student($studentArray);
        $studentID = $this->db->insert_id();

        $section = $this->section_m->general_get_section($sectionID);
        $classes = $this->classes_m->get_classes($classesID);
        $setClasses = customCompute($classes) ? $classes->classes : null;
        $setSection = customCompute($section) ? $section->section : null;

        $this->studentrelation_m->insert_studentrelation([
            'srstudentID' => $studentID,
            'srname' => $name,
            'srclassesID' => $classesID,
            'srclasses' => $setClasses,
            'srroll' => $roll,
            'srregisterNO' => $studentArray['registerNO'],
            'srsectionID' => $sectionID,
            'srsection' => $setSection,
            'srstudentgroupID' => 0,
            'sroptionalsubjectID' => 0,
            'srschoolyearID' => $schoolyearID,
        ]);

        $this->studentextend_m->insert_studentextend([
            'studentID' => $studentID,
            'studentgroupID' => 0,
            'optionalsubjectID' => 0,
            'extracurricularactivities' => '',
            'remarks' => $companyBrief
        ]);

        return [
            'ok' => true,
            'message' => 'Founder created. Username: ' . $username
        ];
    }

    private function _create_mentor_record($payload)
    {
        $name = trim((string) ($payload['name'] ?? ''));
        $email = trim((string) ($payload['email'] ?? ''));
        $phone = trim((string) ($payload['phone'] ?? ''));
        $username = trim((string) ($payload['username'] ?? ''));
        $password = (string) ($payload['password'] ?? '');
        $designation = trim((string) ($payload['designation'] ?? ''));
        $dob = trim((string) ($payload['dob'] ?? ''));
        $sex = trim((string) ($payload['sex'] ?? ''));
        $address = trim((string) ($payload['address'] ?? ''));
        $religion = trim((string) ($payload['religion'] ?? ''));

        if ($name === '' || $username === '' || $password === '' || $dob === '' || $sex === '') {
            return ['ok' => false, 'message' => 'Please fill all required fields.'];
        }
        if (!$this->_is_username_available($username)) {
            return ['ok' => false, 'message' => 'Username already exists.'];
        }
        if ($email !== '' && !$this->_is_email_available($email)) {
            return ['ok' => false, 'message' => 'Email already exists.'];
        }

        $mentorArray = [
            'name' => $name,
            'designation' => $designation !== '' ? $designation : 'Mentor',
            'dob' => date("Y-m-d", strtotime($dob)),
            'sex' => $sex,
            'religion' => $religion,
            'email' => $email,
            'phone' => $phone,
            'address' => $address,
            'jod' => date("Y-m-d"),
            'username' => $username,
            'password' => $this->teacher_m->hash($password),
            'usertypeID' => 2,
            'create_date' => date("Y-m-d H:i:s"),
            'modify_date' => date("Y-m-d H:i:s"),
            'create_userID' => $this->session->userdata('loginuserID'),
            'create_username' => $this->session->userdata('username'),
            'create_usertype' => $this->session->userdata('usertype'),
            'active' => 1,
            'photo' => 'default.png'
        ];

        $this->teacher_m->insert_teacher($mentorArray);
        return [
            'ok' => true,
            'message' => 'Mentor created. Username: ' . $username
        ];
    }

    private function _is_username_available($username)
    {
        $tables = ['student', 'teacher', 'parents', 'user'];
        foreach ($tables as $table) {
            $exists = $this->student_m->get_username($table, ['username' => $username]);
            if (customCompute($exists)) {
                return false;
            }
        }
        return true;
    }

    private function _is_email_available($email)
    {
        $tables = ['student', 'teacher', 'parents', 'user'];
        foreach ($tables as $table) {
            $exists = $this->student_m->get_username($table, ['email' => $email]);
            if (customCompute($exists)) {
                return false;
            }
        }
        return true;
    }

    private function _unique_username($base)
    {
        $candidate = $base;
        $tries = 0;
        while (!$this->_is_username_available($candidate) && $tries < 20) {
            $candidate = $base . '_' . rand(10, 99);
            $tries++;
        }
        return $candidate;
    }

    private function _ensure_default_track()
    {
        $classes = $this->classes_m->general_get_single_classes(['classes' => 'Hatchers AI']);
        if (!customCompute($classes)) {
            $classesID = $this->classes_m->insert_classes([
                'classes' => 'Hatchers AI',
                'classes_numeric' => 1,
                'teacherID' => 0,
                'studentmaxID' => 999999999,
                'note' => 'Default compatibility sprint for the Hatchers LMS.',
                'create_date' => date('Y-m-d H:i:s'),
                'modify_date' => date('Y-m-d H:i:s'),
                'create_userID' => $this->session->userdata('loginuserID'),
                'create_username' => $this->session->userdata('username'),
                'create_usertype' => $this->session->userdata('usertype')
            ]);
            $classes = $this->classes_m->general_get_single_classes(['classesID' => $classesID]);
        }

        $section = $this->section_m->general_get_single_section([
            'classesID' => $classes->classesID,
            'section' => 'Default Track'
        ]);
        if (!customCompute($section)) {
            $sectionID = $this->section_m->insert_section([
                'section' => 'Default Track',
                'category' => 'Founder Journey',
                'capacity' => 9999,
                'classesID' => $classes->classesID,
                'teacherID' => 0,
                'note' => 'Hidden compatibility section for Hatchers founders.',
                'create_date' => date('Y-m-d H:i:s'),
                'modify_date' => date('Y-m-d H:i:s'),
                'create_userID' => $this->session->userdata('loginuserID'),
                'create_username' => $this->session->userdata('username'),
                'create_usertype' => $this->session->userdata('usertype')
            ]);
            $section = $this->section_m->general_get_single_section(['sectionID' => $sectionID]);
        }

        return [
            'classesID' => (int) $classes->classesID,
            'classes' => $classes->classes,
            'sectionID' => (int) $section->sectionID,
            'section' => $section->section
        ];
    }

    private function _render($subview, $activeNav = 'home', $calendar = [])
    {
        $this->data['hatchers_shell'] = $this->hatchers_shell_m->build($activeNav, $calendar);
        $this->data['subview'] = $subview;
        $this->load->view('_layout_hatchers', $this->data);
    }
}
