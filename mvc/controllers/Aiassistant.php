<?php if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Aiassistant extends Admin_Controller
{
    public $load;
    public $session;
    public $data;
    public $input;
    public $db;
    public $setting_m;
    public $hatcher_ai_settings_m;
    public $hatcher_ai_context_m;
    public $mentor_founder_m;
    public $founder_task_m;
    public $founder_meeting_m;
    public $founder_learning_m;
    public $milestone_meta_m;
    public $teacher_m;

    public function __construct()
    {
        parent::__construct();
        $this->load->model('setting_m');
        $this->load->model('hatcher_ai_settings_m');
        $this->load->model('hatcher_ai_context_m');
        $this->load->model('mentor_founder_m');
        $this->load->model('founder_task_m');
        $this->load->model('founder_meeting_m');
        $this->load->model('founder_learning_m');
        $this->load->model('milestone_meta_m');
        $this->load->model('teacher_m');
    }

    public function chat()
    {
        if ($this->session->userdata('usertypeID') != 3) {
            show_404();
        }

        $message = trim((string) $this->input->post('message'));
        if ($message === '') {
            $this->_json(['ok' => false, 'error' => 'Message is required.']);
            return;
        }

        $founderID = $this->session->userdata('loginuserID');

        if (!$this->db->table_exists('hatcher_ai_conversations')) {
            $this->_json(['ok' => false, 'error' => 'AI tables not installed. Run the Hatchers SQL update first.']);
            return;
        }

        $settings = null;
        if ($this->db->table_exists('hatcher_ai_settings')) {
            $settings = $this->hatcher_ai_settings_m->get_latest_settings();
        }
        $openaiKey = getenv('OPENAI_API_KEY');

        if (empty($openaiKey)) {
            $this->_json(['ok' => false, 'error' => 'OpenAI API key not configured.']);
            return;
        }

        $systemPrompt = customCompute($settings) ? $settings->system_prompt : 'You are Hatchers AI, a friendly mentor for founders.';
        $guidelines = customCompute($settings) ? $settings->guidelines : 'Be concise, practical, and action-oriented.';
        $model = customCompute($settings) ? $settings->model : 'gpt-4o-mini';
        $temperature = customCompute($settings) ? (float) $settings->temperature : 0.7;
        $maxTokens = customCompute($settings) ? (int) $settings->max_tokens : 600;

        $contextText = $this->_buildFounderContext($founderID, $openaiKey);

        $history = $this->db->order_by('hatcher_ai_conversation_id', 'DESC')
            ->limit(6)
            ->get_where('hatcher_ai_conversations', ['founder_id' => $founderID])
            ->result();

        $messages = [];
        if (customCompute($history)) {
            $history = array_reverse($history);
            foreach ($history as $row) {
                $messages[] = [
                    'role' => $row->role,
                    'content' => $row->message
                ];
            }
        }
        $messages[] = ['role' => 'user', 'content' => $message];

        $payload = [
            'model' => $model,
            'input' => $messages,
            'instructions' => trim($systemPrompt . "\n\n" . $guidelines . "\n\nFounder context:\n" . $contextText),
            'max_output_tokens' => $maxTokens,
            'temperature' => $temperature
        ];

        $response = $this->_callOpenAI($openaiKey, $payload);
        if (!$response['ok']) {
            $this->_json(['ok' => false, 'error' => $this->_friendlyOpenAIError($response['error'])]);
            return;
        }

        $reply = $this->_extractResponseText($response['data']);
        if ($reply === '') {
            $reply = "I'm here to help. Could you rephrase that?";
        }

        $this->db->insert('hatcher_ai_conversations', [
            'founder_id' => $founderID,
            'role' => 'user',
            'message' => $message,
            'created_at' => date('Y-m-d H:i:s')
        ]);
        $this->db->insert('hatcher_ai_conversations', [
            'founder_id' => $founderID,
            'role' => 'assistant',
            'message' => $reply,
            'created_at' => date('Y-m-d H:i:s')
        ]);

        $this->_json(['ok' => true, 'reply' => $reply]);
    }

    private function _buildFounderContext($founderID, $openaiKey)
    {
        if (!$this->db->table_exists('hatcher_ai_context')) {
            return 'No founder context available yet.';
        }

        $existing = $this->hatcher_ai_context_m->get_by_founder($founderID);
        if (customCompute($existing)) {
            $lastUpdate = strtotime((string) $existing->updated_at);
            if ($lastUpdate && (time() - $lastUpdate) < (6 * 60 * 60)) {
                return $this->_formatContextText($existing);
            }
        }

        $this->db->select('student.studentID, student.name, student.email, student.phone');
        $this->db->from('student');
        $this->db->join('studentextend', 'studentextend.studentID = student.studentID', 'LEFT');
        $this->db->where('student.studentID', $founderID);
        $founder = $this->db->get()->row();

        $mentorAssignment = $this->mentor_founder_m->get_single_mentor_founder([
            'founder_id' => $founderID,
            'status' => 1
        ]);
        $mentor = null;
        if (customCompute($mentorAssignment)) {
            $mentor = $this->teacher_m->get_single_teacher(['teacherID' => $mentorAssignment->mentor_id]);
        }

        $tasks = $this->founder_task_m->get_order_by_founder_task(['founder_id' => $founderID]);
        $milestones = $this->milestone_meta_m->get_order_by_milestone_meta(['founder_id' => $founderID]);
        $learning = $this->founder_learning_m->get_order_by_founder_learning(['founder_id' => $founderID]);
        $meetings = $this->founder_meeting_m->get_order_by_founder_meeting(['founder_id' => $founderID]);

        $recentChats = $this->db->order_by('hatcher_ai_conversation_id', 'DESC')
            ->limit(6)
            ->get_where('hatcher_ai_conversations', ['founder_id' => $founderID])
            ->result();
        $recentChats = array_reverse($recentChats);

        $summaryInput = [
            'founder' => [
                'id' => $founderID,
                'name' => customCompute($founder) ? $founder->name : '',
                'email' => customCompute($founder) ? $founder->email : ''
            ],
            'mentor' => customCompute($mentor) ? $mentor->name : '',
            'tasks' => $this->_mapTasks($tasks),
            'milestones' => $this->_mapMilestones($milestones),
            'learning' => $this->_mapLearning($learning),
            'meetings' => $this->_mapMeetings($meetings),
            'recent_conversation' => $this->_mapConversation($recentChats)
        ];

        $payload = [
            'model' => 'gpt-4o-mini',
            'input' => [
                [
                    'role' => 'user',
                    'content' => "Summarize this founder context into JSON with keys: goals, current_sprint, blockers, progress_summary. Keep each field concise. Input:\n" . json_encode($summaryInput)
                ]
            ],
            'instructions' => 'You are a system that creates structured founder context for mentoring. Output JSON only.',
            'max_output_tokens' => 300,
            'temperature' => 0.2
        ];

        $response = $this->_callOpenAI($openaiKey, $payload);
        if (!$response['ok']) {
            return customCompute($existing) ? $this->_formatContextText($existing) : 'No founder context available yet.';
        }

        $reply = $this->_extractResponseText($response['data']);
        $json = json_decode($reply, true);
        if (!is_array($json)) {
            return customCompute($existing) ? $this->_formatContextText($existing) : 'No founder context available yet.';
        }

        $contextData = [
            'goals' => isset($json['goals']) ? $json['goals'] : '',
            'current_sprint' => isset($json['current_sprint']) ? $json['current_sprint'] : '',
            'blockers' => isset($json['blockers']) ? $json['blockers'] : '',
            'progress_summary' => isset($json['progress_summary']) ? $json['progress_summary'] : '',
            'updated_at' => date('Y-m-d H:i:s')
        ];

        $this->hatcher_ai_context_m->upsert_context($founderID, $contextData);
        $contextData['founder_id'] = $founderID;
        return $this->_formatContextText((object) $contextData);
    }

    private function _formatContextText($context)
    {
        return "Goals: " . (string) $context->goals
            . "\nCurrent sprint: " . (string) $context->current_sprint
            . "\nBlockers: " . (string) $context->blockers
            . "\nProgress summary: " . (string) $context->progress_summary;
    }

    private function _mapTasks($tasks)
    {
        $mapped = [];
        if (customCompute($tasks)) {
            foreach ($tasks as $task) {
                $mapped[] = [
                    'title' => $task->title,
                    'due_date' => $task->due_date,
                    'status' => $task->status
                ];
            }
        }
        return $mapped;
    }

    private function _mapMilestones($milestones)
    {
        $mapped = [];
        if (customCompute($milestones)) {
            foreach ($milestones as $milestone) {
                $mapped[] = [
                    'title' => $milestone->title,
                    'due_date' => $milestone->due_date,
                    'status' => $milestone->status
                ];
            }
        }
        return $mapped;
    }

    private function _mapLearning($learning)
    {
        $mapped = [];
        if (customCompute($learning)) {
            foreach ($learning as $lesson) {
                $mapped[] = [
                    'title' => $lesson->title,
                    'starts_at' => $lesson->starts_at,
                    'status' => $lesson->status
                ];
            }
        }
        return $mapped;
    }

    private function _mapMeetings($meetings)
    {
        $mapped = [];
        if (customCompute($meetings)) {
            foreach ($meetings as $meeting) {
                $mapped[] = [
                    'starts_at' => $meeting->starts_at,
                    'status' => $meeting->status
                ];
            }
        }
        return $mapped;
    }

    private function _mapConversation($rows)
    {
        $mapped = [];
        if (customCompute($rows)) {
            foreach ($rows as $row) {
                $mapped[] = [
                    'role' => $row->role,
                    'message' => $row->message
                ];
            }
        }
        return $mapped;
    }

    private function _callOpenAI($apiKey, $payload)
    {
        $ch = curl_init('https://api.openai.com/v1/responses');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $apiKey
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));

        $result = curl_exec($ch);
        if ($result === false) {
            $error = curl_error($ch);
            curl_close($ch);
            return ['ok' => false, 'error' => $error];
        }

        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        $data = json_decode($result, true);

        if ($status >= 400 || !is_array($data)) {
            $message = isset($data['error']['message']) ? $data['error']['message'] : 'OpenAI request failed.';
            return ['ok' => false, 'error' => $message];
        }

        return ['ok' => true, 'data' => $data];
    }

    private function _extractResponseText($data)
    {
        if (!isset($data['output']) || !is_array($data['output'])) {
            return '';
        }
        foreach ($data['output'] as $item) {
            if (isset($item['type']) && $item['type'] === 'message' && isset($item['content'])) {
                foreach ($item['content'] as $content) {
                    if (isset($content['type']) && $content['type'] === 'output_text') {
                        return $content['text'];
                    }
                }
            }
        }
        return '';
    }

    private function _friendlyOpenAIError($message)
    {
        $safeDefault = 'Hatchers AI is temporarily unavailable. Please try again in a minute.';
        $message = (string) $message;
        if (stripos($message, 'Incorrect API key') !== false || stripos($message, 'authentication') !== false) {
            return 'AI service is not configured correctly. Please contact the admin to verify the API key.';
        }
        if (stripos($message, 'rate limit') !== false) {
            return 'Hatchers AI is busy right now. Please try again shortly.';
        }
        if (stripos($message, 'insufficient_quota') !== false) {
            return 'Hatchers AI is temporarily unavailable due to billing limits.';
        }
        return $safeDefault;
    }

    private function _json($payload)
    {
        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($payload));
    }
}
